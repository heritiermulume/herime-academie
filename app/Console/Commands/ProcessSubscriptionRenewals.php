<?php

namespace App\Console\Commands;

use App\Services\SubscriptionService;
use Illuminate\Console\Command;

class ProcessSubscriptionRenewals extends Command
{
    protected $signature = 'subscriptions:process-renewals';

    protected $description = 'Traite tous les renouvellements (tous utilisateurs). En prod, le planificateur dispatch aussi ProcessSubscriptionRenewalsJob (file d\'attente).';

    public function handle(SubscriptionService $subscriptionService): int
    {
        $processed = $subscriptionService->processRenewals();
        $this->info("Subscription renewals processed: {$processed}");

        return self::SUCCESS;
    }
}
