<?php

namespace App\Observers;

use App\Models\CourseDownload;
use App\Services\ContentRatingReminderService;

class CourseDownloadObserver
{
    public function created(CourseDownload $courseDownload): void
    {
        try {
            app(ContentRatingReminderService::class)->ensureForDownload($courseDownload);
        } catch (\Throwable $e) {
            \Log::warning('ContentRatingReminderService::ensureForDownload: '.$e->getMessage(), [
                'download_id' => $courseDownload->id,
            ]);
        }
    }
}
