<?php

namespace App\Console\Commands;

use App\Jobs\EncodeCommunityHomeVideoToHls;
use App\Jobs\EncodeCourseVideoPreviewToHls;
use App\Jobs\EncodeLessonVideoToHls;
use App\Jobs\EncodePackageCoverVideoToHls;
use App\Models\ContentPackage;
use App\Models\Course;
use App\Models\CourseLesson;
use Illuminate\Console\Command;

class QueueHostedVideoHlsCommand extends Command
{
    protected $signature = 'video:queue-hosted-hls
        {--community : Inclure la vidéo d’accueil communauté (fichier interne)}';

    protected $description = 'Met en file d’attente l’encodage HLS (leçons, aperçus cours, couvertures pack, optionnellement média communauté)';

    public function handle(): int
    {
        if (! config('video.hls.enabled')) {
            $this->error('VIDEO_HLS_ENABLED n’est pas actif dans .env.');

            return self::FAILURE;
        }

        $n = 0;

        CourseLesson::query()->where('type', 'video')->whereNull('youtube_video_id')
            ->where(function ($q) {
                $q->whereNotNull('file_path')->orWhereNotNull('content_url');
            })
            ->orderBy('id')
            ->chunkById(100, function ($lessons) use (&$n) {
                foreach ($lessons as $lesson) {
                    if ($lesson->getInternalStorageVideoPath()) {
                        EncodeLessonVideoToHls::dispatch($lesson->id);
                        $n++;
                    }
                }
            });

        Course::query()->whereNotNull('video_preview')->whereNull('video_preview_youtube_id')
            ->orderBy('id')
            ->chunkById(100, function ($courses) use (&$n) {
                foreach ($courses as $course) {
                    $p = $course->video_preview;
                    if ($p && ! filter_var($p, FILTER_VALIDATE_URL)) {
                        $ext = strtolower(pathinfo($p, PATHINFO_EXTENSION));
                        if (in_array($ext, ['mp4', 'm4v', 'mov'], true)) {
                            EncodeCourseVideoPreviewToHls::dispatch($course->id);
                            $n++;
                        }
                    }
                }
            });

        ContentPackage::query()->whereNotNull('cover_video')->whereNull('cover_video_youtube_id')
            ->orderBy('id')
            ->chunkById(100, function ($packages) use (&$n) {
                foreach ($packages as $package) {
                    $p = $package->cover_video;
                    if ($p && ! filter_var($p, FILTER_VALIDATE_URL)) {
                        $ext = strtolower(pathinfo($p, PATHINFO_EXTENSION));
                        if (in_array($ext, ['mp4', 'm4v', 'mov'], true)) {
                            EncodePackageCoverVideoToHls::dispatch($package->id);
                            $n++;
                        }
                    }
                }
            });

        if ($this->option('community')) {
            EncodeCommunityHomeVideoToHls::dispatch();
            $n++;
        }

        $this->info("{$n} job(s) mis en file. Exécutez : php artisan queue:work");

        return self::SUCCESS;
    }
}
