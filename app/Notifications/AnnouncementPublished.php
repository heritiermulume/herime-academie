<?php

namespace App\Notifications;

use App\Models\Announcement;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class AnnouncementPublished extends Notification
{
    protected Announcement $announcement;

    public function __construct(Announcement $announcement)
    {
        $this->announcement = $announcement;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'announcement_id' => $this->announcement->id,
            'title' => $this->announcement->title,
            'excerpt' => Str::limit(strip_tags($this->announcement->content), 200),
            'type' => $this->announcement->type,
            'button_text' => $this->announcement->button_text,
            'button_url' => $this->announcement->button_url,
            'starts_at' => optional($this->announcement->starts_at)->toIso8601String(),
            'expires_at' => optional($this->announcement->expires_at)->toIso8601String(),
        ];
    }
}


