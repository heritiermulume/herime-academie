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
            $communicationService->sendEmailAndWhatsApp($this->user, $mailable);
            
            // Enregistrer l'email envoyé
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
            Log::error("Erreur lors de l'envoi d'email à {$this->user->email}: " . $e->getMessage());
            
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
                'error_message' => $e->getMessage(),
                'metadata' => [
                    'recipient_type' => $this->recipientType,
                ],
            ]);
            
            // Ne pas relancer le job en cas d'erreur - l'erreur est déjà enregistrée
            // throw $e; // Commenté pour éviter de bloquer la queue
        }
    }
}
