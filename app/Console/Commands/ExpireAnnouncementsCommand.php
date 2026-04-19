<?php

namespace App\Console\Commands;

use App\Models\Announcement;
use Illuminate\Console\Command;

class ExpireAnnouncementsCommand extends Command
{
    protected $signature = 'announcements:expire';

    protected $description = 'Désactive les annonces actives dont la date de fin (expires_at) est dépassée';

    public function handle(): int
    {
        $count = Announcement::query()
            ->where('is_active', true)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->update(['is_active' => false]);

        if ($count > 0) {
            $this->info("{$count} annonce(s) expirée(s) et désactivée(s).");
        }

        return self::SUCCESS;
    }
}
