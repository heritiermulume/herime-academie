<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * Optimise les MP4 pour le streaming progressif (lecture fluide).
 * Place le moov atom au début du fichier (faststart) pour permettre
 * au navigateur de démarrer la lecture sans télécharger tout le fichier.
 */
class VideoOptimizationService
{
    protected string $ffmpegPath = 'ffmpeg';

    /**
     * Optimise un MP4 pour le streaming (moov atom au début).
     * Opération rapide : copie sans ré-encodage.
     *
     * @param string $fullPath Chemin complet du fichier MP4
     * @return bool True si optimisé, false sinon
     */
    public function optimizeForStreaming(string $fullPath): bool
    {
        if (!config('video.optimize_faststart', true)) {
            return false;
        }

        if (!file_exists($fullPath) || !is_readable($fullPath)) {
            Log::warning('VideoOptimizationService: fichier inaccessible', ['path' => $fullPath]);

            return false;
        }

        $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
        if ($extension !== 'mp4' && $extension !== 'm4v') {
            return false;
        }

        if (!$this->isFFmpegAvailable()) {
            Log::debug('VideoOptimizationService: FFmpeg non disponible, skip faststart');

            return false;
        }

        $dir = dirname($fullPath);
        $tempPath = $dir . '/.tmp_faststart_' . basename($fullPath);

        try {
            $command = sprintf(
                '%s -i %s -c copy -movflags +faststart -y %s 2>&1',
                escapeshellcmd($this->ffmpegPath),
                escapeshellarg($fullPath),
                escapeshellarg($tempPath)
            );

            exec($command, $output, $returnCode);

            if ($returnCode === 0 && file_exists($tempPath) && filesize($tempPath) > 0) {
                if (rename($tempPath, $fullPath)) {
                    Log::info('VideoOptimizationService: MP4 optimisé pour streaming', ['path' => $fullPath]);

                    return true;
                }
            }

            if (file_exists($tempPath)) {
                @unlink($tempPath);
            }

            if ($returnCode !== 0) {
                Log::warning('VideoOptimizationService: FFmpeg a échoué', [
                    'path' => $fullPath,
                    'return_code' => $returnCode,
                    'output' => implode("\n", $output),
                ]);
            }
        } catch (\Throwable $e) {
            if (file_exists($tempPath ?? '')) {
                @unlink($tempPath);
            }
            Log::error('VideoOptimizationService: erreur', ['path' => $fullPath, 'message' => $e->getMessage()]);
        }

        return false;
    }

    public function isFFmpegAvailable(): bool
    {
        exec(escapeshellcmd($this->ffmpegPath) . ' -version 2>&1', $output, $returnCode);

        return $returnCode === 0;
    }
}
