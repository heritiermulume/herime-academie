<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /**
     * URL de base de l'API Evolution API
     */
    protected string $baseUrl;
    
    /**
     * Nom de l'instance Evolution API
     */
    protected string $instanceName;
    
    /**
     * Clé API Evolution API
     */
    protected string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.whatsapp.base_url', 'http://localhost:8080');
        $this->instanceName = config('services.whatsapp.instance_name', 'default');
        $this->apiKey = config('services.whatsapp.api_key', '');
    }

    /**
     * Envoyer un message texte via WhatsApp
     * 
     * @param string $phoneNumber Numéro de téléphone au format international (ex: 229XXXXXXXX)
     * @param string $message Contenu du message
     * @return array ['success' => bool, 'message_id' => string|null, 'error' => string|null]
     */
    public function sendMessage(string $phoneNumber, string $message): array
    {
        // Normaliser le numéro de téléphone
        $phoneNumber = $this->normalizePhoneNumber($phoneNumber);
        
        if (!$phoneNumber) {
            return [
                'success' => false,
                'message_id' => null,
                'error' => 'Numéro de téléphone invalide'
            ];
        }

        try {
            // Format du numéro pour Evolution API (format international avec @s.whatsapp.net)
            $formattedPhone = $phoneNumber . '@s.whatsapp.net';
            
            // Evolution API utilise /message/sendText/{instance}
            $url = rtrim($this->baseUrl, '/') . "/message/sendText/{$this->instanceName}";
            
            $headers = [
                'apikey' => $this->apiKey,
                'Content-Type' => 'application/json',
            ];
            
            $payload = [
                'number' => $formattedPhone,
                'text' => $message,
            ];
            
            Log::info('Envoi message WhatsApp', [
                'url' => $url,
                'phone' => $formattedPhone,
                'instance' => $this->instanceName
            ]);
            
            $response = Http::timeout(30)
                ->withHeaders($headers)
                ->post($url, $payload);

            $responseBody = $response->body();
            $responseData = $response->json();
            
            Log::info('Réponse Evolution API', [
                'status' => $response->status(),
                'body' => $responseBody
            ]);

            if ($response->successful()) {
                // Evolution API retourne l'ID du message dans key.id
                $messageId = $responseData['key']['id'] ?? $responseData['id'] ?? null;
                
                // Vérifier si le message a été envoyé avec succès
                if (isset($responseData['key']['id']) || isset($responseData['id'])) {
                    return [
                        'success' => true,
                        'message_id' => $messageId,
                        'error' => null
                    ];
                }
                
                // Certaines versions retournent directement le succès
                if (isset($responseData['status']) && ($responseData['status'] === 'success' || $responseData['status'] === 'PENDING')) {
                    return [
                        'success' => true,
                        'message_id' => $responseData['key']['id'] ?? $responseData['id'] ?? 'pending',
                        'error' => null
                    ];
                }
            }

            // Gestion des erreurs
            $error = 'Erreur inconnue lors de l\'envoi';
            if (is_array($responseData)) {
                $error = $responseData['message'] ?? $responseData['error'] ?? $responseData['response']['message'] ?? $error;
            }
            
            Log::error('Erreur WhatsApp API', [
                'phone' => $phoneNumber,
                'status' => $response->status(),
                'response' => $responseBody,
                'error' => $error
            ]);

            return [
                'success' => false,
                'message_id' => null,
                'error' => $error
            ];
        } catch (\Exception $e) {
            Log::error('Exception lors de l\'envoi WhatsApp', [
                'phone' => $phoneNumber,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message_id' => null,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Envoyer un message avec une image
     * 
     * @param string $phoneNumber Numéro de téléphone
     * @param string $message Texte du message
     * @param string $imageUrl URL de l'image
     * @return array
     */
    public function sendImage(string $phoneNumber, string $message, string $imageUrl): array
    {
        $phoneNumber = $this->normalizePhoneNumber($phoneNumber);
        
        if (!$phoneNumber) {
            return [
                'success' => false,
                'message_id' => null,
                'error' => 'Numéro de téléphone invalide'
            ];
        }

        try {
            // Format du numéro pour Evolution API
            $formattedPhone = $phoneNumber . '@s.whatsapp.net';
            
            $url = rtrim($this->baseUrl, '/') . "/message/sendMedia/{$this->instanceName}";
            
            $headers = [
                'apikey' => $this->apiKey,
                'Content-Type' => 'application/json',
            ];
            
            $payload = [
                'number' => $formattedPhone,
                'mediatype' => 'image',
                'media' => $imageUrl,
                'caption' => $message,
            ];
            
            Log::info('Envoi image WhatsApp', [
                'url' => $url,
                'phone' => $formattedPhone
            ]);
            
            $response = Http::timeout(30)
                ->withHeaders($headers)
                ->post($url, $payload);

            $responseBody = $response->body();
            $responseData = $response->json();

            if ($response->successful()) {
                $messageId = $responseData['key']['id'] ?? $responseData['id'] ?? null;
                
                if ($messageId) {
                    return [
                        'success' => true,
                        'message_id' => $messageId,
                        'error' => null
                    ];
                }
                
                if (isset($responseData['status']) && ($responseData['status'] === 'success' || $responseData['status'] === 'PENDING')) {
                    return [
                        'success' => true,
                        'message_id' => $responseData['key']['id'] ?? 'pending',
                        'error' => null
                    ];
                }
            }

            $error = 'Erreur inconnue lors de l\'envoi';
            if (is_array($responseData)) {
                $error = $responseData['message'] ?? $responseData['error'] ?? $responseData['response']['message'] ?? $error;
            }
            
            Log::error('Erreur WhatsApp API (image)', [
                'phone' => $phoneNumber,
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return [
                'success' => false,
                'message_id' => null,
                'error' => $error
            ];
        } catch (\Exception $e) {
            Log::error('Exception lors de l\'envoi WhatsApp (image)', [
                'phone' => $phoneNumber,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message_id' => null,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Vérifier l'état de connexion de l'instance
     * 
     * @return array ['connected' => bool, 'state' => string|null]
     */
    public function checkConnection(): array
    {
        $url = '';
        try {
            // Vérifier l'état de l'instance Evolution API
            $url = rtrim($this->baseUrl, '/') . "/instance/connectionState/{$this->instanceName}";
            
            $headers = [
                'apikey' => $this->apiKey,
            ];
            
            $response = Http::timeout(10)
                ->withHeaders($headers)
                ->get($url);

            if ($response->successful()) {
                $data = $response->json();
                // Evolution API retourne l'état dans instance.state
                $state = $data['instance']['state'] ?? $data['state'] ?? $data['status'] ?? 'unknown';
                $connected = ($state === 'open' || $state === 'connected');
                
                return [
                    'connected' => $connected,
                    'state' => $state
                ];
            }

            // Si l'endpoint n'existe pas, essayer fetchInstances
            $url2 = rtrim($this->baseUrl, '/') . "/instance/fetchInstances";
            $response2 = Http::timeout(10)
                ->withHeaders($headers)
                ->get($url2);
                
            if ($response2->successful()) {
                $instances = $response2->json();
                if (is_array($instances) && count($instances) > 0) {
                    $instance = $instances[0];
                    $state = $instance['connectionStatus'] ?? 'unknown';
                    $connected = ($state === 'open' || $state === 'connected');
                    
                    return [
                        'connected' => $connected,
                        'state' => $state
                    ];
                }
                
                return [
                    'connected' => false,
                    'state' => 'no_instance'
                ];
            }

            return [
                'connected' => false,
                'state' => 'error'
            ];
        } catch (\Exception $e) {
            Log::error('Erreur lors de la vérification de connexion WhatsApp', [
                'error' => $e->getMessage(),
                'url' => $url ?: 'unknown',
                'base_url' => $this->baseUrl,
                'instance' => $this->instanceName
            ]);

            return [
                'connected' => false,
                'state' => 'error'
            ];
        }
    }

    /**
     * Normaliser un numéro de téléphone au format international
     * 
     * @param string $phoneNumber
     * @return string|null
     */
    protected function normalizePhoneNumber(string $phoneNumber): ?string
    {
        // Supprimer tous les caractères non numériques
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        // Si le numéro commence par 0, le remplacer par l'indicatif du pays (229 pour le Bénin)
        if (strlen($phoneNumber) > 0 && $phoneNumber[0] === '0') {
            $phoneNumber = '229' . substr($phoneNumber, 1);
        }
        
        // Si le numéro ne commence pas par un indicatif, ajouter 229 (Bénin par défaut)
        if (strlen($phoneNumber) > 0 && !preg_match('/^[1-9]\d{1,14}$/', $phoneNumber)) {
            if (strlen($phoneNumber) === 9) {
                $phoneNumber = '229' . $phoneNumber;
            }
        }
        
        // Vérifier que le numéro est valide (entre 10 et 15 chiffres)
        if (strlen($phoneNumber) < 10 || strlen($phoneNumber) > 15) {
            return null;
        }
        
        return $phoneNumber;
    }
}

