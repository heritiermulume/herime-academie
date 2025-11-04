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
     *
     * @param string $token
     * @return array|null Retourne les données utilisateur ou null si invalide
     */
    public function validateToken(string $token): ?array
    {
        if (empty($this->ssoBaseUrl) || empty($this->ssoSecret)) {
            Log::warning('SSO credentials not configured');
            return null;
        }

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

            Log::warning('SSO Token Validation Failed', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return null;

        } catch (Exception $e) {
            Log::error('SSO Token Validation Exception', [
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
     * Obtenir l'URL de déconnexion SSO
     *
     * @param string|null $redirectUrl
     * @return string
     */
    public function getLogoutUrl(?string $redirectUrl = null): string
    {
        $logoutUrl = $this->ssoBaseUrl . '/logout';
        
        if ($redirectUrl) {
            $logoutUrl .= '?redirect=' . urlencode($redirectUrl);
        }
        
        return $logoutUrl;
    }
}

