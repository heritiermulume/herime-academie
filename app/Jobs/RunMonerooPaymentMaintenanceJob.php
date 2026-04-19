<?php

namespace App\Jobs;

use App\Http\Controllers\MonerooController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RunMonerooPaymentMaintenanceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 900;

    public function handle(MonerooController $monerooController): void
    {
        $monerooController->runScheduledPaymentMaintenance();
    }
}
