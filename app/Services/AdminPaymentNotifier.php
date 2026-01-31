<?php

namespace App\Services;

use App\Mail\AdminPaymentReceivedMail;
use App\Models\Order;
use App\Models\User;
use App\Notifications\AdminPaymentReceived;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class AdminPaymentNotifier
{
    /**
     * Envoyer email + notification aux admins/super_user quand un paiement est confirmé.
     * 
     * Idempotent (par admin): si une notification existe déjà pour cette commande,
     * on ne renvoie pas (évite les doublons webhook/redirection).
     */
    public function notify(Order $order): void
    {
        try {
            // Charger ce dont on a besoin pour l'email
            if (!$order->relationLoaded('user')) {
                $order->load('user');
            }
            if (!$order->relationLoaded('orderItems')) {
                $order->load('orderItems.course');
            }

            $admins = User::admins()
                ->whereNotNull('email')
                ->where('is_active', true)
                ->get();

            if ($admins->isEmpty()) {
                return;
            }

            foreach ($admins as $admin) {
                // Anti-doublon par admin & commande
                $alreadyNotified = DatabaseNotification::query()
                    ->where('notifiable_type', User::class)
                    ->where('notifiable_id', $admin->id)
                    ->where('type', AdminPaymentReceived::class)
                    ->where('data->order_id', $order->id)
                    ->exists();

                if ($alreadyNotified) {
                    continue;
                }

                // 1) Email
                try {
                    Mail::to($admin->email)->send(new AdminPaymentReceivedMail($order, $admin));
                } catch (\Throwable $e) {
                    Log::error("AdminPaymentNotifier: erreur envoi email admin paiement", [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'admin_id' => $admin->id,
                        'admin_email' => $admin->email,
                        'error' => $e->getMessage(),
                    ]);
                }

                // 2) Notification in-app
                try {
                    Notification::sendNow($admin, new AdminPaymentReceived($order));
                } catch (\Throwable $e) {
                    Log::error("AdminPaymentNotifier: erreur envoi notification admin paiement", [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'admin_id' => $admin->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            Log::error("AdminPaymentNotifier: erreur globale", [
                'order_id' => $order->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

