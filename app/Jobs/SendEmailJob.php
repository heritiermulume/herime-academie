<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\SentEmail;
use App\Mail\CustomAnnouncementMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $user;
    public $subject;
    public $content;
    public $attachmentPaths;
    public $recipientType;
    public $sentEmailId;

    /**
     * Create a new job instance.
     */
    public function __construct(
        User $user,
        string $subject,
        string $content,
        array $attachmentPaths = [],
        string $recipientType = 'custom',
        ?int $sentEmailId = null
    )
    {
        $this->user = $user;
        $this->subject = $subject;
        $this->content = $content;
        $this->attachmentPaths = $attachmentPaths;
        $this->recipientType = $recipientType;
        $this->sentEmailId = $sentEmailId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Envoyer l'email et WhatsApp en parallèle
            $mailable = new CustomAnnouncementMail(
                $this->subject,
                $this->content,
                $this->attachmentPaths
            );
            $communicationService = app(\App\Services\CommunicationService::class);
            $results = $communicationService->sendEmailAndWhatsApp($this->user, $mailable, null, false);
            
            // Vérifier si l'envoi d'email a réussi
            if (!$results['email']['success']) {
                $errorMessage = $results['email']['error'] ?? 'Erreur inconnue lors de l\'envoi de l\'email';
                Log::error("Échec de l'envoi d'email à {$this->user->email}: {$errorMessage}");
                
                $this->storeFailedEmail($errorMessage);
                
                // Lancer une exception pour que le job soit marqué comme échoué
                throw new \Exception($errorMessage);
            }
            
            $this->storeSentEmail();
            
            // Notifier l'utilisateur qu'un email lui a été envoyé
            try {
                Notification::sendNow($this->user, new \App\Notifications\EmailSentNotification($this->subject, now()));
            } catch (\Exception $e) {
                // Ne pas faire échouer le job si la notification échoue
                Log::warning("Impossible d'envoyer la notification email à {$this->user->email}: " . $e->getMessage());
            }
        } catch (\Exception $e) {
            Log::error("Erreur lors de l'envoi d'email à {$this->user->email}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->storeFailedEmail($e->getMessage());
            
            // Relancer l'exception pour que le job soit marqué comme échoué dans la queue
            throw $e;
        }
    }

    protected function storeSentEmail(): void
    {
        $existingEmail = $this->resolveTrackedEmail();
        if ($existingEmail) {
            $existingEmail->update([
                'status' => 'sent',
                'error_message' => null,
                'sent_at' => now(),
            ]);

            return;
        }

        SentEmail::create($this->baseEmailPayload([
            'status' => 'sent',
            'sent_at' => now(),
        ]));
    }

    protected function storeFailedEmail(string $errorMessage): void
    {
        $existingEmail = $this->resolveTrackedEmail();
        if ($existingEmail) {
            $existingEmail->update([
                'status' => 'failed',
                'error_message' => $errorMessage,
                'sent_at' => null,
            ]);

            return;
        }

        $recentFailure = SentEmail::where('user_id', $this->user->id)
            ->where('subject', $this->subject)
            ->where('recipient_email', $this->user->email)
            ->where('created_at', '>=', now()->subMinute())
            ->first();

        if ($recentFailure) {
            $recentFailure->update([
                'status' => 'failed',
                'error_message' => $errorMessage,
                'sent_at' => null,
            ]);

            return;
        }

        SentEmail::create($this->baseEmailPayload([
            'status' => 'failed',
            'error_message' => $errorMessage,
            'sent_at' => null,
        ]));
    }

    protected function resolveTrackedEmail(): ?SentEmail
    {
        if ($this->sentEmailId) {
            $tracked = SentEmail::find($this->sentEmailId);
            if ($tracked) {
                return $tracked;
            }
        }

        return SentEmail::where('user_id', $this->user->id)
            ->where('subject', $this->subject)
            ->where('recipient_email', $this->user->email)
            ->whereIn('status', ['pending', 'failed'])
            ->where('created_at', '>=', now()->subMinutes(5))
            ->first();
    }

    protected function baseEmailPayload(array $overrides = []): array
    {
        return array_merge([
            'user_id' => $this->user->id,
            'recipient_email' => $this->user->email,
            'recipient_name' => $this->user->name,
            'subject' => $this->subject,
            'content' => $this->content,
            'attachments' => $this->attachmentPaths ?: null,
            'type' => 'custom',
            'metadata' => [
                'recipient_type' => $this->recipientType,
            ],
        ], $overrides);
    }
}
