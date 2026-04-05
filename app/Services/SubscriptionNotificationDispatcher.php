<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification as NotificationFacade;

/**
 * Envoie les notifications liées aux abonnements sans qu’un échec (mail, canal, etc.) bloque la suite.
 */
final class SubscriptionNotificationDispatcher
{
    public static function notifyUser(?User $user, Notification $notification, string $logLabel, array $logContext = []): void
    {
        if (! $user) {
            return;
        }

        try {
            $user->notify($notification);
        } catch (\Throwable $e) {
            Log::error('SubscriptionNotificationDispatcher: échec notification utilisateur', array_merge([
                'label' => $logLabel,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ], $logContext));
        }
    }

    public static function notifyAdmins(Notification $notification, string $logLabel, array $logContext = []): void
    {
        $admins = User::admins()
            ->whereNotNull('email')
            ->where('is_active', true)
            ->get();

        foreach ($admins as $admin) {
            try {
                NotificationFacade::sendNow($admin, $notification);
            } catch (\Throwable $e) {
                Log::error('SubscriptionNotificationDispatcher: échec notification admin', array_merge([
                    'label' => $logLabel,
                    'admin_id' => $admin->id,
                    'error' => $e->getMessage(),
                ], $logContext));
            }
        }
    }
}
