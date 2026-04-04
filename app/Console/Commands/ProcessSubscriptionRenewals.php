<?php

namespace App\Console\Commands;

use App\Services\SubscriptionService;
use Illuminate\Console\Command;

class ProcessSubscriptionRenewals extends Command
{
    protected $signature = 'subscriptions:process-renewals';
    protected $description = 'Traite tous les renouvellements (tous utilisateurs). En prod, les clients sont aussi mis à jour à la visite (middleware web).';

    public function handle(SubscriptionService $subscriptionService): int
    {
        $processed = $subscriptionService->processRenewals();
        $this->info("Subscription renewals processed: {$processed}");

        return self::SUCCESS;
    }
}

