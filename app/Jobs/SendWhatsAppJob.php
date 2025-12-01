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

class SendWhatsAppJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $user;
    public $message;
    public $recipientType;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user, string $message, string $recipientType = 'custom')
    {
        $this->user = $user;
        $this->message = $message;
        $this->recipientType = $recipientType;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $whatsappService = app(WhatsAppService::class);
            
            // Envoyer le message WhatsApp
            $result = $whatsappService->sendMessage($this->user->phone, $this->message);
            
            // Enregistrer le message
            SentWhatsAppMessage::create([
                'user_id' => $this->user->id,
                'recipient_phone' => $this->user->phone,
                'recipient_name' => $this->user->name,
                'message_id' => $result['message_id'] ?? null,
                'message' => $this->message,
                'type' => $this->recipientType,
                'status' => $result['success'] ? 'sent' : 'failed',
                'error_message' => $result['error'] ?? null,
                'sent_at' => $result['success'] ? now() : null,
                'metadata' => [
                    'recipient_type' => $this->recipientType,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error("Erreur lors de l'envoi WhatsApp à {$this->user->phone}: " . $e->getMessage());
            
            // Enregistrer l'échec
            SentWhatsAppMessage::create([
                'user_id' => $this->user->id,
                'recipient_phone' => $this->user->phone,
                'recipient_name' => $this->user->name,
                'message' => $this->message,
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
