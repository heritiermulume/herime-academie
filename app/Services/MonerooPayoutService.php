<?php

namespace App\Services;

use App\Models\InstructorPayout;
use App\Models\User;
use App\Notifications\InstructorPayoutReceived;
use App\Mail\InstructorPayoutReceivedMail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

/**
 * Service pour gérer les payouts Moneroo
 * 
 * Documentation: https://docs.moneroo.io/payouts/initialize-payout
 */
class MonerooPayoutService
{
    private string $apiUrl;
    private string $apiKey;
    private string $callbackUrl;

    public function __construct()
    {
        $this->apiUrl = rtrim(config('services.moneroo.base_url', 'https://api.moneroo.io/v1'), '/');
        $this->apiKey = config('services.moneroo.api_key');
        $this->callbackUrl = config('services.moneroo.payout_callback_url', env('APP_URL') . '/api/moneroo/payout/callback');
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
     * Convertir le montant en entier (selon la documentation Moneroo, amount doit être un integer)
     * Pour XOF, XAF, JPY, etc. : pas de sous-unité, donc montant entier
     * Pour USD, EUR, etc. : multiplier par 100 pour obtenir les centimes
     */
    private function convertAmountToInteger(float $amount, string $currency): int
    {
        // Devises sans sous-unité (comme XOF, XAF, JPY, etc.)
        $noSubunitCurrencies = ['XOF', 'XAF', 'JPY', 'KRW', 'CLP', 'VND', 'CDF'];
        
        if (in_array(strtoupper($currency), $noSubunitCurrencies)) {
            // Arrondir à l'entier le plus proche
            return (int) round($amount);
        }
        
        // Pour les autres devises (USD, EUR, etc.), multiplier par 100 pour obtenir les centimes
        return (int) round($amount * 100);
    }

    /**
     * Extraire le prénom et le nom de famille depuis le nom complet
     */
    private function extractNames(string $fullName): array
    {
        $parts = explode(' ', trim($fullName), 2);
        return [
            'first_name' => $parts[0] ?? '',
            'last_name' => $parts[1] ?? '',
        ];
    }

    /**
     * Obtenir les champs recipient selon la méthode de payout
     * Selon la documentation Moneroo, chaque méthode a ses propres champs requis
     */
    private function getRecipientFields(string $method, string $phoneNumber, string $country): array
    {
        // Pour les méthodes mobile money, utiliser msisdn (numéro de téléphone)
        // Format selon la documentation: recipient.msisdn pour mobile money
        return [
            'msisdn' => $phoneNumber,
        ];
    }

    /**
     * Initier un payout vers un formateur externe
     * 
     * Documentation: https://docs.moneroo.io/payouts/initialize-payout
     */
    public function initiatePayout(
        int $instructorId,
        int $orderId,
        int $courseId,
        float $amount,
        string $currency,
        string $phoneNumber,
        string $provider,
        string $country
    ): array {
        try {
            $instructor = User::findOrFail($instructorId);
            
            // Extraire le prénom et le nom de famille
            $names = $this->extractNames($instructor->name);
            
            // Convertir le montant en entier selon la devise
            $amountInteger = $this->convertAmountToInteger($amount, $currency);
            
            // Obtenir les champs recipient selon la méthode
            $recipientFields = $this->getRecipientFields($provider, $phoneNumber, $country);
            
            // Préparer le payload selon la documentation Moneroo
            // Documentation: https://docs.moneroo.io/payouts/initialize-payout
            $payload = [
                'amount' => $amountInteger,
                'currency' => strtoupper($currency),
                'description' => config('services.moneroo.company_name', 'Herime Académie') . ' - Paiement commission formateur',
                'method' => $provider, // Code de la méthode (ex: mtn_bj, orange_sn, etc.)
                'customer' => [
                    'email' => $instructor->email,
                    'first_name' => $names['first_name'],
                    'last_name' => $names['last_name'],
                    'phone' => $phoneNumber,
                    'country' => $country,
                ],
                'recipient' => $recipientFields,
                'metadata' => [
                    'instructor_id' => $instructorId,
                    'order_id' => $orderId,
                    'course_id' => $courseId,
                    'payout_type' => 'instructor_commission',
                ],
            ];

            Log::info('Moneroo: Initiation du payout', [
                'instructor_id' => $instructorId,
                'order_id' => $orderId,
                'amount' => $amountInteger,
                'currency' => $currency,
                'method' => $provider,
            ]);

            // Faire l'appel API à Moneroo
            // Endpoint: POST /v1/payouts/initialize
            $response = Http::withHeaders($this->authHeaders())
                ->post("{$this->apiUrl}/payouts/initialize", $payload);

            $responseData = $response->json();

            Log::info('Moneroo: Réponse de l\'API payout', [
                'status' => $response->status(),
                'response' => $responseData,
            ]);

            // Vérifier le format de réponse Moneroo: { "success": true, "message": "...", "data": { "id": "..." } }
            $isSuccess = $response->successful() && 
                        isset($responseData['success']) && 
                        $responseData['success'] === true;

            $payoutData = $responseData['data'] ?? [];
            $actualPayoutId = $payoutData['id'] ?? null;

            if (!$actualPayoutId && $isSuccess) {
                Log::error('Moneroo: Payout ID manquant dans la réponse', [
                    'response' => $responseData,
                ]);
                return [
                    'success' => false,
                    'error' => 'Payout ID manquant dans la réponse Moneroo',
                ];
            }

            // Enregistrer le payout dans la base de données
            $payout = InstructorPayout::create([
                'instructor_id' => $instructorId,
                'order_id' => $orderId,
                'course_id' => $courseId,
                'payout_id' => $actualPayoutId,
                'amount' => $amount,
                'currency' => $currency,
                'status' => $isSuccess ? 'pending' : 'failed',
                'moneroo_status' => $payoutData['status'] ?? 'pending',
                'moneroo_response' => $responseData,
                'failure_reason' => $isSuccess ? null : ($responseData['message'] ?? 'Erreur inconnue'),
            ]);

            if ($isSuccess) {
                Log::info("Payout Moneroo initié avec succès", [
                    'payout_id' => $actualPayoutId,
                    'instructor_id' => $instructorId,
                    'order_id' => $orderId,
                    'amount' => $amount,
                ]);

                return [
                    'success' => true,
                    'payout' => $payout,
                    'payout_id' => $actualPayoutId,
                    'status' => $payoutData['status'] ?? 'pending',
                ];
            } else {
                Log::error("Échec de l'initiation du payout Moneroo", [
                    'payout_id' => $actualPayoutId,
                    'response' => $responseData,
                    'status_code' => $response->status(),
                ]);

                return [
                    'success' => false,
                    'payout' => $payout,
                    'error' => $responseData['message'] ?? 'Erreur inconnue lors de l\'initiation du payout',
                ];
            }
        } catch (\Exception $e) {
            Log::error("Exception lors de l'initiation du payout Moneroo", [
                'instructor_id' => $instructorId,
                'order_id' => $orderId,
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
     * 
     * Documentation: https://docs.moneroo.io/payouts/verify-payout
     */
    public function checkPayoutStatus(string $payoutId): array
    {
        try {
            // Endpoint: GET /v1/payouts/{payoutId}
            $response = Http::withHeaders($this->authHeaders())
                ->get("{$this->apiUrl}/payouts/{$payoutId}");

            if ($response->successful()) {
                $responseData = $response->json();
                $payoutData = $responseData['data'] ?? $responseData;

                // Mettre à jour le payout dans la base de données
                $payout = InstructorPayout::where('payout_id', $payoutId)->first();
                if ($payout) {
                    // Sauvegarder l'ancien statut pour vérifier si on passe à "completed"
                    $oldStatus = $payout->status;
                    $monerooStatus = $payoutData['status'] ?? null;
                    $newStatus = $this->mapMonerooStatusToLocalStatus($monerooStatus);
                    $isNewlyCompleted = ($oldStatus !== 'completed' && $newStatus === 'completed');

                    $updateData = [
                        'moneroo_status' => $monerooStatus,
                        'moneroo_response' => $responseData,
                        'status' => $newStatus,
                    ];

                    // Mettre à jour le transaction_id si disponible
                    if (isset($payoutData['transaction_id']) || isset($payoutData['reference'])) {
                        $updateData['provider_transaction_id'] = $payoutData['transaction_id'] ?? $payoutData['reference'];
                    }

                    // Mettre à jour le failure_reason si disponible
                    if (isset($payoutData['failure_reason']) || isset($payoutData['error'])) {
                        $updateData['failure_reason'] = $payoutData['failure_reason'] ?? $payoutData['error'];
                    }

                    // Mettre à jour processed_at si le payout est terminé
                    if (in_array($monerooStatus, ['completed', 'failed'])) {
                        $updateData['processed_at'] = now();
                    }

                    $payout->update($updateData);

                    // Recharger le payout avec les relations pour les notifications
                    $payout->refresh();
                    $payout->load(['instructor', 'course', 'order']);

                    // Envoyer notification et email si le paiement vient d'être complété
                    if ($isNewlyCompleted && $payout->instructor) {
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
     * 
     * Documentation: https://docs.moneroo.io/introduction/webhooks
     */
    public function handleCallback(array $callbackData): bool
    {
        $payoutId = $callbackData['data']['id'] ?? $callbackData['id'] ?? null;

        if (!$payoutId) {
            Log::error("Callback Moneroo sans payoutId", ['data' => $callbackData]);
            return false;
        }

        $payout = InstructorPayout::where('payout_id', $payoutId)->first();

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
        $payout->load(['instructor', 'course', 'order']);

        // Envoyer notification et email si le paiement vient d'être complété
        if ($isNewlyCompleted && $payout->instructor) {
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
            'pending', 'processing' => 'pending',
            'completed', 'success' => 'completed',
            'failed', 'error' => 'failed',
            default => 'pending',
        };
    }

    /**
     * Envoyer la notification et l'email au formateur après un paiement complété
     */
    private function sendPayoutNotificationAndEmail(InstructorPayout $payout): void
    {
        try {
            $instructor = $payout->instructor;

            if (!$instructor || !$instructor->email) {
                Log::warning("Impossible d'envoyer notification/email de payout: formateur ou email manquant", [
                    'payout_id' => $payout->id,
                    'instructor_id' => $payout->instructor_id,
                ]);
                return;
            }

            // Envoyer l'email directement de manière synchrone
            try {
                $mailable = new InstructorPayoutReceivedMail($payout);
                $communicationService = app(\App\Services\CommunicationService::class);
                $communicationService->sendEmailAndWhatsApp($instructor, $mailable);
                Log::info("Email InstructorPayoutReceivedMail envoyé à {$instructor->email} pour le payout {$payout->payout_id}", [
                    'payout_id' => $payout->id,
                    'instructor_id' => $instructor->id,
                    'instructor_email' => $instructor->email,
                ]);
            } catch (\Exception $emailException) {
                Log::error("Erreur lors de l'envoi de l'email InstructorPayoutReceivedMail", [
                    'payout_id' => $payout->id,
                    'instructor_id' => $instructor->id,
                    'instructor_email' => $instructor->email,
                    'error' => $emailException->getMessage(),
                    'trace' => $emailException->getTraceAsString(),
                ]);
            }

            // Envoyer la notification
            try {
                Notification::sendNow($instructor, new InstructorPayoutReceived($payout));

                Log::info("Notification InstructorPayoutReceived envoyée au formateur {$instructor->id} pour le payout {$payout->id}", [
                    'payout_id' => $payout->id,
                    'instructor_id' => $instructor->id,
                    'instructor_email' => $instructor->email,
                ]);
            } catch (\Exception $notifException) {
                Log::error("Erreur lors de l'envoi de la notification InstructorPayoutReceived", [
                    'payout_id' => $payout->id,
                    'instructor_id' => $instructor->id,
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

