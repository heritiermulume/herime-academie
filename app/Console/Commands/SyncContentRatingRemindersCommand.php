<?php

namespace App\Console\Commands;

use App\Models\Enrollment;
use App\Services\ContentRatingReminderService;
use Illuminate\Console\Command;

class SyncContentRatingRemindersCommand extends Command
{
    protected $signature = 'content-rating-reminders:sync {--days= : Inscriptions depuis N jours au plus (laisser vide pour toutes)}';

    protected $description = 'Crée les suivis de relance notation pour les inscriptions existantes sans avis (idempotent)';

    public function handle(ContentRatingReminderService $service): int
    {
        $daysOption = $this->option('days');
        $days = is_numeric($daysOption) ? max(1, (int) $daysOption) : null;

        $processed = 0;
        $query = Enrollment::query()
            ->whereIn('status', ['active', 'completed'])
            ->orderBy('id');

        if ($days !== null) {
            $query->where('created_at', '>=', now()->subDays($days));
        }

        $query
            ->chunkById(200, function ($enrollments) use ($service, &$processed) {
                foreach ($enrollments as $enrollment) {
                    $service->ensureForEnrollment($enrollment);
                    $processed++;
                }
            });

        $scopeLabel = $days !== null ? "sur {$days} jour(s)" : 'sur toutes les inscriptions';
        $this->info("Traité {$processed} inscription(s) {$scopeLabel} (relances créées ou inchangées).");

        return self::SUCCESS;
    }
}
