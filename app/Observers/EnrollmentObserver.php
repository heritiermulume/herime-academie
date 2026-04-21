<?php

namespace App\Observers;

use App\Models\Enrollment;
use App\Services\ContentRatingReminderService;

class EnrollmentObserver
{
    public function created(Enrollment $enrollment): void
    {
        $this->syncReminderForEnrollment($enrollment);
    }

    public function updated(Enrollment $enrollment): void
    {
        if (! $enrollment->wasChanged('status')) {
            return;
        }

        if (! in_array($enrollment->status, ['active', 'completed'], true)) {
            return;
        }

        $this->syncReminderForEnrollment($enrollment);
    }

    private function syncReminderForEnrollment(Enrollment $enrollment): void
    {
        try {
            app(ContentRatingReminderService::class)->ensureForEnrollment($enrollment);
        } catch (\Throwable $e) {
            \Log::warning('ContentRatingReminderService::ensureForEnrollment: '.$e->getMessage(), [
                'enrollment_id' => $enrollment->id,
                'status' => $enrollment->status,
            ]);
        }
    }
}
