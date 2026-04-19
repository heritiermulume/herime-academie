<?php

namespace App\Console\Commands;

use App\Models\Enrollment;
use App\Services\ContentRatingReminderService;
use Illuminate\Console\Command;

class SyncContentRatingRemindersCommand extends Command
{
    protected $signature = 'content-rating-reminders:sync {--days=90 : Inscriptions depuis N jours au plus}';

    protected $description = 'Crée les suivis de relance notation pour les inscriptions existantes sans avis (idempotent)';

    public function handle(ContentRatingReminderService $service): int
    {
        $days = max(1, (int) $this->option('days'));
        $since = now()->subDays($days);

        $processed = 0;
        Enrollment::query()
            ->whereIn('status', ['active', 'completed'])
            ->where('created_at', '>=', $since)
            ->orderBy('id')
            ->chunkById(200, function ($enrollments) use ($service, &$processed) {
                foreach ($enrollments as $enrollment) {
                    $service->ensureForEnrollment($enrollment);
                    $processed++;
                }
            });

        $this->info("Traité {$processed} inscription(s) (relances créées ou inchangées).");

        return self::SUCCESS;
    }
}
