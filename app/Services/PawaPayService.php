<?php

namespace App\Services;

use App\Models\InstructorPayout;
use App\Notifications\InstructorPayoutReceived;
use App\Mail\InstructorPayoutReceivedMail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class PawaPayService
{
    private string $apiUrl;
    private string $apiToken;
    private string $callbackUrl;

    public function __construct()
    {
        $this->apiUrl = config('services.pawapay.api_url', config('services.pawapay.base_url', 'https://api.sandbox.pawapay.io/v2'));
        $this->apiToken = config('services.pawapay.api_token', config('services.pawapay.api_key'));
        $this->callbackUrl = config('services.pawapay.callback_url', route('pawapay.payout.callback'));
    }

    /**
     * Initier un payout vers un formateur externe
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
        // Générer un UUIDv4 pour le payout
        $payoutId = (string) Str::uuid();

        // Préparer le payload selon la documentation pawaPay
        $payload = [
            'payoutId' => $payoutId,
            'amount' => number_format($amount, 2, '.', ''),
            'currency' => $currency,
            'recipient' => [
                'type' => 'MMO',
                'accountDetails' => [
                    'phoneNumber' => $phoneNumber,
                    'provider' => $provider,
                ],
            ],
        ];

        try {
            // Faire l'appel API à pawaPay
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Content-Type' => 'application/json',
            ])->post("{$this->apiUrl}/payouts", $payload);

            $responseData = $response->json();

            // Enregistrer le payout dans la base de données
            $payout = InstructorPayout::create([
                'instructor_id' => $instructorId,
                'order_id' => $orderId,
                'course_id' => $courseId,
                'payout_id' => $payoutId,
                'amount' => $amount,
                'currency' => $currency,
                'status' => $response->successful() && isset($responseData['status']) && $responseData['status'] === 'ACCEPTED' ? 'processing' : 'pending',
                'pawapay_status' => $responseData['status'] ?? null,
                'pawapay_response' => $responseData,
            ]);

            if ($response->successful() && isset($responseData['status']) && $responseData['status'] === 'ACCEPTED') {
                Log::info("Payout initié avec succès", [
                    'payout_id' => $payoutId,
                    'instructor_id' => $instructorId,
                    'order_id' => $orderId,
                    'amount' => $amount,
                ]);

                return [
                    'success' => true,
                    'payout' => $payout,
                    'payout_id' => $payoutId,
                    'status' => $responseData['status'] ?? 'ACCEPTED',
                ];
            } else {
                Log::error("Échec de l'initiation du payout", [
                    'payout_id' => $payoutId,
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
            Log::error("Exception lors de l'initiation du payout", [
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
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
            ])->get("{$this->apiUrl}/payouts/{$payoutId}");

            if ($response->successful()) {
                $responseData = $response->json();

                // Mettre à jour le payout dans la base de données
                $payout = InstructorPayout::where('payout_id', $payoutId)->first();
                if ($payout) {
                    // Sauvegarder l'ancien statut pour vérifier si on passe à "completed"
                    $oldStatus = $payout->status;
                    $newStatus = $this->mapPawaPayStatusToLocalStatus($responseData['status'] ?? null);
                    $isNewlyCompleted = ($oldStatus !== 'completed' && $newStatus === 'completed');

                    $payout->update([
                        'pawapay_status' => $responseData['status'] ?? null,
                        'provider_transaction_id' => $responseData['providerTransactionId'] ?? null,
                        'pawapay_response' => $responseData,
                        'status' => $newStatus,
                        'processed_at' => isset($responseData['status']) && in_array($responseData['status'], ['COMPLETED', 'FAILED']) ? now() : null,
                    ]);

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
                    'status' => $responseData['status'] ?? null,
                    'data' => $responseData,
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Impossible de récupérer le statut du payout',
                    'status_code' => $response->status(),
                ];
            }
        } catch (\Exception $e) {
            Log::error("Exception lors de la vérification du statut du payout", [
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
     * Traiter le callback de pawaPay
     */
    public function handleCallback(array $callbackData): bool
    {
        $payoutId = $callbackData['payoutId'] ?? null;

        if (!$payoutId) {
            Log::error("Callback pawaPay sans payoutId", ['data' => $callbackData]);
            return false;
        }

        $payout = InstructorPayout::where('payout_id', $payoutId)->first();

        if (!$payout) {
            Log::error("Payout non trouvé pour le callback", ['payout_id' => $payoutId]);
            return false;
        }

        $status = $callbackData['status'] ?? null;
        $mappedStatus = $this->mapPawaPayStatusToLocalStatus($status);

        // Sauvegarder l'ancien statut pour vérifier si on passe à "completed"
        $oldStatus = $payout->status;
        $isNewlyCompleted = ($oldStatus !== 'completed' && $mappedStatus === 'completed');

        $updateData = [
            'pawapay_status' => $status,
            'status' => $mappedStatus,
            'pawapay_response' => $callbackData,
        ];

        if (isset($callbackData['providerTransactionId'])) {
            $updateData['provider_transaction_id'] = $callbackData['providerTransactionId'];
        }

        if (isset($callbackData['failureReason'])) {
            $updateData['failure_reason'] = is_array($callbackData['failureReason'])
                ? ($callbackData['failureReason']['failureMessage'] ?? json_encode($callbackData['failureReason']))
                : $callbackData['failureReason'];
        }

        if (in_array($status, ['COMPLETED', 'FAILED'])) {
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

        Log::info("Callback pawaPay traité", [
            'payout_id' => $payoutId,
            'status' => $status,
            'mapped_status' => $mappedStatus,
            'is_newly_completed' => $isNewlyCompleted,
        ]);

        return true;
    }

    /**
     * Mapper le statut pawaPay vers le statut local
     */
    private function mapPawaPayStatusToLocalStatus(?string $pawapayStatus): string
    {
        return match($pawapayStatus) {
            'ACCEPTED' => 'processing',
            'PROCESSING' => 'processing',
            'COMPLETED' => 'completed',
            'FAILED' => 'failed',
            'IN_RECONCILIATION' => 'processing',
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
                // Ne pas relancer l'exception pour ne pas bloquer le processus
            }

            // Envoyer la notification (pour la base de données et l'affichage dans la navbar)
            // Utiliser sendNow() pour envoyer immédiatement sans passer par la queue
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
                // Ne pas relancer l'exception pour ne pas bloquer le processus
            }
        } catch (\Exception $e) {
            // Logger l'erreur mais ne pas faire échouer le processus de callback
            Log::error("Erreur lors de l'envoi de la notification/email de payout pour le payout {$payout->id}: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
        }
    }

    /**
     * Prédire le provider à partir du numéro de téléphone
     */
    public function predictProvider(string $phoneNumber, string $country): ?string
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
            ])->get("{$this->apiUrl}/toolkit/predict-provider", [
                'phoneNumber' => $phoneNumber,
                'country' => $country,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['provider'] ?? null;
            }
        } catch (\Exception $e) {
            Log::error("Erreur lors de la prédiction du provider", [
                'phone' => $phoneNumber,
                'country' => $country,
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }
}

