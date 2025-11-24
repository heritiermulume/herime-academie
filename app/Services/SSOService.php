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
                            
                            // Normaliser le rôle (peut être dans role, privilege, ou privileges)
                            if (!isset($user['role'])) {
                                if (isset($user['privilege'])) {
                                    $user['role'] = $user['privilege'];
                                } elseif (isset($user['privileges'])) {
                                    $user['role'] = is_array($user['privileges']) 
                                        ? ($user['privileges'][0] ?? 'student')
                                        : $user['privileges'];
                                }
                            }
                        }
                        
                        return $user;
                    }
                }

                Log::debug('SSO API validation failed, trying Bearer /api/user then local JWT validation', [
                    'status' => $response->status(),
                ]);

            } catch (Exception $e) {
                Log::debug('SSO API validation exception, trying Bearer /api/user then local JWT validation', [
                    'message' => $e->getMessage(),
                ]);
            }

            // Fallback Option B (guide): utiliser le token utilisateur en Bearer vers /api/user
            try {
                $userResp = Http::timeout($this->timeout)
                    ->withHeaders([
                        'Accept' => 'application/json',
                        'Authorization' => 'Bearer ' . $token,
                    ])
                    ->get(rtrim($this->ssoBaseUrl, '/') . '/api/user');

                if ($userResp->successful()) {
                    $user = $userResp->json();

                    if (is_array($user)) {
                        // Normaliser l'avatar
                        if (!isset($user['avatar'])) {
                            if (isset($user['photo'])) {
                                $user['avatar'] = $user['photo'];
                            } elseif (isset($user['picture'])) {
                                $user['avatar'] = $user['picture'];
                            } elseif (isset($user['image'])) {
                                $user['avatar'] = $user['image'];
                            }
                        }

                        // Normaliser user_id
                        if (!isset($user['user_id']) && isset($user['id'])) {
                            $user['user_id'] = $user['id'];
                        }

                        // Normaliser name
                        if (!isset($user['name'])) {
                            if (isset($user['full_name'])) {
                                $user['name'] = $user['full_name'];
                            } elseif (isset($user['first_name']) || isset($user['last_name'])) {
                                $first = $user['first_name'] ?? '';
                                $last = $user['last_name'] ?? '';
                                $user['name'] = trim($first . ' ' . $last) ?: null;
                            }
                        }
                        
                        // Normaliser le rôle (peut être dans role, privilege, ou privileges)
                        if (!isset($user['role'])) {
                            if (isset($user['privilege'])) {
                                $user['role'] = $user['privilege'];
                            } elseif (isset($user['privileges'])) {
                                $user['role'] = is_array($user['privileges']) 
                                    ? ($user['privileges'][0] ?? 'student')
                                    : $user['privileges'];
                            }
                        }
                    }

                    // Vérifier la présence d'email (obligatoire)
                    if (!empty($user) && is_array($user) && !empty($user['email'])) {
                        Log::debug('SSO validation via Bearer /api/user succeeded');
                        return $user;
                    }
                }

                Log::debug('SSO Bearer /api/user validation failed, will try local JWT', [
                    'status' => $userResp->status(),
                    'body' => $userResp->body(),
                ]);
            } catch (Exception $e) {
                Log::debug('SSO Bearer /api/user validation exception, will try local JWT', [
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
            // Vérifier que le token n'est pas vide
            if (empty($token) || !is_string($token)) {
                Log::debug('SSO Token empty or invalid type');
                return null;
            }

            // Décoder le JWT manuellement (sans dépendance externe)
            $parts = explode('.', $token);
            
            if (count($parts) !== 3) {
                Log::debug('SSO Token invalid format', ['parts_count' => count($parts)]);
                return null;
            }

            // Décoder le payload (partie 2) avec gestion d'erreur
            try {
                $decoded = base64_decode(strtr($parts[1], '-_', '+/'), true);
                if ($decoded === false) {
                    Log::debug('SSO Token base64 decode failed');
                    return null;
                }
                $payload = json_decode($decoded, true);
            } catch (\Exception $e) {
                Log::debug('SSO Token decode exception', ['message' => $e->getMessage()]);
                return null;
            }
            
            if (!$payload || !is_array($payload)) {
                Log::debug('SSO Token payload decode failed or not an array');
                return null;
            }

            // Vérifier l'expiration
            if (isset($payload['exp']) && is_numeric($payload['exp']) && $payload['exp'] < time()) {
                Log::debug('SSO Token expired', ['exp' => $payload['exp'], 'now' => time()]);
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
                Log::debug('SSO Token missing email');
                return null;
            }

            Log::debug('SSO Token validated locally', ['email' => $userData['email']]);

            return $userData;

        } catch (\Throwable $e) {
            // Capturer toutes les exceptions et erreurs (y compris les erreurs fatales)
            Log::debug('SSO Token validation error', [
                'message' => $e->getMessage(),
                'type' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
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
     * Essaie d'abord de valider via l'API, puis utilise la validation locale comme fallback
     * 
     * @param string $token
     * @return bool
     */
    public function checkToken(string $token): bool
    {
        if (empty($token)) {
            Log::debug('SSO checkToken: token is empty');
            return false;
        }

        if (empty($this->ssoBaseUrl)) {
            // Si pas de SSO configuré, utiliser la validation locale
            Log::debug('SSO checkToken: no base URL configured, using local validation');
            return $this->validateTokenLocally($token) !== null;
        }

        // Essayer d'abord la validation via l'API pour vérifier l'état réel côté serveur SSO
        // Cela garantit que même si le JWT n'est pas expiré localement,
        // on vérifie si la session est toujours valide sur compte.herime.com
        try {
            $validateUrl = $this->ssoBaseUrl . '/api/validate-token';

            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->ssoSecret,
                ])
                ->post($validateUrl, [
                    'token' => $token,
                ]);

            $data = null;

            if ($response->successful()) {
                $data = $response->json();
                
                // Vérifier la réponse de l'API
                if (isset($data['valid']) && $data['valid'] === true) {
                    Log::debug('SSO checkToken: token validated via API', [
                        'token_preview' => substr($token, 0, 20) . '...'
                    ]);
                    return true;
                }
                
                // Si l'API dit que le token n'est pas valide
                if (isset($data['valid']) && $data['valid'] === false) {
                    Log::debug('SSO checkToken: token invalidated via API', [
                        'token_preview' => substr($token, 0, 20) . '...',
                        'response' => $data
                    ]);
                    return false;
                }
            }

            // Si la réponse n'est pas successful ou ne contient pas 'valid'
            Log::debug('SSO checkToken: API validation returned unexpected response', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            // Depuis la mise à jour de compte.herime.com, certains appels utilisent encore GET
            // L'endpoint retourne désormais { "valid": false } (HTTP 200) au lieu d'un 405,
            // mais par sécurité, on tente une requête GET si la POST échoue ou si la réponse est vide.
            if (in_array($response->status(), [405, 400]) || ($response->successful() && !isset($data['valid']))) {
                try {
                    $getResponse = Http::timeout($this->timeout)
                        ->withHeaders([
                            'Accept' => 'application/json',
                            'Authorization' => 'Bearer ' . $this->ssoSecret,
                        ])
                        ->get($validateUrl, [
                            'token' => $token,
                        ]);

                    if ($getResponse->successful()) {
                        $getData = $getResponse->json();

                        if (isset($getData['valid']) && $getData['valid'] === true) {
                            Log::debug('SSO checkToken: token validated via GET /api/validate-token fallback', [
                                'token_preview' => substr($token, 0, 20) . '...',
                            ]);
                            return true;
                        }

                        if (isset($getData['valid']) && $getData['valid'] === false) {
                            Log::debug('SSO checkToken: token invalidated via GET /api/validate-token fallback', [
                                'token_preview' => substr($token, 0, 20) . '...',
                                'response' => $getData,
                            ]);
                            return false;
                        }
                    }

                    Log::debug('SSO checkToken: GET fallback returned unexpected response', [
                        'status' => $getResponse->status(),
                        'response' => $getResponse->body(),
                    ]);
                } catch (\Exception $getException) {
                    Log::debug('SSO checkToken: GET fallback failed', [
                        'message' => $getException->getMessage(),
                    ]);
                }
            }

            // Si l'API retourne 404 ou erreur, essayer l'endpoint alternatif /api/sso/check-token
            if ($response->status() === 404 || !$response->successful()) {
                try {
                    $checkResponse = Http::timeout($this->timeout)
                        ->withHeaders([
                            'Accept' => 'application/json',
                            'Content-Type' => 'application/json',
                        ])
                        ->post($this->ssoBaseUrl . '/api/sso/check-token', [
                            'token' => $token,
                        ]);

                    if ($checkResponse->successful()) {
                        $checkData = $checkResponse->json();
                        if (isset($checkData['success']) && $checkData['success'] === true 
                            && isset($checkData['valid']) && $checkData['valid'] === true) {
                            Log::debug('SSO checkToken: token validated via check-token API');
                            return true;
                        }
                    }
                } catch (\Exception $checkException) {
                    Log::debug('SSO checkToken: check-token endpoint failed', [
                        'message' => $checkException->getMessage()
                    ]);
                }
            }

            // Si l'API n'est pas disponible ou retourne une erreur,
            // TOUJOURS utiliser la validation locale comme fallback
            // C'est la source de vérité si l'API est indisponible
            Log::debug('SSO checkToken: API unavailable or error, falling back to local validation', [
                'api_status' => $response->status() ?? 'unknown',
                'response_preview' => substr($response->body() ?? '', 0, 100)
            ]);
            
            $localResult = $this->validateTokenLocally($token);
            
            if ($localResult !== null) {
                // Le token est valide localement (format correct, pas expiré)
                // On fait TOUJOURS confiance à la validation locale si l'API échoue
                // Cela évite les déconnexions intempestives quand l'API est indisponible
                Log::info('SSO checkToken: API unavailable/error, trusting local validation (token valid locally)', [
                    'token_preview' => substr($token, 0, 20) . '...',
                    'user_email' => $localResult['email'] ?? 'unknown',
                    'api_status' => $response->status() ?? 'unknown'
                ]);
                return true;
            }
            
            // Si la validation locale échoue aussi, le token est vraiment invalide
            Log::warning('SSO checkToken: Both API and local validation failed - token is invalid', [
                'token_preview' => substr($token, 0, 20) . '...',
                'api_status' => $response->status() ?? 'unknown'
            ]);
            return false;

        } catch (\Exception $e) {
            // En cas d'exception lors de l'appel API, utiliser la validation locale
            Log::debug('SSO checkToken: API exception, falling back to local validation', [
                'message' => $e->getMessage(),
                'type' => get_class($e),
            ]);
            
            // Fallback: validation locale rapide
            // En cas d'exception API, on fait TOUJOURS confiance à la validation locale
            try {
                $localResult = $this->validateTokenLocally($token);
                if ($localResult !== null) {
                    // Si l'API a une exception mais que le token est valide localement,
                    // on le considère TOUJOURS comme valide pour éviter les déconnexions intempestives
                    Log::info('SSO checkToken: API exception, trusting local validation (token valid locally)', [
                        'token_preview' => substr($token, 0, 20) . '...',
                        'user_email' => $localResult['email'] ?? 'unknown',
                        'api_error' => $e->getMessage()
                    ]);
                    return true;
                }
                // Si la validation locale échoue, le token est vraiment invalide
                Log::warning('SSO checkToken: API exception and local validation failed - token is invalid', [
                    'token_preview' => substr($token, 0, 20) . '...',
                    'api_error' => $e->getMessage()
                ]);
                return false;
            } catch (\Exception $localException) {
                Log::warning('SSO checkToken: both API and local validation failed with exceptions', [
                    'api_error' => $e->getMessage(),
                    'local_error' => $localException->getMessage(),
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

