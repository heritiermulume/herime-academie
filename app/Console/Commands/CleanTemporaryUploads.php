<?php

namespace App\Console\Commands;

use App\Services\TemporaryUploadCleaner;
use Illuminate\Console\Command;

class CleanTemporaryUploads extends Command
{
    protected $signature = 'uploads:clean-temp {--minutes= : Âge maximum des fichiers temporaires en minutes}';

    protected $description = 'Supprime les fichiers d\'upload temporaires périmés.';

    public function handle(TemporaryUploadCleaner $cleaner): int
    {
        $maxAge = (int) ($this->option('minutes') ?: config('uploads.temporary.max_age_minutes', 1440));
        $deleted = $cleaner->clean($maxAge);

        $this->info(sprintf('%d fichier(s) temporaire(s) supprimé(s).', $deleted));

        return Command::SUCCESS;
    }
}





