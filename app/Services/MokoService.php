<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Exception;

class MokoService
{
    protected $apiUrl;
    protected $tokenUrl;
    protected $merchantId;
    protected $merchantSecret;
    protected $timeout;

    public function __construct()
    {
        $this->apiUrl = config('moko.api_url');
        $this->tokenUrl = config('moko.token_url');
        $this->merchantId = config('moko.merchant_id');
        $this->merchantSecret = config('moko.merchant_secret');
        $this->timeout = config('moko.timeout_seconds', 30);
    }

    /**
     * Générer un token d'authentification
     */
    public function generateToken()
    {
        // Mode test si les credentials ne sont pas configurés
        if (empty($this->merchantId) || empty($this->merchantSecret)) {
            Log::warning('MOKO Credentials not configured, using test mode');
            $testToken = 'test_token_' . time() . '_' . Str::random(10);
            Cache::put('moko_token', $testToken, now()->addMinutes(config('moko.token_expiry_minutes', 20)));
            return $testToken;
        }

        try {
            $response = Http::timeout($this->timeout)
                ->post($this->tokenUrl, [
                    'merchant_id' => $this->merchantId,
                    'merchant_secrete' => $this->merchantSecret,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $token = $data['token'] ?? null;
                
                if ($token) {
                    // Cache le token pour éviter de le régénérer à chaque requête
                    Cache::put('moko_token', $token, now()->addMinutes(config('moko.token_expiry_minutes', 20)));
                    return $token;
                }
            }

            Log::error('MOKO Token Generation Failed', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            throw new Exception('Échec de génération du token MOKO Afrika');

        } catch (Exception $e) {
            Log::error('MOKO Token Generation Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Obtenir le token d'authentification (depuis le cache ou en générer un nouveau)
     */
    public function getToken()
    {
        $token = Cache::get('moko_token');
        
        if (!$token) {
            $token = $this->generateToken();
        }

        return $token;
    }

    /**
     * Initier une transaction de débit (C2B)
     */
    public function initiateDebit($data)
    {
        // Mode test si les credentials ne sont pas configurés
        if (empty($this->merchantId) || empty($this->merchantSecret)) {
            Log::warning('MOKO Credentials not configured, using test mode for transaction');
            
            // Simuler une réponse de succès MOKO
            $testResponse = [
                'Amount' => $data['amount'],
                'Comment' => 'Transaction de test - Credentials MOKO non configurés',
                'Created_At' => now()->format('Y-m-d H:i:s.u'),
                'Currency' => $data['currency'] ?? config('moko.default_currency'),
                'Customer_Number' => $this->formatPhoneNumber($data['customer_number']),
                'Reference' => $data['reference'],
                'Status' => 'Success',
                'Trans_Status' => 'Successful',
                'Trans_Status_Description' => 'Transaction de test réussie',
                'Transaction_id' => 'TEST_' . time() . '_' . Str::random(8),
                'Updated_At' => now()->format('Y-m-d H:i:s.u')
            ];
            
            return [
                'success' => true,
                'data' => $testResponse,
                'status_code' => 200
            ];
        }

        try {
            $token = $this->getToken();

            $payload = [
                'merchant_id' => $this->merchantId,
                'merchant_secrete' => $this->merchantSecret,
                'amount' => (string) $data['amount'],
                'currency' => $data['currency'] ?? config('moko.default_currency'),
                'action' => 'debit',
                'customer_number' => $this->formatPhoneNumber($data['customer_number']),
                'firstname' => $data['firstname'],
                'lastname' => $data['lastname'],
                'e-mail' => $data['email'],
                'reference' => $data['reference'],
                'method' => $data['method'],
                'callback_url' => $data['callback_url'] ?? config('moko.callback_url'),
            ];

            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json',
                ])
                ->post($this->apiUrl, $payload);

            return $this->handleResponse($response, 'Debit Transaction');

        } catch (Exception $e) {
            Log::error('MOKO Debit Transaction Failed', [
                'message' => $e->getMessage(),
                'data' => $data
            ]);
            throw $e;
        }
    }

    /**
     * Initier une transaction de crédit (B2C)
     */
    public function initiateCredit($data)
    {
        try {
            $token = $this->getToken();

            $payload = [
                'merchant_id' => $this->merchantId,
                'merchant_secrete' => $this->merchantSecret,
                'amount' => (string) $data['amount'],
                'currency' => $data['currency'] ?? config('moko.default_currency'),
                'action' => 'credit',
                'customer_number' => $this->formatPhoneNumber($data['customer_number']),
                'firstname' => $data['firstname'],
                'lastname' => $data['lastname'],
                'e-mail' => $data['email'],
                'reference' => $data['reference'],
                'method' => $data['method'],
                'callback_url' => $data['callback_url'] ?? config('moko.callback_url'),
            ];

            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json',
                ])
                ->post($this->apiUrl, $payload);

            return $this->handleResponse($response, 'Credit Transaction');

        } catch (Exception $e) {
            Log::error('MOKO Credit Transaction Failed', [
                'message' => $e->getMessage(),
                'data' => $data
            ]);
            throw $e;
        }
    }

    /**
     * Vérifier le statut d'une transaction
     */
    public function verifyTransaction($reference)
    {
        // Mode test si les credentials ne sont pas configurés
        if (empty($this->merchantId) || empty($this->merchantSecret)) {
            Log::warning('MOKO Credentials not configured, using test mode for verification');
            
            // Simuler une vérification de succès
            $testResponse = [
                'Amount' => '99.00',
                'Comment' => 'Transaction de test vérifiée avec succès',
                'Created_At' => now()->format('Y-m-d H:i:s.u'),
                'Currency' => config('moko.default_currency'),
                'Customer_Number' => '+243824449218',
                'Reference' => $reference,
                'Status' => 'Success',
                'Trans_Status' => 'Successful',
                'Trans_Status_Description' => 'Transaction de test réussie',
                'Transaction_id' => 'TEST_' . time() . '_' . Str::random(8),
                'Updated_At' => now()->format('Y-m-d H:i:s.u')
            ];
            
            return [
                'success' => true,
                'data' => $testResponse,
                'status_code' => 200
            ];
        }

        try {
            $token = $this->getToken();

            $payload = [
                'merchant_id' => $this->merchantId,
                'merchant_secrete' => $this->merchantSecret,
                'action' => 'verify',
                'reference' => $reference,
            ];

            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json',
                ])
                ->post($this->apiUrl, $payload);

            return $this->handleResponse($response, 'Transaction Verification');

        } catch (Exception $e) {
            Log::error('MOKO Transaction Verification Failed', [
                'message' => $e->getMessage(),
                'reference' => $reference
            ]);
            throw $e;
        }
    }

    /**
     * Formater le numéro de téléphone pour MOKO Afrika
     */
    protected function formatPhoneNumber($phoneNumber)
    {
        // Supprimer tous les espaces et caractères spéciaux
        $phone = preg_replace('/[^0-9+]/', '', $phoneNumber);
        
        // Si le numéro commence par +243, le garder tel quel
        if (str_starts_with($phone, '+243')) {
            return $phone;
        }
        
        // Si le numéro commence par 243, ajouter le +
        if (str_starts_with($phone, '243')) {
            return '+' . $phone;
        }
        
        // Si le numéro commence par 0, remplacer par +243
        if (str_starts_with($phone, '0')) {
            return '+243' . substr($phone, 1);
        }
        
        // Sinon, ajouter +243
        return '+243' . $phone;
    }

    /**
     * Gérer la réponse de l'API MOKO
     */
    protected function handleResponse($response, $operation)
    {
        $statusCode = $response->status();
        $responseData = $response->json();

        Log::info("MOKO {$operation} Response", [
            'status_code' => $statusCode,
            'response' => $responseData
        ]);

        if ($response->successful()) {
            return [
                'success' => true,
                'data' => $responseData,
                'status_code' => $statusCode
            ];
        }

        // Gérer les erreurs spécifiques selon la documentation MOKO
        $errorMessage = $this->getErrorMessage($statusCode, $responseData);
        
        return [
            'success' => false,
            'error' => $errorMessage,
            'status_code' => $statusCode,
            'data' => $responseData
        ];
    }

    /**
     * Obtenir le message d'erreur selon le code d'erreur MOKO
     */
    protected function getErrorMessage($statusCode, $responseData)
    {
        $errorMessages = [
            400 => 'Une erreur s\'est produite lors de l\'exécution de la requête.',
            401 => 'Vous ne pouvez pas effectuer un paiement de ce montant.',
            402 => 'Votre solde est insuffisant pour effectuer ce paiement.',
            404 => 'Identifiant de transaction non reconnu dans le système.',
            405 => 'Le préfixe du numéro de téléphone du client est incorrect.',
            407 => 'La valeur de la devise fournie est incorrecte ou non reconnue.',
            408 => 'L\'action spécifiée n\'est pas reconnue par le système.',
            409 => 'Problème de connectivité avec la base de données.',
            500 => 'Erreur interne du serveur.',
        ];

        $message = $errorMessages[$statusCode] ?? 'Erreur inconnue';
        
        // Ajouter des détails supplémentaires si disponibles
        if (isset($responseData['message'])) {
            $message .= ' Détails: ' . $responseData['message'];
        }

        return $message;
    }

    /**
     * Obtenir les méthodes de paiement disponibles
     */
    public function getAvailablePaymentMethods()
    {
        return config('moko.payment_methods');
    }

    /**
     * Valider les données de transaction
     */
    public function validateTransactionData($data)
    {
        $required = ['amount', 'customer_number', 'firstname', 'lastname', 'email', 'reference', 'method'];
        
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new Exception("Le champ '{$field}' est requis.");
            }
        }

        // Valider le montant
        if (!is_numeric($data['amount']) || $data['amount'] <= 0) {
            throw new Exception('Le montant doit être un nombre positif.');
        }

        // Valider la méthode de paiement
        $availableMethods = array_keys($this->getAvailablePaymentMethods());
        if (!in_array($data['method'], $availableMethods)) {
            throw new Exception('Méthode de paiement non supportée.');
        }

        // Valider l'email
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Adresse email invalide.');
        }

        return true;
    }
}
