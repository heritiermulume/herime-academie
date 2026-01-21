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
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $user;
    public $subject;
    public $content;
    public $attachmentPaths;
    public $recipientType;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user, string $subject, string $content, array $attachmentPaths = [], string $recipientType = 'custom')
    {
        $this->user = $user;
        $this->subject = $subject;
        $this->content = $content;
        $this->attachmentPaths = $attachmentPaths;
        $this->recipientType = $recipientType;
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
                
                // Enregistrer l'échec
                SentEmail::create([
                    'user_id' => $this->user->id,
                    'recipient_email' => $this->user->email,
                    'recipient_name' => $this->user->name,
                    'subject' => $this->subject,
                    'content' => $this->content,
                    'attachments' => $this->attachmentPaths ?: null,
                    'type' => $this->recipientType,
                    'status' => 'failed',
                    'error_message' => $errorMessage,
                    'metadata' => [
                        'recipient_type' => $this->recipientType,
                    ],
                ]);
                
                // Lancer une exception pour que le job soit marqué comme échoué
                throw new \Exception($errorMessage);
            }
            
            // Enregistrer l'email envoyé avec succès
            SentEmail::create([
                'user_id' => $this->user->id,
                'recipient_email' => $this->user->email,
                'recipient_name' => $this->user->name,
                'subject' => $this->subject,
                'content' => $this->content,
                'attachments' => $this->attachmentPaths ?: null,
                'type' => $this->recipientType,
                'status' => 'sent',
                'sent_at' => now(),
                'metadata' => [
                    'recipient_type' => $this->recipientType,
                ],
            ]);
            
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
            
            // Enregistrer l'échec si ce n'est pas déjà fait
            $existingEmail = SentEmail::where('user_id', $this->user->id)
                ->where('subject', $this->subject)
                ->where('recipient_email', $this->user->email)
                ->where('created_at', '>=', now()->subMinute())
                ->first();
            
            if (!$existingEmail) {
                SentEmail::create([
                    'user_id' => $this->user->id,
                    'recipient_email' => $this->user->email,
                    'recipient_name' => $this->user->name,
                    'subject' => $this->subject,
                    'content' => $this->content,
                    'attachments' => $this->attachmentPaths ?: null,
                    'type' => $this->recipientType,
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'metadata' => [
                        'recipient_type' => $this->recipientType,
                    ],
                ]);
            }
            
            // Relancer l'exception pour que le job soit marqué comme échoué dans la queue
            throw $e;
        }
    }
}
