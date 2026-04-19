<?php

namespace App\Observers;

use App\Models\Enrollment;
use App\Services\ContentRatingReminderService;

class EnrollmentObserver
{
    public function created(Enrollment $enrollment): void
    {
        try {
            app(ContentRatingReminderService::class)->ensureForEnrollment($enrollment);
        } catch (\Throwable $e) {
            \Log::warning('ContentRatingReminderService::ensureForEnrollment: '.$e->getMessage(), [
                'enrollment_id' => $enrollment->id,
            ]);
        }
    }
}
