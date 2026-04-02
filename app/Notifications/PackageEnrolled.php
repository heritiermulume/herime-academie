<?php

namespace App\Notifications;

use App\Models\ContentPackage;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PackageEnrolled extends Notification
{
    use Queueable;

    public function __construct(
        public ContentPackage $package,
        public ?Order $order = null
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $packUrl = route('customer.pack', $this->package);

        return [
            'type' => 'package_enrolled',
            'title' => 'Accès au pack',
            'message' => 'Vous avez accès au pack « ' . $this->package->title . ' » et à tous ses contenus. Ouvrez la page du pack pour commencer.',
            'package_id' => $this->package->id,
            'package_title' => $this->package->title,
            'package_slug' => $this->package->slug,
            'order_id' => $this->order?->id,
            'button_text' => 'Voir mon pack',
            'button_url' => $packUrl,
        ];
    }
}
