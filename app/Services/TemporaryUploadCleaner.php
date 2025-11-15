<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TemporaryUploadCleaner
{
    /**
     * Supprimer les fichiers temporaires plus anciens que $maxAgeMinutes.
     *
     * @return int Nombre de fichiers supprimés
     */
    public function clean(?int $maxAgeMinutes = null): int
    {
        $disk = Storage::disk('local');
        $basePath = FileUploadService::TEMPORARY_BASE_PATH;

        $maxAgeMinutes = $maxAgeMinutes ?? (int) config('uploads.temporary.max_age_minutes', 1440);
        $threshold = now()->subMinutes($maxAgeMinutes)->getTimestamp();
        $deletedCount = 0;

        if ($disk->exists($basePath)) {
            $deletedCount += $this->cleanDirectory($disk, $basePath, $threshold, 'TemporaryUploadCleaner');
        }

        foreach (['chunks', 'private/chunks'] as $chunkDir) {
            if ($disk->exists($chunkDir)) {
                $deletedCount += $this->cleanDirectory($disk, $chunkDir, $threshold, 'TemporaryUploadCleaner (chunks)', true);
            }
        }

        return $deletedCount;
    }

    protected function cleanDirectory($disk, string $path, int $threshold, string $logContext, bool $ignoreThreshold = false): int
    {
        $deletedCount = 0;

        try {
            $files = $disk->allFiles($path);
        } catch (\Throwable $e) {
            Log::warning("{$logContext}: impossible de lister les fichiers", [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
            return 0;
        }

        foreach ($files as $file) {
            try {
                $lastModified = $disk->lastModified($file);
            } catch (\Throwable $e) {
                Log::debug("{$logContext}: impossible de récupérer la date de modification", [
                    'file' => $file,
                    'error' => $e->getMessage(),
                ]);
                continue;
            }

            if (($ignoreThreshold || $lastModified <= $threshold) && $disk->delete($file)) {
                $deletedCount++;
            }
        }

        $this->removeEmptyDirectories($disk, $path);

        return $deletedCount;
    }

    protected function removeEmptyDirectories($disk, string $basePath): void
    {
        try {
            $directories = $disk->allDirectories($basePath);
        } catch (\Throwable $e) {
            Log::debug('TemporaryUploadCleaner: impossible de lister les dossiers temporaires', [
                'error' => $e->getMessage(),
            ]);
            return;
        }

        // Supprimer les répertoires en partant des plus profonds
        rsort($directories);

        foreach ($directories as $directory) {
            try {
                $files = $disk->files($directory);
                $subDirs = $disk->directories($directory);
            } catch (\Throwable $e) {
                Log::debug('TemporaryUploadCleaner: impossible de lire le dossier', [
                    'directory' => $directory,
                    'error' => $e->getMessage(),
                ]);
                continue;
            }

            if (empty($files) && empty($subDirs)) {
                $disk->deleteDirectory($directory);
            }
        }
    }
}

