<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CancelOrderIfPending implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private int $orderId)
    {
    }

    public function handle(): void
    {
        $order = Order::with('payments')->find($this->orderId);
        if (!$order) {
            return;
        }

        if (in_array($order->status, ['paid', 'completed', 'cancelled'])) {
            return;
        }

        // Annuler la commande
        $order->update(['status' => 'cancelled']);

        // Marquer les paiements liés encore en attente comme échoués
        Payment::where('order_id', $order->id)
            ->where('status', 'pending')
            ->update([
                'status' => 'failed',
                'failure_reason' => 'Annulation automatique après délai',
            ]);
    }
}


