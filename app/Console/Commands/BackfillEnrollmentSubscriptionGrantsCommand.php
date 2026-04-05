<?php

namespace App\Console\Commands;

use App\Models\Course;
use App\Models\Enrollment;
use App\Services\EnrollmentSubscriptionGrantBackfillService;
use Illuminate\Console\Command;

class BackfillEnrollmentSubscriptionGrantsCommand extends Command
{
    protected $signature = 'enrollments:backfill-subscription-grants
                            {--dry-run : Afficher le résultat sans modifier la base}
                            {--chunk=500 : Nombre de lignes traitées par lot}';

    protected $description = 'Renseigne access_granted_by_subscription_id sur les inscriptions sans commande (hors achat individuel détecté).';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $chunk = max(1, (int) $this->option('chunk'));

        $updated = 0;
        $skippedStandalonePurchase = 0;
        $skippedNoCourse = 0;
        $skippedNoSubscriptionMatch = 0;

        Enrollment::query()
            ->whereNull('order_id')
            ->whereNull('access_granted_by_subscription_id')
            ->orderBy('id')
            ->chunkById($chunk, function ($enrollments) use (
                $dryRun,
                &$updated,
                &$skippedStandalonePurchase,
                &$skippedNoCourse,
                &$skippedNoSubscriptionMatch
            ) {
                foreach ($enrollments as $enrollment) {
                    $course = Course::query()->find($enrollment->content_id);
                    if (! $course) {
                        $skippedNoCourse++;

                        continue;
                    }

                    if ($course->userHasValidStandalonePurchase((int) $enrollment->user_id)) {
                        $skippedStandalonePurchase++;

                        continue;
                    }

                    $subscriptionId = EnrollmentSubscriptionGrantBackfillService::findGrantingSubscriptionId(
                        (int) $enrollment->user_id,
                        $course
                    );

                    if ($subscriptionId === null) {
                        $skippedNoSubscriptionMatch++;

                        continue;
                    }

                    if (! $dryRun) {
                        Enrollment::query()->whereKey($enrollment->id)->update([
                            'access_granted_by_subscription_id' => $subscriptionId,
                        ]);
                    }

                    $updated++;
                }
            });

        $this->info($dryRun ? '[dry-run] Aucune écriture en base.' : 'Backfill terminé.');
        $this->table(
            ['Statistique', 'Nombre'],
            [
                ['Inscriptions mises à jour (ou qui le seraient en dry-run)', (string) $updated],
                ['Ignorées (achat individuel payé détecté)', (string) $skippedStandalonePurchase],
                ['Ignorées (cours introuvable)', (string) $skippedNoCourse],
                ['Ignorées (aucun abonnement ne couvre ce cours)', (string) $skippedNoSubscriptionMatch],
            ]
        );

        return self::SUCCESS;
    }
}
