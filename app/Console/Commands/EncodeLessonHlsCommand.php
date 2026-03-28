<?php

namespace App\Console\Commands;

use App\Jobs\EncodeLessonVideoToHls;
use App\Models\CourseLesson;
use Illuminate\Console\Command;

class EncodeLessonHlsCommand extends Command
{
    protected $signature = 'lesson:encode-hls
        {lesson : ID de la leçon (content_lessons)}
        {--sync : Exécuter sans file d\'attente (debug / serveur sans queue)}';

    protected $description = 'Encode une leçon vidéo en HLS multi-débits (FFmpeg)';

    public function handle(): int
    {
        if (! config('video.hls.enabled')) {
            $this->error('VIDEO_HLS_ENABLED est désactivé. Activez-le dans .env pour encoder.');

            return self::FAILURE;
        }

        $lesson = CourseLesson::query()->find($this->argument('lesson'));
        if (! $lesson) {
            $this->error('Leçon introuvable.');

            return self::FAILURE;
        }

        if ($lesson->type !== 'video') {
            $this->error('La leçon n\'est pas de type vidéo.');

            return self::FAILURE;
        }

        if (! empty($lesson->youtube_video_id)) {
            $this->error('Leçon YouTube : pas d\'encodage HLS côté fichier.');

            return self::FAILURE;
        }

        if (! $lesson->getInternalStorageVideoPath()) {
            $this->error('Aucune vidéo hébergée (file_path / content_url) trouvée.');

            return self::FAILURE;
        }

        if ($this->option('sync')) {
            $this->info('Encodage synchrone…');
            $job = new EncodeLessonVideoToHls($lesson->id);
            $job->handle(app(\App\Services\HlsEncodingService::class));
            $lesson->refresh();
            $this->line('Statut HLS : '.($lesson->hls_status ?? 'n/a'));
            if ($lesson->hls_manifest_path) {
                $this->line('Manifest : '.$lesson->hls_manifest_path);
            }
        } else {
            EncodeLessonVideoToHls::dispatch($lesson->id);
            $this->info('Job mis en file d\'attente. Lancez : php artisan queue:work');
        }

        return self::SUCCESS;
    }
}
