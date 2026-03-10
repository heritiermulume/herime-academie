<?php

namespace App\Console\Commands;

use App\Models\AmbassadorCommission;
use App\Models\Order;
use App\Models\ProviderPayout;
use App\Services\WalletRevenueService;
use Illuminate\Console\Command;

class SyncWalletRevenue extends Command
{
    protected $signature = 'wallet:sync-revenue
                            {--dry-run : Afficher ce qui serait fait sans créditer}';

    protected $description = 'Alimente les wallets plateforme et ambassadeurs à partir des commandes et payouts existants (idempotent)';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $revenueService = app(WalletRevenueService::class);

        if ($dryRun) {
            $this->warn('Mode dry-run : aucun crédit ne sera effectué.');
            $this->newLine();
        }

        // Commandes internes payées → wallet plateforme
        $internalOrders = Order::withTrashed()
            ->whereIn('status', ['paid', 'completed'])
            ->whereDoesntHave('orderItems', function ($query) {
                $query->whereHas('content', function ($q) {
                    $q->whereHas('provider', function ($providerQuery) {
                        $providerQuery->where('role', 'provider');
                    });
                });
            })
            ->get();

        $this->info('Commandes internes payées : ' . $internalOrders->count());
        $platformOrderCredits = 0;
        foreach ($internalOrders as $order) {
            if (!$dryRun && $revenueService->creditPlatformFromOrder($order)) {
                $platformOrderCredits++;
            }
        }
        if (!$dryRun) {
            $this->info("  → Plateforme créditée pour {$platformOrderCredits} commande(s).");
        }
        $this->newLine();

        // ProviderPayout complétés → commission plateforme
        $payouts = ProviderPayout::withTrashed()->where('status', 'completed')->get();
        $this->info('Payouts prestataires complétés : ' . $payouts->count());
        $platformPayoutCredits = 0;
        foreach ($payouts as $payout) {
            if (!$dryRun && $revenueService->creditPlatformFromProviderPayout($payout)) {
                $platformPayoutCredits++;
            }
        }
        if (!$dryRun) {
            $this->info("  → Plateforme créditée pour {$platformPayoutCredits} commission(s).");
        }
        $this->newLine();

        // Commissions ambassadeurs → wallet ambassadeur (sans scope SoftDeletes au cas où la table n'a pas deleted_at)
        $commissions = AmbassadorCommission::withoutGlobalScopes()->with(['order', 'ambassador'])->get();
        $this->info('Commissions ambassadeurs : ' . $commissions->count());
        $ambassadorCredits = 0;
        foreach ($commissions as $commission) {
            if (!$commission->order) {
                continue;
            }
            if (!$dryRun && $revenueService->creditAmbassadorFromCommission($commission->order, $commission)) {
                $ambassadorCredits++;
            }
        }
        if (!$dryRun) {
            $this->info("  → Ambassadeurs crédités pour {$ambassadorCredits} commission(s).");
        }

        $this->newLine();
        $this->info('Synchronisation terminée.');

        return Command::SUCCESS;
    }
}
