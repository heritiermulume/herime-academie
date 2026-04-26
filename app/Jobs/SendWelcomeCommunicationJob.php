<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\CommunicationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendWelcomeCommunicationJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 120;

    public int $tries = 3;

    public function __construct(public int $userId) {}

    public function uniqueId(): string
    {
        return 'welcome-communication-'.$this->userId;
    }

    public function uniqueFor(): int
    {
        // Couvre le délai d’envoi (30 min) + marge pour éviter un second job concurrent.
        return 45 * 60;
    }

    public function handle(CommunicationService $communicationService): void
    {
        $user = User::query()->find($this->userId);
        if (! $user) {
            Log::warning('SendWelcomeCommunicationJob: utilisateur introuvable', [
                'user_id' => $this->userId,
            ]);

            return;
        }

        $communicationService->sendWelcomeCommunicationOnce($user);
    }
}
