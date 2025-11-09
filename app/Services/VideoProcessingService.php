<?php

namespace App\Services;

use App\Models\MediaFile;
use App\Models\MediaVariant;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VideoProcessingService
{
    protected $ffmpegPath = 'ffmpeg'; // ou chemin complet si nécessaire
    protected $ffprobePath = 'ffprobe'; // ou chemin complet si nécessaire

    /**
     * Traiter une vidéo : encoder en multiples résolutions et générer HLS
     */
    public function processVideo(MediaFile $mediaFile): bool
    {
        try {
            $relativePath = ltrim(preg_replace('#^storage/#', '', $mediaFile->storage_path), '/');
            $disk = Storage::disk('local');
            $inputPath = $disk->path($relativePath);
            
            if (!file_exists($inputPath)) {
                throw new \Exception("Fichier vidéo introuvable: {$inputPath}");
            }

            // 1. Extraire les métadonnées vidéo
            $metadata = $this->extractVideoMetadata($inputPath);
            $mediaFile->setMeta('video.duration', $metadata['duration']);
            $mediaFile->setMeta('video.width', $metadata['width']);
            $mediaFile->setMeta('video.height', $metadata['height']);
            $mediaFile->setMeta('video.codec', $metadata['codec']);
            $mediaFile->setMeta('video.bitrate', $metadata['bitrate']);

            // 2. Générer la miniature
            $this->generateThumbnail($mediaFile, $inputPath);

            // 3. Encoder en multiples résolutions
            $this->encodeMultipleResolutions($mediaFile, $inputPath, $metadata);

            // 4. Générer le manifeste HLS master
            $this->generateHLSManifest($mediaFile);

            $mediaFile->markAsReady();
            
            return true;
        } catch (\Exception $e) {
            $mediaFile->markAsFailed($e->getMessage());
            \Log::error('Erreur traitement vidéo: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Extraire les métadonnées vidéo avec FFprobe
     */
    protected function extractVideoMetadata(string $inputPath): array
    {
        $command = sprintf(
            '%s -v quiet -print_format json -show_format -show_streams "%s"',
            $this->ffprobePath,
            $inputPath
        );

        $output = shell_exec($command);
        $data = json_decode($output, true);

        if (!$data) {
            throw new \Exception("Impossible d'extraire les métadonnées vidéo");
        }

        // Trouver le stream vidéo
        $videoStream = collect($data['streams'] ?? [])
            ->first(fn($s) => ($s['codec_type'] ?? '') === 'video');

        if (!$videoStream) {
            throw new \Exception("Aucun stream vidéo trouvé");
        }

        return [
            'duration' => floatval($data['format']['duration'] ?? 0),
            'width' => intval($videoStream['width'] ?? 0),
            'height' => intval($videoStream['height'] ?? 0),
            'codec' => $videoStream['codec_name'] ?? 'unknown',
            'bitrate' => intval($data['format']['bit_rate'] ?? 0),
            'fps' => $this->parseFps($videoStream['r_frame_rate'] ?? '0'),
        ];
    }

    /**
     * Parser le framerate
     */
    protected function parseFps(string $fps): float
    {
        if (strpos($fps, '/') !== false) {
            [$num, $den] = explode('/', $fps);
            return $den > 0 ? floatval($num) / floatval($den) : 0;
        }
        return floatval($fps);
    }

    /**
     * Générer une miniature
     */
    protected function generateThumbnail(MediaFile $mediaFile, string $inputPath): void
    {
        $basePath = dirname(ltrim(preg_replace('#^storage/#', '', $mediaFile->storage_path), '/'));
        $thumbnailPath = "{$basePath}/thumbnail.jpg";
        $disk = Storage::disk('local');
        $disk->makeDirectory($basePath);
        $thumbnailFullPath = $disk->path($thumbnailPath);

        // Extraire une frame à 5 secondes
        $command = sprintf(
            '%s -i "%s" -ss 00:00:05 -vframes 1 -vf scale=1280:-1 -q:v 2 "%s" 2>&1',
            $this->ffmpegPath,
            $inputPath,
            $thumbnailFullPath
        );

        exec($command, $output, $returnCode);

        if ($returnCode === 0 && file_exists($thumbnailFullPath)) {
            MediaVariant::create([
                'media_file_id' => $mediaFile->id,
                'variant_type' => 'thumbnail',
                'format' => 'jpg',
                'storage_path' => $thumbnailPath,
                'size' => filesize($thumbnailFullPath),
                'status' => 'ready',
            ]);
        }
    }

    /**
     * Encoder la vidéo en multiples résolutions
     */
    protected function encodeMultipleResolutions(MediaFile $mediaFile, string $inputPath, array $metadata): void
    {
        $originalHeight = $metadata['height'];
        $basePath = dirname(ltrim(preg_replace('#^storage/#', '', $mediaFile->storage_path), '/'));

        // Définir les résolutions cibles
        $resolutions = $this->determineResolutions($originalHeight);

        foreach ($resolutions as $resolution) {
            $this->encodeResolution($mediaFile, $inputPath, $basePath, $resolution, $metadata);
        }
    }

    /**
     * Déterminer les résolutions à générer selon la vidéo originale
     */
    protected function determineResolutions(int $originalHeight): array
    {
        $allResolutions = [
            '360p' => ['height' => 360, 'bitrate' => '800k', 'audio_bitrate' => '96k'],
            '480p' => ['height' => 480, 'bitrate' => '1200k', 'audio_bitrate' => '128k'],
            '720p' => ['height' => 720, 'bitrate' => '2500k', 'audio_bitrate' => '128k'],
            '1080p' => ['height' => 1080, 'bitrate' => '5000k', 'audio_bitrate' => '192k'],
        ];

        // Ne générer que les résolutions <= à l'original
        return array_filter($allResolutions, function ($config) use ($originalHeight) {
            return $config['height'] <= $originalHeight;
        });
    }

    /**
     * Encoder une résolution spécifique en HLS
     */
    protected function encodeResolution(MediaFile $mediaFile, string $inputPath, string $basePath, string $resolutionKey, array $resolution, array $originalMetadata): void
    {
        $height = $resolution['height'];
        $bitrate = $resolution['bitrate'];
        $audioBitrate = $resolution['audio_bitrate'];

        // Créer le dossier pour cette résolution
        $resolutionPath = "{$basePath}/{$resolutionKey}";
        $resolutionFullPath = "{$basePath}/{$resolutionKey}/playlist.m3u8";
        
        $disk = Storage::disk('local');
        $disk->makeDirectory(dirname($resolutionFullPath));
        $resolutionFullPath = $disk->path($resolutionFullPath);

        $outputPlaylist = $resolutionFullPath;
        $outputSegment = "{$basePath}/{$resolutionKey}/segment_%03d.ts";

        // Commande FFmpeg pour encoder en HLS
        $command = sprintf(
            '%s -i "%s" ' .
            '-vf scale=-2:%d ' . // Maintenir le ratio
            '-c:v libx264 -preset medium -crf 23 -b:v %s -maxrate %s -bufsize %s ' .
            '-c:a aac -b:a %s -ac 2 ' .
            '-f hls -hls_time 6 -hls_list_size 0 -hls_segment_filename "%s" "%s" 2>&1',
            $this->ffmpegPath,
            $inputPath,
            $height,
            $bitrate,
            $bitrate,
            intval(substr($bitrate, 0, -1)) * 2 . 'k', // bufsize = 2x bitrate
            $audioBitrate,
            $outputSegment,
            $outputPlaylist
        );

        exec($command, $output, $returnCode);

        if ($returnCode === 0 && file_exists($outputPlaylist)) {
            // Calculer la taille totale des segments
            $totalSize = 0;
            foreach (glob("{$basePath}/{$resolutionKey}/segment_*.ts") as $segment) {
                $totalSize += filesize($segment);
            }

            MediaVariant::create([
                'media_file_id' => $mediaFile->id,
                'variant_type' => $resolutionKey,
                'format' => 'm3u8',
                'storage_path' => 'storage/' . $resolutionPath . '/playlist.m3u8',
                'size' => $totalSize,
                'width' => intval($originalMetadata['width'] * ($height / $originalMetadata['height'])),
                'height' => $height,
                'bitrate' => intval(substr($bitrate, 0, -1)) * 1000,
                'codec' => 'h264',
                'status' => 'ready',
                'metadata' => [
                    'segments_count' => count(glob("{$basePath}/{$resolutionKey}/segment_*.ts")),
                    'segment_duration' => 6,
                ],
            ]);
        }
    }

    /**
     * Générer le manifeste HLS master
     */
    protected function generateHLSManifest(MediaFile $mediaFile): void
    {
        $basePath = dirname(preg_replace('#^storage/#', '', $mediaFile->storage_path));
        $disk = Storage::disk('local');
        $disk->makeDirectory($basePath);
        $masterPlaylist = $disk->path("{$basePath}/master.m3u8");

        $variants = $mediaFile->variants()
            ->where('format', 'm3u8')
            ->where('status', 'ready')
            ->orderBy('height')
            ->get();

        if ($variants->isEmpty()) {
            return;
        }

        $content = "#EXTM3U\n";
        $content .= "#EXT-X-VERSION:3\n\n";

        foreach ($variants as $variant) {
            $bandwidth = $variant->bitrate ?: 2500000;
            $resolution = "{$variant->width}x{$variant->height}";
            $relativePath = basename(dirname($variant->storage_path)) . '/' . basename($variant->storage_path);

            $content .= "#EXT-X-STREAM-INF:BANDWIDTH={$bandwidth},RESOLUTION={$resolution}\n";
            $content .= "{$relativePath}\n\n";
        }

        file_put_contents($masterPlaylist, $content);

        // Enregistrer le chemin du manifeste dans les métadonnées
        $mediaFile->setMeta('hls.manifest_path', 'storage/' . $basePath . '/master.m3u8');
    }

    /**
     * Vérifier si FFmpeg est disponible
     */
    public function isFFmpegAvailable(): bool
    {
        exec("{$this->ffmpegPath} -version 2>&1", $output, $returnCode);
        return $returnCode === 0;
    }
}

