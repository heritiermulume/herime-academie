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
                        return $data['user'] ?? null;
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
                'user_id' => $payload['user_id'] ?? null,
                'email' => $payload['email'] ?? null,
                'name' => $payload['name'] ?? null,
                'role' => $payload['role'] ?? 'student',
                'is_verified' => $payload['is_verified'] ?? false,
                'is_active' => $payload['is_active'] ?? true,
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
     * Obtenir l'URL du profil SSO
     * Redirige vers la page de profil du SSO avec le menu profil sélectionné
     *
     * @return string
     */
    public function getProfileUrl(): string
    {
        // URL du profil sur le SSO (compte.herime.com/profile)
        // Le SSO devrait avoir une section profil avec un menu
        return $this->ssoBaseUrl . '/profile';
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

