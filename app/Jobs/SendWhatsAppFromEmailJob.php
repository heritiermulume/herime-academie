<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\SentWhatsAppMessage;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendWhatsAppFromEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $user;
    public $message;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user, string $message)
    {
        $this->user = $user;
        $this->message = $message;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info("SendWhatsAppFromEmailJob démarré", [
                'user_id' => $this->user->id,
                'user_phone' => $this->user->phone,
                'message_length' => strlen($this->message)
            ]);
            
            if (!$this->user->phone) {
                Log::warning("Tentative d'envoi WhatsApp à un utilisateur sans numéro", [
                    'user_id' => $this->user->id,
                    'user_email' => $this->user->email
                ]);
                return;
            }

            $whatsappService = app(WhatsAppService::class);
            
            Log::info("Envoi du message WhatsApp via WhatsAppService", [
                'user_id' => $this->user->id,
                'phone' => $this->user->phone
            ]);
            
            $result = $whatsappService->sendMessage($this->user->phone, $this->message);
            
            Log::info("Résultat de l'envoi WhatsApp", [
                'user_id' => $this->user->id,
                'phone' => $this->user->phone,
                'success' => $result['success'] ?? false,
                'message_id' => $result['message_id'] ?? null,
                'error' => $result['error'] ?? null
            ]);
            
            SentWhatsAppMessage::create([
                'user_id' => $this->user->id,
                'recipient_phone' => $this->user->phone,
                'recipient_name' => $this->user->name,
                'message_id' => $result['message_id'] ?? null,
                'message' => $this->message,
                'type' => 'auto',
                'status' => $result['success'] ? 'sent' : 'failed',
                'error_message' => $result['error'] ?? null,
                'sent_at' => $result['success'] ? now() : null,
                'metadata' => [
                    'source' => 'email_auto',
                    'recipient_type' => 'auto',
                ],
            ]);

            if ($result['success']) {
                Log::info("WhatsApp envoyé avec succès depuis email", [
                    'user_id' => $this->user->id,
                    'phone' => $this->user->phone
                ]);
            } else {
                Log::warning("Échec envoi WhatsApp depuis email", [
                    'user_id' => $this->user->id,
                    'phone' => $this->user->phone,
                    'error' => $result['error']
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Erreur lors de l'envoi WhatsApp depuis email", [
                'user_id' => $this->user->id,
                'phone' => $this->user->phone ?? null,
                'error' => $e->getMessage()
            ]);
            
            // Enregistrer l'échec dans la base de données
            try {
                SentWhatsAppMessage::create([
                    'user_id' => $this->user->id,
                    'recipient_phone' => $this->user->phone,
                    'recipient_name' => $this->user->name,
                    'message' => $this->message,
                    'type' => 'auto',
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'metadata' => [
                        'source' => 'email_auto',
                        'recipient_type' => 'auto',
                    ],
                ]);
            } catch (\Exception $dbError) {
                Log::error("Impossible d'enregistrer l'échec WhatsApp dans la base", [
                    'error' => $dbError->getMessage()
                ]);
            }
        }
    }
}

