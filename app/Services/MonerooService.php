<?php

namespace App\Services;

use App\Models\ProviderPayout;
use App\Notifications\ProviderPayoutReceived;
use App\Mail\ProviderPayoutReceivedMail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class MonerooService
{
    private string $apiUrl;
    private string $apiKey;
    private string $callbackUrl;

    public function __construct()
    {
        $this->apiUrl = rtrim(config('services.moneroo.base_url', 'https://api.moneroo.io/v1'), '/');
        $this->apiKey = config('services.moneroo.api_key');
        $this->callbackUrl = config('services.moneroo.callback_url', route('moneroo.payout.callback'));
    }

    /**
     * Obtenir les en-têtes d'authentification pour les requêtes API
     */
    private function authHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    /**
     * Initier un payout vers un prestataire externe
     */
    public function initiatePayout(
        int $providerId,
        int $orderId,
        int $contentId,
        float $amount,
        string $currency,
        string $phoneNumber,
        string $provider,
        string $country
    ): array {
        // Générer un ID unique pour le payout
        $payoutId = 'payout_' . strtoupper(Str::random(16)) . '_' . time();

        // Préparer le payload selon la documentation Moneroo
        $payload = [
            'amount' => (string) number_format($amount, 2, '.', ''),
            'currency' => $currency,
            'recipient' => [
                'type' => 'mobile_money',
                'phone' => $phoneNumber,
                'provider' => $provider,
                'country' => $country,
            ],
            'callback_url' => $this->callbackUrl,
            'metadata' => [
                'payout_id' => $payoutId,
                'provider_id' => $providerId,
                'order_id' => $orderId,
                'content_id' => $contentId,
            ],
        ];

        try {
            // Faire l'appel API à Moneroo
            $response = Http::withHeaders($this->authHeaders())
                ->post("{$this->apiUrl}/payouts", $payload);

            $responseData = $response->json();

            // Vérifier le format de réponse Moneroo: { "success": true, "message": "...", "data": {} }
            $isSuccess = $response->successful() && 
                        isset($responseData['success']) && 
                        $responseData['success'] === true;

            $payoutData = $responseData['data'] ?? [];
            $actualPayoutId = $payoutData['id'] ?? $payoutId;
            $status = $payoutData['status'] ?? 'pending';

            // Enregistrer le payout dans la base de données
            $payout = ProviderPayout::create([
                'provider_id' => $providerId,
                'order_id' => $orderId,
                'content_id' => $contentId,
                'payout_id' => $actualPayoutId,
                'amount' => $amount,
                'currency' => $currency,
                'status' => $this->mapMonerooStatusToLocalStatus($status),
                'moneroo_status' => $status,
                'moneroo_response' => $responseData,
            ]);

            if ($isSuccess) {
                Log::info("Payout Moneroo initié avec succès", [
                    'payout_id' => $actualPayoutId,
                    'provider_id' => $providerId,
                    'order_id' => $orderId,
                    'amount' => $amount,
                ]);

                return [
                    'success' => true,
                    'payout' => $payout,
                    'payout_id' => $actualPayoutId,
                    'status' => $status,
                ];
            } else {
                Log::error("Échec de l'initiation du payout Moneroo", [
                    'payout_id' => $actualPayoutId,
                    'response' => $responseData,
                    'status_code' => $response->status(),
                ]);

                $payout->update([
                    'status' => 'failed',
                    'failure_reason' => $responseData['message'] ?? 'Erreur inconnue lors de l\'initiation du payout',
                ]);

                return [
                    'success' => false,
                    'payout' => $payout,
                    'error' => $responseData['message'] ?? 'Erreur inconnue',
                ];
            }
        } catch (\Exception $e) {
            Log::error("Exception lors de l'initiation du payout Moneroo", [
                'payout_id' => $payoutId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Vérifier le statut d'un payout
     */
    public function checkPayoutStatus(string $payoutId): array
    {
        try {
            $response = Http::withHeaders($this->authHeaders())
                ->get("{$this->apiUrl}/payouts/{$payoutId}");

            if ($response->successful()) {
                $responseData = $response->json();
                $payoutData = $responseData['data'] ?? $responseData;

                // Mettre à jour le payout dans la base de données
                $payout = ProviderPayout::where('payout_id', $payoutId)->first();
                if ($payout) {
                    // Sauvegarder l'ancien statut pour vérifier si on passe à "completed"
                    $oldStatus = $payout->status;
                    $monerooStatus = $payoutData['status'] ?? null;
                    $newStatus = $this->mapMonerooStatusToLocalStatus($monerooStatus);
                    $isNewlyCompleted = ($oldStatus !== 'completed' && $newStatus === 'completed');

                    $payout->update([
                        'moneroo_status' => $monerooStatus,
                        'provider_transaction_id' => $payoutData['transaction_id'] ?? $payoutData['reference'] ?? null,
                        'moneroo_response' => $responseData,
                        'status' => $newStatus,
                        'processed_at' => in_array($monerooStatus, ['completed', 'failed']) ? now() : null,
                    ]);

                    // Recharger le payout avec les relations pour les notifications
                    $payout->refresh();
                    $payout->load(['provider', 'course', 'order']);

                    // Envoyer notification et email si le paiement vient d'être complété
                    if ($isNewlyCompleted && $payout->provider) {
                        $this->sendPayoutNotificationAndEmail($payout);
                    }
                }

                return [
                    'success' => true,
                    'status' => $monerooStatus,
                    'data' => $payoutData,
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Impossible de récupérer le statut du payout',
                    'status_code' => $response->status(),
                ];
            }
        } catch (\Exception $e) {
            Log::error("Exception lors de la vérification du statut du payout Moneroo", [
                'payout_id' => $payoutId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Traiter le callback de Moneroo
     */
    public function handleCallback(array $callbackData): bool
    {
        $payoutId = $callbackData['data']['id'] ?? $callbackData['id'] ?? null;

        if (!$payoutId) {
            Log::error("Callback Moneroo sans payoutId", ['data' => $callbackData]);
            return false;
        }

        $payout = ProviderPayout::where('payout_id', $payoutId)->first();

        if (!$payout) {
            Log::error("Payout non trouvé pour le callback Moneroo", ['payout_id' => $payoutId]);
            return false;
        }

        $payoutData = $callbackData['data'] ?? $callbackData;
        $status = $payoutData['status'] ?? null;
        $mappedStatus = $this->mapMonerooStatusToLocalStatus($status);

        // Sauvegarder l'ancien statut pour vérifier si on passe à "completed"
        $oldStatus = $payout->status;
        $isNewlyCompleted = ($oldStatus !== 'completed' && $mappedStatus === 'completed');

        $updateData = [
            'moneroo_status' => $status,
            'status' => $mappedStatus,
            'moneroo_response' => $callbackData,
        ];

        if (isset($payoutData['transaction_id']) || isset($payoutData['reference'])) {
            $updateData['provider_transaction_id'] = $payoutData['transaction_id'] ?? $payoutData['reference'];
        }

        if (isset($payoutData['failure_reason']) || isset($payoutData['error'])) {
            $updateData['failure_reason'] = $payoutData['failure_reason'] ?? $payoutData['error'];
        }

        if (in_array($status, ['completed', 'failed'])) {
            $updateData['processed_at'] = now();
        }

        $payout->update($updateData);

        // Recharger le payout avec les relations pour les notifications
        $payout->refresh();
        $payout->load(['provider', 'course', 'order']);

        // Envoyer notification et email si le paiement vient d'être complété
        if ($isNewlyCompleted && $payout->provider) {
            $this->sendPayoutNotificationAndEmail($payout);
        }

        Log::info("Callback Moneroo traité", [
            'payout_id' => $payoutId,
            'status' => $status,
            'mapped_status' => $mappedStatus,
            'is_newly_completed' => $isNewlyCompleted,
        ]);

        return true;
    }

    /**
     * Mapper le statut Moneroo vers le statut local
     */
    private function mapMonerooStatusToLocalStatus(?string $monerooStatus): string
    {
        return match($monerooStatus) {
            'pending', 'processing' => 'processing',
            'completed', 'success' => 'completed',
            'failed', 'error' => 'failed',
            default => 'pending',
        };
    }

    /**
     * Envoyer la notification et l'email au prestataire après un paiement complété
     */
    private function sendPayoutNotificationAndEmail(ProviderPayout $payout): void
    {
        try {
            $provider = $payout->provider;

            if (!$provider || !$provider->email) {
                Log::warning("Impossible d'envoyer notification/email de payout: prestataire ou email manquant", [
                    'payout_id' => $payout->id,
                    'provider_id' => $payout->provider_id,
                ]);
                return;
            }

            // Envoyer l'email directement de manière synchrone
            try {
                $mailable = new ProviderPayoutReceivedMail($payout);
                $communicationService = app(\App\Services\CommunicationService::class);
                $communicationService->sendEmailAndWhatsApp($provider, $mailable);
                Log::info("Email ProviderPayoutReceivedMail envoyé à {$provider->email} pour le payout {$payout->payout_id}", [
                    'payout_id' => $payout->id,
                    'provider_id' => $provider->id,
                    'provider_email' => $provider->email,
                ]);
            } catch (\Exception $emailException) {
                Log::error("Erreur lors de l'envoi de l'email ProviderPayoutReceivedMail", [
                    'payout_id' => $payout->id,
                    'provider_id' => $provider->id,
                    'provider_email' => $provider->email,
                    'error' => $emailException->getMessage(),
                    'trace' => $emailException->getTraceAsString(),
                ]);
            }

            // Envoyer la notification
            try {
                Notification::sendNow($provider, new ProviderPayoutReceived($payout));

                Log::info("Notification ProviderPayoutReceived envoyée au prestataire {$provider->id} pour le payout {$payout->id}", [
                    'payout_id' => $payout->id,
                    'provider_id' => $provider->id,
                    'provider_email' => $provider->email,
                ]);
            } catch (\Exception $notifException) {
                Log::error("Erreur lors de l'envoi de la notification ProviderPayoutReceived", [
                    'payout_id' => $payout->id,
                    'provider_id' => $provider->id,
                    'error' => $notifException->getMessage(),
                    'trace' => $notifException->getTraceAsString(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Erreur lors de l'envoi de la notification/email de payout pour le payout {$payout->id}: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
        }
    }
}

