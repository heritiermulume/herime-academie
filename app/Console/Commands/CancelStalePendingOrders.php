<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Console\Command;

class CancelStalePendingOrders extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'orders:cancel-stale {--minutes= : Override timeout in minutes}';

    /**
     * The console command description.
     */
    protected $description = 'Annule automatiquement les commandes en attente dépassant le délai (et marque les paiements comme échoués).';

    public function handle(): int
    {
        $timeoutMinutes = (int) ($this->option('minutes') ?? env('ORDER_PENDING_TIMEOUT_MIN', 30));
        $threshold = now()->subMinutes($timeoutMinutes);

        $staleOrders = Order::where('status', 'pending')
            ->where('created_at', '<', $threshold)
            ->get();

        if ($staleOrders->isEmpty()) {
            $this->info('Aucune commande en attente à annuler.');
            return self::SUCCESS;
        }

        $count = 0;
        foreach ($staleOrders as $order) {
            // Ne pas toucher aux commandes déjà payées
            if (in_array($order->status, ['paid', 'completed'])) {
                continue;
            }

            $order->update(['status' => 'cancelled']);

            // Marquer les paiements liés comme échoués si toujours en attente
            Payment::where('order_id', $order->id)
                ->where('status', 'pending')
                ->update([
                    'status' => 'failed',
                ]);

            $count++;
        }

        $this->info("{$count} commande(s) annulée(s) après délai de {$timeoutMinutes} min.");
        return self::SUCCESS;
    }
}


