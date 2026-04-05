<?php

namespace App\Console\Commands;

use App\Services\VideoOptimizationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class OptimizeVideosForStreaming extends Command
{
    protected $signature = 'videos:optimize-streaming
                            {--dry-run : Afficher les fichiers sans les modifier}';

    protected $description = 'Optimise les MP4 existants pour le streaming (moov atom au début). À exécuter une fois pour les vidéos déjà uploadées.';

    public function handle(VideoOptimizationService $optimizer): int
    {
        $dryRun = $this->option('dry-run');

        if (! $optimizer->isFFmpegAvailable()) {
            $this->error('FFmpeg n\'est pas disponible. Installez FFmpeg ou désactivez VIDEO_OPTIMIZE_FASTSTART.');

            return self::FAILURE;
        }

        $disk = Storage::disk('local');
        $folders = [
            'courses/lessons',
            'courses/previews',
            'packages/covers',
            'site/community-home',
            'tmp/uploads/courses/lessons',
            'tmp/uploads/courses/previews',
            'tmp/uploads/packages/covers',
            'tmp/uploads/site/community-home',
        ];

        $count = 0;
        $optimized = 0;

        foreach ($folders as $folder) {
            if (! $disk->exists($folder)) {
                continue;
            }

            $files = $disk->allFiles($folder);
            foreach ($files as $file) {
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if ($ext !== 'mp4' && $ext !== 'm4v') {
                    continue;
                }

                $count++;
                $fullPath = $disk->path($file);

                if ($dryRun) {
                    $this->line("  [dry-run] {$file}");

                    continue;
                }

                if ($optimizer->optimizeForStreaming($fullPath)) {
                    $optimized++;
                    $this->info("  ✓ {$file}");
                }
            }
        }

        if ($dryRun) {
            $this->info("\n{$count} fichier(s) MP4 trouvé(s). Exécutez sans --dry-run pour optimiser.");
        } else {
            $this->info("\n{$optimized}/{$count} vidéo(s) optimisée(s).");
        }

        return self::SUCCESS;
    }
}
