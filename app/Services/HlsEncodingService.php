<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class HlsEncodingService
{
    public function deleteHlsOutputForVideo(string $relativeVideoPath): void
    {
        $disk = Storage::disk('local');
        $base = dirname($relativeVideoPath);
        $hlsDir = $base.'/hls';
        if ($disk->exists($hlsDir)) {
            $disk->deleteDirectory($hlsDir);
        }
    }

    /**
     * @return string Chemin relatif disque du master.m3u8 (ex. courses/lessons/x/hls/master.m3u8)
     */
    public function encodeLessonVideoToHls(string $relativeVideoPath): string
    {
        $disk = Storage::disk('local');
        $relativeVideoPath = ltrim($relativeVideoPath, '/');

        if (! $disk->exists($relativeVideoPath)) {
            throw new \RuntimeException('Fichier vidéo introuvable: '.$relativeVideoPath);
        }

        $abs = $disk->path($relativeVideoPath);
        $cwd = dirname($abs);
        $basename = basename($abs);

        $this->deleteHlsOutputForVideo($relativeVideoPath);

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
            '-i', $basename,
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
            '-hls_segment_filename', 'hls/stream_%v/segment%03d.ts',
            '-master_pl_name', 'master.m3u8',
            '-var_stream_map', implode(' ', $vsmParts),
            'hls/stream_%v.m3u8'
        );

        $process = new Process($args, $cwd);
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

        $manifestRel = dirname($relativeVideoPath).'/hls/master.m3u8';
        if (! $disk->exists($manifestRel)) {
            throw new \RuntimeException('master.m3u8 introuvable après encodage.');
        }

        return $manifestRel;
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
