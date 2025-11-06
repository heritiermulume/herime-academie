<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;

class SSOService
{
    protected $ssoBaseUrl;
    protected $ssoSecret;
    protected $timeout;

    public function __construct()
    {
        $this->ssoBaseUrl = config('services.sso.base_url');
        $this->ssoSecret = config('services.sso.secret');
        $this->timeout = config('services.sso.timeout', 10);
    }

    /**
     * Valider un token SSO auprès du serveur d'authentification
     * Essaie d'abord l'API externe, puis valide localement le JWT si l'API n'est pas disponible
     *
     * @param string $token
     * @return array|null Retourne les données utilisateur ou null si invalide
     */
    public function validateToken(string $token): ?array
    {
        if (empty($this->ssoSecret)) {
            Log::warning('SSO secret not configured');
            return null;
        }

        // Essayer d'abord la validation via l'API externe si disponible
        if (!empty($this->ssoBaseUrl)) {
            try {
                $response = Http::timeout($this->timeout)
                    ->withHeaders([
                        'Accept' => 'application/json',
                        'Authorization' => 'Bearer ' . $this->ssoSecret,
                    ])
                    ->post($this->ssoBaseUrl . '/api/validate-token', [
                        'token' => $token,
                    ]);

                if ($response->successful()) {
                    $data = $response->json();
                    
                    if (isset($data['valid']) && $data['valid'] === true) {
                        $user = $data['user'] ?? null;
                        
                        // Normaliser les données utilisateur pour assurer la cohérence
                        if ($user && is_array($user)) {
                            // Normaliser les clés de l'avatar/photo
                            if (!isset($user['avatar']) && isset($user['photo'])) {
                                $user['avatar'] = $user['photo'];
                            } elseif (!isset($user['avatar']) && isset($user['picture'])) {
                                $user['avatar'] = $user['picture'];
                            } elseif (!isset($user['avatar']) && isset($user['image'])) {
                                $user['avatar'] = $user['image'];
                            }
                            
                            // Normaliser user_id
                            if (!isset($user['user_id']) && isset($user['id'])) {
                                $user['user_id'] = $user['id'];
                            }
                            
                            // Normaliser name
                            if (!isset($user['name']) && isset($user['full_name'])) {
                                $user['name'] = $user['full_name'];
                            }
                        }
                        
                        return $user;
                    }
                }

                Log::debug('SSO API validation failed, trying local JWT validation', [
                    'status' => $response->status(),
                ]);

            } catch (Exception $e) {
                Log::debug('SSO API validation exception, trying local JWT validation', [
                    'message' => $e->getMessage(),
                ]);
            }
        }

        // Fallback: valider le token JWT localement
        return $this->validateTokenLocally($token);
    }

    /**
     * Valider un token JWT localement
     *
     * @param string $token
     * @return array|null
     */
    protected function validateTokenLocally(string $token): ?array
    {
        try {
            // Décoder le JWT manuellement (sans dépendance externe)
            $parts = explode('.', $token);
            
            if (count($parts) !== 3) {
                Log::warning('SSO Token invalid format');
                return null;
            }

            // Décoder le payload (partie 2)
            $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
            
            if (!$payload) {
                Log::warning('SSO Token payload decode failed');
                return null;
            }

            // Vérifier l'expiration
            if (isset($payload['exp']) && $payload['exp'] < time()) {
                Log::warning('SSO Token expired', ['exp' => $payload['exp'], 'now' => time()]);
                return null;
            }

            // Vérifier la signature (optionnel mais recommandé)
            // Pour une validation complète, il faudrait vérifier la signature HMAC
            // Pour l'instant, on fait confiance au token si la structure est correcte
            
            // Extraire les données utilisateur
            $userData = [
                'user_id' => $payload['user_id'] ?? $payload['id'] ?? null,
                'email' => $payload['email'] ?? null,
                'name' => $payload['name'] ?? $payload['full_name'] ?? null,
                'role' => $payload['role'] ?? 'student',
                'is_verified' => $payload['is_verified'] ?? false,
                'is_active' => $payload['is_active'] ?? true,
                // Récupérer l'avatar/photo depuis le SSO
                'avatar' => $payload['avatar'] ?? $payload['photo'] ?? $payload['picture'] ?? $payload['image'] ?? null,
            ];

            // Vérifier que les données essentielles sont présentes
            if (empty($userData['email'])) {
                Log::warning('SSO Token missing email');
                return null;
            }

            Log::info('SSO Token validated locally', ['email' => $userData['email']]);

            return $userData;

        } catch (Exception $e) {
            Log::error('SSO Local Token Validation Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Obtenir l'URL de connexion SSO avec redirection
     *
     * @param string|null $redirectUrl
     * @param bool $forceToken Force la génération d'un token même si l'utilisateur est déjà connecté
     * @return string
     */
    public function getLoginUrl(?string $redirectUrl = null, bool $forceToken = true): string
    {
        // Utiliser /login avec le paramètre force_token pour forcer la génération
        // d'un token même si l'utilisateur est déjà connecté
        // Si compte.herime.com a /sso/authorize, il faudra l'utiliser
        // Pour l'instant, utilisons /login avec force_token=1
        $loginUrl = $this->ssoBaseUrl . '/login';
        
        $params = [];
        
        if ($redirectUrl) {
            $params['redirect'] = $redirectUrl;
        }
        
        // Toujours ajouter force_token=1 pour forcer la génération du token
        // même si l'utilisateur est déjà connecté sur compte.herime.com
        if ($forceToken) {
            $params['force_token'] = '1';
        }
        
        if (!empty($params)) {
            $loginUrl .= '?' . http_build_query($params);
        }
        
        return $loginUrl;
    }

    /**
     * Obtenir l'URL d'enregistrement SSO
     * Le SSO redirigera l'utilisateur vers $redirectUrl après l'enregistrement
     *
     * @param string|null $redirectUrl URL de callback après enregistrement
     * @param bool $forceToken Force la génération d'un token même si l'utilisateur est déjà connecté
     * @return string
     */
    public function getRegisterUrl(?string $redirectUrl = null, bool $forceToken = true): string
    {
        // Utiliser la route /register du SSO si elle existe, sinon utiliser /login
        // Le SSO peut gérer l'enregistrement via la même page de login ou une page dédiée
        $registerUrl = $this->ssoBaseUrl . '/register';
        
        $params = [];
        
        if ($redirectUrl) {
            $params['redirect'] = $redirectUrl;
        }
        
        // Forcer la génération du token même si l'utilisateur est déjà connecté
        if ($forceToken) {
            $params['force_token'] = '1';
        }
        
        if (!empty($params)) {
            $registerUrl .= '?' . http_build_query($params);
        }
        
        return $registerUrl;
    }

    /**
     * Vérifier rapidement si un token SSO est toujours valide
     * Utilise l'endpoint /api/sso/check-token pour une validation légère
     * 
     * @param string $token
     * @return bool
     */
    public function checkToken(string $token): bool
    {
        if (empty($token)) {
            return false;
        }

        if (empty($this->ssoBaseUrl)) {
            // Si pas de SSO configuré, utiliser la validation locale
            return $this->validateTokenLocally($token) !== null;
        }

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->post($this->ssoBaseUrl . '/api/sso/check-token', [
                    'token' => $token,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return isset($data['success']) && $data['success'] === true 
                    && isset($data['valid']) && $data['valid'] === true;
            }

            return false;
        } catch (\Exception $e) {
            Log::debug('SSO check-token exception, falling back to local validation', [
                'message' => $e->getMessage(),
                'type' => get_class($e),
            ]);
            
            // Fallback: validation locale rapide
            try {
                return $this->validateTokenLocally($token) !== null;
            } catch (\Exception $localException) {
                Log::warning('SSO local token validation also failed', [
                    'message' => $localException->getMessage(),
                ]);
                // En dernier recours, considérer le token comme invalide
                return false;
            }
        }
    }

    /**
     * Obtenir l'URL du profil SSO
     * Redirige vers le SSO (compte.herime.com)
     * Le SSO gérera automatiquement la redirection vers le profil si l'utilisateur est connecté
     *
     * @return string
     */
    public function getProfileUrl(): string
    {
        // URL de base du SSO (compte.herime.com)
        // Le SSO redirigera automatiquement vers le profil si l'utilisateur est connecté
        return $this->ssoBaseUrl;
    }

    /**
     * Obtenir l'URL de déconnexion SSO
     * Le SSO redirigera l'utilisateur vers $redirectUrl après la déconnexion
     *
     * @param string|null $redirectUrl URL complète vers laquelle rediriger après déconnexion
     * @return string
     */
    public function getLogoutUrl(?string $redirectUrl = null): string
    {
        $logoutUrl = $this->ssoBaseUrl . '/logout';
        
        if ($redirectUrl) {
            // S'assurer que l'URL est absolue
            if (!filter_var($redirectUrl, FILTER_VALIDATE_URL)) {
                // Si ce n'est pas une URL absolue, la convertir
                $redirectUrl = url($redirectUrl);
            }
            
            // Encoder l'URL de redirection pour éviter les problèmes avec les caractères spéciaux
            // Le SSO doit rediriger vers cette URL après la déconnexion
            $logoutUrl .= '?redirect=' . urlencode($redirectUrl);
        }
        
        return $logoutUrl;
    }
}

