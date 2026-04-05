<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class HlsEncodingService
{
    /** Préfixes disque (private) reconnus pour l’encodage + routes FileController */
    public const STORAGE_PREFIXES = [
        'courses/lessons',
        'courses/previews',
        'packages/covers',
        'site/community-home',
    ];

    public function deleteHlsOutputForVideo(string $relativeVideoPath): void
    {
        $relativeVideoPath = ltrim($relativeVideoPath, '/');
        $disk = Storage::disk('local');
        $parentDir = dirname($relativeVideoPath);
        if ($parentDir === '.' || $parentDir === '') {
            return;
        }
        $stem = pathinfo($relativeVideoPath, PATHINFO_FILENAME);
        $hlsDir = $parentDir.'/'.$stem.'_hls';
        if ($disk->exists($hlsDir)) {
            $disk->deleteDirectory($hlsDir);
        }
        // Ancien schéma (un seul dossier hls/ par répertoire parent)
        $legacy = $parentDir.'/hls';
        if ($disk->exists($legacy)) {
            $disk->deleteDirectory($legacy);
        }
    }

    /**
     * @param  string  $relativeVideoPath  Chemin relatif disque (ex. courses/lessons/foo.mp4)
     * @return string Chemin relatif à passer à route('files.serve', ['type' => ..., 'path' => ...]) (ex. foo_hls/master.m3u8)
     */
    public function encodeLessonVideoToHls(string $relativeVideoPath): string
    {
        return $this->encodeVideoToHls($relativeVideoPath);
    }

    /**
     * Encode une vidéo hébergée en HLS multi-débits (dossier {stem}_hls à côté du fichier source).
     *
     * @param  string  $relativeVideoPath  ex. courses/previews/abc.mp4
     * @return string Suffixe après le préfixe du type (ex. abc_hls/master.m3u8) pour FileController
     */
    public function encodeVideoToHls(string $relativeVideoPath): string
    {
        $disk = Storage::disk('local');
        $relativeVideoPath = ltrim($relativeVideoPath, '/');

        if (! $disk->exists($relativeVideoPath)) {
            throw new \RuntimeException('Fichier vidéo introuvable: '.$relativeVideoPath);
        }

        $prefix = $this->resolveStoragePrefix($relativeVideoPath);
        if ($prefix === null) {
            throw new \RuntimeException('Préfixe de stockage non supporté pour HLS: '.$relativeVideoPath);
        }

        $this->deleteHlsOutputForVideo($relativeVideoPath);

        $abs = $disk->path($relativeVideoPath);
        $parentAbs = dirname($abs);
        $videoBasename = basename($abs);
        $stem = pathinfo($relativeVideoPath, PATHINFO_FILENAME);
        $outFolder = $stem.'_hls';

        $variants = config('video.hls.variants', []);
        if ($variants === [] || count($variants) < 1) {
            throw new \RuntimeException('Aucun profil HLS configuré (video.hls.variants).');
        }

        $n = count($variants);
        $splitLabels = implode('', array_map(fn (int $i) => '[v'.$i.']', range(0, $n - 1)));
        $fc = '[0:v]split='.$n.$splitLabels;
        for ($i = 0; $i < $n; $i++) {
            $h = (int) ($variants[$i]['height'] ?? 480);
            $fc .= sprintf(';[v%d]scale=-2:%d[out%d]', $i, $h, $i);
        }

        $ffmpeg = config('video.hls.ffmpeg_path', 'ffmpeg');
        $preset = config('video.hls.preset', 'faster');
        $seg = (int) config('video.hls.segment_seconds', 6);

        $hasAudio = $this->sourceHasAudio($abs);

        $args = [
            $ffmpeg, '-y', '-hide_banner', '-loglevel', 'warning',
            '-i', $videoBasename,
            '-filter_complex', $fc,
        ];

        for ($i = 0; $i < $n; $i++) {
            $args[] = '-map';
            $args[] = sprintf('[out%d]', $i);
            if ($hasAudio) {
                $args[] = '-map';
                $args[] = '0:a:0';
            }
            $args[] = '-c:v:'.$i;
            $args[] = 'libx264';
            $args[] = '-preset';
            $args[] = $preset;
            $args[] = '-b:v:'.$i;
            $args[] = (string) ($variants[$i]['bitrate'] ?? '1000k');
            if (! empty($variants[$i]['maxrate'])) {
                $args[] = '-maxrate:v:'.$i;
                $args[] = (string) $variants[$i]['maxrate'];
            }
            if (! empty($variants[$i]['bufsize'])) {
                $args[] = '-bufsize:v:'.$i;
                $args[] = (string) $variants[$i]['bufsize'];
            }
            $args[] = '-g';
            $args[] = '48';
            $args[] = '-keyint_min';
            $args[] = '48';
            $args[] = '-sc_threshold';
            $args[] = '0';
            if ($hasAudio) {
                $args[] = '-c:a:'.$i;
                $args[] = 'aac';
                $args[] = '-b:a:'.$i;
                $args[] = '128k';
                $args[] = '-ac';
                $args[] = '2';
            }
        }

        $vsmParts = [];
        for ($i = 0; $i < $n; $i++) {
            $vsmParts[] = $hasAudio ? 'v:'.$i.',a:'.$i : 'v:'.$i;
        }

        array_push(
            $args,
            '-f', 'hls',
            '-hls_time', (string) $seg,
            '-hls_playlist_type', 'vod',
            '-hls_flags', 'independent_segments',
            '-hls_segment_type', 'mpegts',
            '-hls_segment_filename', $outFolder.'/stream_%v/segment%03d.ts',
            '-master_pl_name', $outFolder.'/master.m3u8',
            '-var_stream_map', implode(' ', $vsmParts),
            $outFolder.'/stream_%v.m3u8'
        );

        $process = new Process($args, $parentAbs);
        $process->setTimeout(3600);
        $process->run();

        if (! $process->isSuccessful()) {
            Log::error('HlsEncodingService: FFmpeg failed', [
                'path' => $relativeVideoPath,
                'stderr' => $process->getErrorOutput(),
                'stdout' => $process->getOutput(),
            ]);
            throw new \RuntimeException(
                'FFmpeg HLS a échoué. Vérifiez que FFmpeg est installé et que la vidéo est valide.'
            );
        }

        $manifestFull = dirname($relativeVideoPath).'/'.$outFolder.'/master.m3u8';
        if (! $disk->exists($manifestFull)) {
            throw new \RuntimeException('master.m3u8 introuvable après encodage.');
        }

        $suffix = $this->pathSuffixAfterPrefix($manifestFull, $prefix);
        if ($suffix === null) {
            throw new \RuntimeException('Impossible de dériver le chemin manifeste HLS relatif.');
        }

        return $suffix;
    }

    public function resolveStoragePrefix(string $relativePath): ?string
    {
        $relativePath = ltrim($relativePath, '/');
        foreach (self::STORAGE_PREFIXES as $p) {
            if ($relativePath === $p || str_starts_with($relativePath, $p.'/')) {
                return $p;
            }
        }

        return null;
    }

    protected function pathSuffixAfterPrefix(string $fullRelativePath, string $prefix): ?string
    {
        $fullRelativePath = ltrim($fullRelativePath, '/');
        $prefix = rtrim($prefix, '/').'/';
        if (! str_starts_with($fullRelativePath, $prefix)) {
            return null;
        }

        return substr($fullRelativePath, strlen($prefix));
    }

    protected function sourceHasAudio(string $absoluteVideoPath): bool
    {
        $ffmpeg = config('video.hls.ffmpeg_path', 'ffmpeg');
        $ffprobe = preg_replace('#ffmpeg([^/]*)$#', 'ffprobe$1', $ffmpeg) ?: 'ffprobe';

        $process = new Process([
            $ffprobe,
            '-v', 'error',
            '-show_entries', 'stream=codec_type',
            '-of', 'csv=p=0',
            $absoluteVideoPath,
        ]);
        $process->setTimeout(120);
        $process->run();

        if (! $process->isSuccessful()) {
            return true;
        }

        $out = trim($process->getOutput());

        return str_contains($out, 'audio');
    }
}
