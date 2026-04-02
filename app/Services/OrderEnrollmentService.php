<?php

namespace App\Services;

use App\Models\ContentPackage;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\User;

class OrderEnrollmentService
{
    public function __construct(
        protected PackageEnrollmentNotifier $packageEnrollmentNotifier
    ) {}

    /**
     * Inscriptions pour toutes les lignes : les cours issus d'un pack sont créés sans mail/notif cours ;
     * un email + une notif « pack » sont envoyés une fois par pack présent sur la commande.
     *
     * @param  iterable<\App\Models\OrderItem>  $orderItems
     * @return int Nombre de nouvelles inscriptions créées
     */
    public function syncEnrollmentsFromOrderItems(Order $order, iterable $orderItems): int
    {
        $userId = $order->user_id;
        if (! $userId) {
            return 0;
        }

        $items = collect($orderItems);
        $created = 0;

        foreach ($items as $orderItem) {
            $course = $orderItem->course;
            if (! $course) {
                continue;
            }

            $existing = Enrollment::where('user_id', $userId)
                ->where('content_id', $orderItem->content_id)
                ->first();

            $fromPack = ! empty($orderItem->content_package_id);

            if (! $existing) {
                Enrollment::createAndNotify([
                    'user_id' => $userId,
                    'content_id' => $orderItem->content_id,
                    'order_id' => $order->id,
                    'status' => 'active',
                ], ! $fromPack);
                $created++;
            } else {
                $existing->update(['order_id' => $order->id, 'status' => 'active']);
            }
        }

        $user = $order->relationLoaded('user') ? $order->user : null;
        if (! $user) {
            $user = User::find($userId);
        }
        if (! $user) {
            return $created;
        }

        $packageIds = $items->pluck('content_package_id')->filter()->unique()->map(fn ($id) => (int) $id)->values();
        foreach ($packageIds as $pid) {
            $package = ContentPackage::find($pid);
            if ($package) {
                $this->packageEnrollmentNotifier->notify($user, $package, $order);
            }
        }

        return $created;
    }
}
