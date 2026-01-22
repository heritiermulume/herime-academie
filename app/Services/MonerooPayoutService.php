<?php

namespace App\Services;

use App\Models\ProviderPayout;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletPayout;
use App\Notifications\ProviderPayoutReceived;
use App\Mail\ProviderPayoutReceivedMail;
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
     * Selon la documentation Moneroo: https://docs.moneroo.io/payouts/available-methods#required-fields
     * 
     * La plupart des méthodes nécessitent 'msisdn' (integer)
     * La méthode 'moneroo_payout_demo' nécessite 'account_number' (integer)
     */
    private function getRecipientFields(string $method, string $phoneNumber, string $country): array
    {
        // Convertir le numéro en entier selon la documentation (msisdn et account_number sont des integers)
        $phoneInteger = (int) preg_replace('/[^0-9]/', '', $phoneNumber);
        
        // La méthode moneroo_payout_demo nécessite account_number au lieu de msisdn
        if ($method === 'moneroo_payout_demo') {
            return [
                'account_number' => $phoneInteger,
            ];
        }
        
        // Pour toutes les autres méthodes mobile money, utiliser msisdn
        // Format selon la documentation: recipient.msisdn pour mobile money
        return [
            'msisdn' => $phoneInteger,
        ];
    }

    /**
     * Initier un payout vers un prestataire externe
     * 
     * Documentation: https://docs.moneroo.io/payouts/initialize-payout
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
        try {
            $providerUser = User::findOrFail($providerId);
            
            // Extraire le prénom et le nom de famille
            $names = $this->extractNames($providerUser->name);
            
            // Convertir le montant en entier selon la devise
            $amountInteger = $this->convertAmountToInteger($amount, $currency);
            
            // Obtenir les champs recipient selon la méthode
            $recipientFields = $this->getRecipientFields($provider, $phoneNumber, $country);
            
            // Préparer le payload selon la documentation Moneroo
            // Documentation: https://docs.moneroo.io/payouts/initialize-payout
            $payload = [
                'amount' => $amountInteger, // integer requis
                'currency' => strtoupper($currency), // ISO 4217 format
                'description' => config('services.moneroo.company_name', 'Herime Académie') . ' - Paiement commission prestataire',
                'method' => $provider, // Code de la méthode (ex: mtn_bj, orange_sn, moneroo_payout_demo, etc.)
                'customer' => [
                    'email' => $providerUser->email, // requis
                    'first_name' => $names['first_name'], // requis
                    'last_name' => $names['last_name'], // requis
                    'phone' => (int) preg_replace('/[^0-9]/', '', $phoneNumber), // integer optionnel selon la doc
                    'country' => $country, // ISO 3166-1 alpha-2, optionnel
                ],
                'recipient' => $recipientFields, // msisdn (integer) ou account_number (integer) selon la méthode
                'metadata' => [
                    'provider_id' => $providerId,
                    'order_id' => $orderId,
                    'content_id' => $contentId,
                    'payout_type' => 'provider_commission',
                ],
            ];

            // Faire l'appel API à Moneroo
            // Endpoint: POST /v1/payouts/initialize
            $response = Http::withHeaders($this->authHeaders())
                ->post("{$this->apiUrl}/payouts/initialize", $payload);

            $responseData = $response->json();

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
            $payout = ProviderPayout::create([
                'provider_id' => $providerId,
                'order_id' => $orderId,
                'content_id' => $contentId,
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
                    'provider_id' => $providerId,
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
                'provider_id' => $providerId,
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
                $payout = ProviderPayout::where('payout_id', $payoutId)->first();
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
            'pending', 'processing' => 'pending',
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

    /**
     * Initier un payout vers un wallet d'ambassadeur
     * 
     * Documentation: https://docs.moneroo.io/fr/payouts/initialiser-un-transfert
     */
    public function initiateWalletPayout(
        Wallet $wallet,
        float $amount,
        string $currency,
        string $phoneNumber,
        string $method,
        string $country,
        string $description = null
    ): array {
        try {
            $user = $wallet->user;
            
            if (!$user) {
                return [
                    'success' => false,
                    'error' => 'Utilisateur non trouvé pour ce wallet',
                ];
            }

            // Vérifier que le wallet a suffisamment de solde
            if (!$wallet->hasBalance($amount)) {
                return [
                    'success' => false,
                    'error' => 'Solde insuffisant',
                    'balance' => $wallet->balance,
                    'requested' => $amount,
                ];
            }

            // Extraire le prénom et le nom de famille
            $names = $this->extractNames($user->name);
            
            // Convertir le montant en entier selon la devise
            $amountInteger = $this->convertAmountToInteger($amount, $currency);
            
            // Obtenir les champs recipient selon la méthode
            $recipientFields = $this->getRecipientFields($method, $phoneNumber, $country);
            
            // Préparer le payload selon la documentation Moneroo
            // Documentation: https://docs.moneroo.io/payouts/initialize-payout
            $payload = [
                'amount' => $amountInteger, // integer requis
                'currency' => strtoupper($currency), // ISO 4217 format
                'description' => $description ?? (config('services.moneroo.company_name', 'Herime Académie') . ' - Retrait wallet'),
                'method' => $method, // Code de la méthode (ex: mtn_cd, airtel_cd, moneroo_payout_demo, etc.)
                'customer' => [
                    'email' => $user->email, // requis
                    'first_name' => $names['first_name'], // requis
                    'last_name' => $names['last_name'], // requis
                    'phone' => (int) preg_replace('/[^0-9]/', '', $phoneNumber), // integer optionnel selon la doc
                    'country' => $country, // ISO 3166-1 alpha-2, optionnel
                ],
                'recipient' => $recipientFields, // msisdn (integer) ou account_number (integer) selon la méthode
                'metadata' => [
                    'user_id' => $user->id,
                    'wallet_id' => $wallet->id,
                    'payout_type' => 'wallet_withdrawal',
                ],
            ];

            Log::info('Moneroo: Initiation du payout wallet', [
                'user_id' => $user->id,
                'wallet_id' => $wallet->id,
                'amount' => $amountInteger,
                'currency' => $currency,
                'method' => $method,
            ]);

            // Créer le payout dans la base de données AVANT l'appel API
            // Cela permet de débiter le wallet immédiatement et d'éviter les doubles demandes
            $walletPayout = WalletPayout::create([
                'wallet_id' => $wallet->id,
                'amount' => $amount,
                'currency' => $currency,
                'status' => 'pending',
                'method' => $method,
                'phone' => $phoneNumber,
                'country' => $country,
                'description' => $description ?? 'Retrait wallet',
                'customer_email' => $user->email,
                'customer_first_name' => $names['first_name'],
                'customer_last_name' => $names['last_name'],
                'initiated_at' => now(),
            ]);

            // Débiter le wallet immédiatement
            try {
                $transaction = $wallet->debit(
                    $amount,
                    'payout',
                    'Retrait wallet #' . $walletPayout->id,
                    $walletPayout,
                    ['payout_id' => $walletPayout->id, 'method' => $method]
                );
                
                // Lier la transaction au payout
                $walletPayout->wallet_transaction_id = $transaction->id;
                $walletPayout->save();
            } catch (\Exception $e) {
                // Si le débit échoue, supprimer le payout et retourner l'erreur
                $walletPayout->delete();
                return [
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }

            // Faire l'appel API à Moneroo
            $response = Http::withHeaders($this->authHeaders())
                ->post("{$this->apiUrl}/payouts/initialize", $payload);

            $responseData = $response->json();

            Log::info('Moneroo: Réponse de l\'API payout wallet', [
                'status' => $response->status(),
                'response' => $responseData,
            ]);

            // Vérifier le format de réponse Moneroo: { "success": true, "message": "...", "data": { "id": "..." } }
            $isSuccess = $response->successful() && 
                        isset($responseData['success']) && 
                        $responseData['success'] === true;

            $payoutData = $responseData['data'] ?? [];
            $actualPayoutId = $payoutData['id'] ?? null;

            if ($isSuccess && $actualPayoutId) {
                // Mettre à jour le payout avec l'ID Moneroo
                $walletPayout->update([
                    'moneroo_id' => $actualPayoutId,
                    'status' => 'processing',
                    'moneroo_data' => $responseData,
                ]);

                Log::info("Payout wallet Moneroo initié avec succès", [
                    'moneroo_id' => $actualPayoutId,
                    'wallet_payout_id' => $walletPayout->id,
                    'user_id' => $user->id,
                    'amount' => $amount,
                ]);

                return [
                    'success' => true,
                    'payout' => $walletPayout,
                    'moneroo_id' => $actualPayoutId,
                    'status' => $payoutData['status'] ?? 'processing',
                ];
            } else {
                // En cas d'échec, annuler le payout et rembourser le wallet
                $failureReason = $responseData['message'] ?? 'Erreur inconnue lors de l\'initiation du payout';
                
                Log::error("Échec de l'initiation du payout wallet Moneroo", [
                    'wallet_payout_id' => $walletPayout->id,
                    'response' => $responseData,
                    'status_code' => $response->status(),
                ]);

                // Annuler le payout (cela remboursera automatiquement le wallet)
                $walletPayout->cancel($failureReason);

                return [
                    'success' => false,
                    'payout' => $walletPayout,
                    'error' => $failureReason,
                ];
            }
        } catch (\Exception $e) {
            Log::error("Exception lors de l'initiation du payout wallet Moneroo", [
                'wallet_id' => $wallet->id ?? null,
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
     * Vérifier le statut d'un payout wallet
     * 
     * Documentation: https://docs.moneroo.io/fr/payouts/verifier-un-transfert
     */
    public function checkWalletPayoutStatus(string $monerooId): array
    {
        try {
            // Endpoint: GET /v1/payouts/{payoutId}/verify
            $response = Http::withHeaders($this->authHeaders())
                ->get("{$this->apiUrl}/payouts/{$monerooId}/verify");

            if ($response->successful()) {
                $responseData = $response->json();
                $payoutData = $responseData['data'] ?? $responseData;

                // Mettre à jour le payout dans la base de données
                $walletPayout = WalletPayout::where('moneroo_id', $monerooId)->first();
                
                if ($walletPayout) {
                    $monerooStatus = $payoutData['status'] ?? null;
                    $newStatus = $this->mapMonerooStatusToWalletPayoutStatus($monerooStatus);

                    $updateData = [
                        'status' => $newStatus,
                        'moneroo_data' => array_merge($walletPayout->moneroo_data ?? [], $payoutData),
                    ];

                    // Mettre à jour les frais si disponibles
                    if (isset($payoutData['fee'])) {
                        $updateData['fee'] = $payoutData['fee'];
                    }

                    if (isset($payoutData['net_amount'])) {
                        $updateData['net_amount'] = $payoutData['net_amount'];
                    }

                    // Mettre à jour le failure_reason si disponible
                    if (isset($payoutData['failure_reason']) || isset($payoutData['error'])) {
                        $updateData['failure_reason'] = $payoutData['failure_reason'] ?? $payoutData['error'];
                    }

                    // Mettre à jour completed_at ou failed_at
                    if ($newStatus === 'completed' && !$walletPayout->completed_at) {
                        $updateData['completed_at'] = now();
                    } elseif ($newStatus === 'failed' && !$walletPayout->failed_at) {
                        $updateData['failed_at'] = now();
                        
                        // Si le payout échoue, rembourser le wallet
                        if ($walletPayout->status !== 'failed') {
                            $this->refundFailedWalletPayout($walletPayout);
                        }
                    }

                    $walletPayout->update($updateData);
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
            Log::error("Exception lors de la vérification du statut du payout wallet Moneroo", [
                'moneroo_id' => $monerooId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Traiter le callback de Moneroo pour les payouts wallet
     */
    public function handleWalletPayoutCallback(array $callbackData): bool
    {
        $monerooId = $callbackData['data']['id'] ?? $callbackData['id'] ?? null;

        if (!$monerooId) {
            Log::error("Callback Moneroo wallet payout sans ID", ['data' => $callbackData]);
            return false;
        }

        $walletPayout = WalletPayout::where('moneroo_id', $monerooId)->first();

        if (!$walletPayout) {
            Log::error("Wallet payout non trouvé pour le callback Moneroo", ['moneroo_id' => $monerooId]);
            return false;
        }

        $payoutData = $callbackData['data'] ?? $callbackData;
        $monerooStatus = $payoutData['status'] ?? null;
        $newStatus = $this->mapMonerooStatusToWalletPayoutStatus($monerooStatus);

        $updateData = [
            'status' => $newStatus,
            'moneroo_data' => array_merge($walletPayout->moneroo_data ?? [], $callbackData),
        ];

        if (isset($payoutData['fee'])) {
            $updateData['fee'] = $payoutData['fee'];
        }

        if (isset($payoutData['net_amount'])) {
            $updateData['net_amount'] = $payoutData['net_amount'];
        }

        if (isset($payoutData['failure_reason']) || isset($payoutData['error'])) {
            $updateData['failure_reason'] = $payoutData['failure_reason'] ?? $payoutData['error'];
        }

        if ($newStatus === 'completed' && !$walletPayout->completed_at) {
            $updateData['completed_at'] = now();
        } elseif ($newStatus === 'failed' && !$walletPayout->failed_at) {
            $updateData['failed_at'] = now();
            
            // Si le payout échoue, rembourser le wallet
            if ($walletPayout->status !== 'failed') {
                $this->refundFailedWalletPayout($walletPayout);
            }
        }

        $walletPayout->update($updateData);

        Log::info("Callback Moneroo wallet payout traité", [
            'moneroo_id' => $monerooId,
            'wallet_payout_id' => $walletPayout->id,
            'status' => $monerooStatus,
            'new_status' => $newStatus,
        ]);

        return true;
    }

    /**
     * Mapper le statut Moneroo vers le statut local pour wallet payouts
     */
    private function mapMonerooStatusToWalletPayoutStatus(?string $monerooStatus): string
    {
        return match($monerooStatus) {
            'pending' => 'pending',
            'processing' => 'processing',
            'completed', 'success' => 'completed',
            'failed', 'error' => 'failed',
            'cancelled' => 'cancelled',
            default => 'pending',
        };
    }

    /**
     * Rembourser le wallet en cas d'échec du payout
     */
    private function refundFailedWalletPayout(WalletPayout $walletPayout): void
    {
        try {
            $wallet = $walletPayout->wallet;
            
            if (!$wallet) {
                Log::error("Wallet non trouvé pour le remboursement du payout échoué", [
                    'wallet_payout_id' => $walletPayout->id,
                ]);
                return;
            }

            // Créditer le wallet du montant du payout échoué
            $wallet->credit(
                $walletPayout->amount,
                'refund',
                'Remboursement du retrait échoué #' . $walletPayout->id,
                $walletPayout,
                [
                    'payout_id' => $walletPayout->id,
                    'failure_reason' => $walletPayout->failure_reason,
                ]
            );

            Log::info("Wallet remboursé pour le payout échoué", [
                'wallet_payout_id' => $walletPayout->id,
                'wallet_id' => $wallet->id,
                'amount' => $walletPayout->amount,
            ]);
        } catch (\Exception $e) {
            Log::error("Erreur lors du remboursement du wallet pour le payout échoué", [
                'wallet_payout_id' => $walletPayout->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}

