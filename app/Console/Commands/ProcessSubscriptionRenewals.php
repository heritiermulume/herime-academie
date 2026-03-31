<?php

namespace App\Console\Commands;

use App\Services\SubscriptionService;
use Illuminate\Console\Command;

class ProcessSubscriptionRenewals extends Command
{
    protected $signature = 'subscriptions:process-renewals';
    protected $description = 'Process recurring subscription renewals and invoices';

    public function handle(SubscriptionService $subscriptionService): int
    {
        $processed = $subscriptionService->processRenewals();
        $this->info("Subscription renewals processed: {$processed}");

        return self::SUCCESS;
    }
}

