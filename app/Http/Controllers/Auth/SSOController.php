<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\SSOService;
use App\Models\User;
use App\Http\Controllers\CartController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SSOController extends Controller
{
    protected $ssoService;

    public function __construct(SSOService $ssoService)
    {
        $this->ssoService = $ssoService;
    }

    /**
     * Callback SSO - reçoit le token depuis compte.herime.com
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function callback(Request $request)
    {
        $token = $request->query('token');
        $redirect = $request->query('redirect', route('dashboard'));

        if (!$token) {
            Log::warning('SSO callback received without token');
            // Toujours rediriger vers SSO, jamais vers la vue locale
            return $this->redirectToSSO($request);
        }

        // Valider le token auprès du serveur SSO
        $userData = $this->ssoService->validateToken($token);

        if (!$userData) {
            Log::warning('SSO token validation failed', ['token' => substr($token, 0, 20) . '...']);
            // Toujours rediriger vers SSO, jamais vers la vue locale
            return $this->redirectToSSO($request);
        }

        try {
            // Trouver ou créer l'utilisateur
            $user = $this->findOrCreateUser($userData);

            // Connecter l'utilisateur
            Auth::login($user, true); // true = remember me

            // Régénérer la session pour la sécurité
            $request->session()->regenerate();

            // Stocker le token SSO dans la session pour validation ultérieure
            // (optionnel, seulement si nécessaire pour la validation avant actions)
            try {
                if ($token) {
                    $request->session()->put('sso_token', $token);
                }
            } catch (\Exception $sessionException) {
                // Si l'écriture en session échoue, logger mais continuer
                Log::warning('SSO token storage in session failed but login continues', [
                    'message' => $sessionException->getMessage(),
                    'user_id' => $user->id
                ]);
            }

            // Synchroniser le panier de session avec la base de données
            // Protégé contre les erreurs pour ne pas bloquer la connexion
            try {
                $cartController = new CartController();
                $cartController->syncSessionToDatabase();
            } catch (\Exception $cartException) {
                Log::warning('SSO cart sync failed but login continues', [
                    'message' => $cartException->getMessage(),
                    'user_id' => $user->id
                ]);
            }

            Log::info('SSO login successful', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            // Valider l'URL de redirection pour éviter les boucles
            $validatedRedirect = $this->validateRedirectUrl($redirect);
            
            // Rediriger vers la page demandée ou le dashboard
            return redirect()->intended($validatedRedirect);

        } catch (\Exception $e) {
            Log::error('SSO callback error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Toujours rediriger vers SSO, jamais vers la vue locale
            return $this->redirectToSSO($request);
        }
    }

    /**
     * Trouver ou créer un utilisateur à partir des données SSO
     *
     * @param array $userData
     * @return User
     */
    protected function findOrCreateUser(array $userData): User
    {
        $email = $userData['email'] ?? null;
        
        if (!$email) {
            throw new \Exception('Email manquant dans les données SSO');
        }

        // Normaliser le rôle AVANT toute opération
        $role = $this->normalizeRole($userData['role'] ?? 'student');

        // Chercher l'utilisateur par email
        $user = User::where('email', $email)->first();

        $provider = $userData['provider'] ?? 'herime';
        $ssoUserId = $userData['user_id'] ?? null;
        $ssoMetadata = $userData['metadata'] ?? null;

        if ($user) {
            // Mettre à jour les informations utilisateur depuis SSO
            $updateData = [
                'name' => $userData['name'] ?? $user->name,
                'email' => $email,
                'is_verified' => $userData['is_verified'] ?? $user->is_verified,
                'is_active' => $userData['is_active'] ?? true,
                'last_login_at' => now(),
                'role' => $role, // Utiliser le rôle normalisé
            ];
            
            // Mettre à jour l'avatar si fourni par le SSO (toujours mettre à jour, même si vide)
            if (isset($userData['avatar'])) {
                $updateData['avatar'] = $userData['avatar'] ?: null;
            }
            
            if (!empty($ssoUserId)) {
                $updateData['sso_id'] = $ssoUserId;
            }

            if (!empty($provider)) {
                $updateData['sso_provider'] = $provider;
            }

            if (!empty($ssoMetadata)) {
                $updateData['sso_metadata'] = $ssoMetadata;
            }
            
            $user->update($updateData);

            return $user;
        }

        // Créer un nouvel utilisateur avec le rôle normalisé
        try {
            $userDataCreate = [
                'name' => $userData['name'] ?? 'Utilisateur',
                'email' => $email,
                'password' => Hash::make(Str::random(32)), // Mot de passe aléatoire (non utilisé avec SSO)
                'role' => $role, // Utiliser le rôle normalisé
                'is_verified' => $userData['is_verified'] ?? false,
                'is_active' => $userData['is_active'] ?? true,
                'last_login_at' => now(),
                'sso_id' => $ssoUserId,
                'sso_provider' => $provider,
            ];
            
            // Ajouter l'avatar si fourni par le SSO (toujours ajouter, même si vide)
            if (isset($userData['avatar'])) {
                $userDataCreate['avatar'] = $userData['avatar'] ?: null;
            }
            
            if (!empty($ssoMetadata)) {
                $userDataCreate['sso_metadata'] = $ssoMetadata;
            }
            
            $user = User::create($userDataCreate);

            Log::info('SSO user created', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role
            ]);

            return $user;
        } catch (\Illuminate\Database\QueryException $e) {
            // Si l'utilisateur existe déjà (contrainte unique), le récupérer
            if ($e->getCode() == 23000 || str_contains($e->getMessage(), 'Duplicate entry')) {
                Log::warning('SSO user creation failed - user already exists, fetching existing user', [
                    'email' => $email,
                    'error' => $e->getMessage()
                ]);
                
                $user = User::where('email', $email)->first();
                if ($user) {
                    // Mettre à jour les informations
                    $updateData = [
                        'name' => $userData['name'] ?? $user->name,
                        'is_verified' => $userData['is_verified'] ?? $user->is_verified,
                        'is_active' => $userData['is_active'] ?? true,
                        'last_login_at' => now(),
                        'role' => $role,
                    ];
                    
                    if (isset($userData['avatar'])) {
                        $updateData['avatar'] = $userData['avatar'] ?: null;
                    }
                    
                    if (isset($userData['user_id']) && !empty($userData['user_id'])) {
                        $updateData['sso_id'] = $userData['user_id'];
                    }
                    
                    if (!empty($provider)) {
                        $updateData['sso_provider'] = $provider;
                    }

                    if (!empty($ssoMetadata)) {
                        $updateData['sso_metadata'] = $ssoMetadata;
                    }

                    $user->update($updateData);
                    return $user;
                }
            }
            
            // Autre erreur de base de données
            Log::error('SSO user creation failed', [
                'email' => $email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('SSO user creation exception', [
                'email' => $email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Normaliser le rôle utilisateur
     * Conserve super_user comme tel (il a accès à l'admin via isAdmin())
     *
     * @param string|null $role
     * @return string
     */
    protected function normalizeRole(?string $role): string
    {
        $validRoles = ['student', 'instructor', 'admin', 'affiliate', 'super_user'];
        
        // Si aucun rôle fourni, retourner student par défaut
        if (empty($role)) {
            return 'student';
        }
        
        // Conserver super_user tel quel (il aura accès à l'admin via isAdmin())
        // S'assurer que le rôle est valide
        if (!in_array($role, $validRoles)) {
            Log::warning('SSO: Invalid role provided, defaulting to student', [
                'invalid_role' => $role
            ]);
            return 'student';
        }
        
        return $role;
    }

    /**
     * Valider l'URL de redirection pour éviter les boucles
     * 
     * @param string $redirectUrl
     * @return string
     */
    protected function validateRedirectUrl(string $redirectUrl): string
    {
        try {
            // Si l'URL est vide ou invalide, retourner le dashboard
            if (empty($redirectUrl)) {
                return route('dashboard');
            }

            // Parser l'URL
            $parsed = parse_url($redirectUrl);
            
            // Si l'URL ne peut pas être parsée, retourner le dashboard
            if (!$parsed) {
                return route('dashboard');
            }

            // Extraire le domaine de l'URL de redirection
            $redirectHost = $parsed['host'] ?? null;
            
            // Extraire le domaine SSO depuis la configuration
            $ssoBaseUrl = config('services.sso.base_url', '');
            $ssoParsed = parse_url($ssoBaseUrl);
            $ssoHost = $ssoParsed['host'] ?? null;

            // Si l'URL de redirection pointe vers le domaine SSO, éviter la boucle
            if ($redirectHost && $ssoHost && $redirectHost === $ssoHost) {
                Log::debug('SSO redirect URL points to SSO domain, redirecting to dashboard', [
                    'redirect_url' => $redirectUrl,
                    'sso_host' => $ssoHost
                ]);
                return route('dashboard');
            }

            // Extraire le domaine de l'application
            $appUrl = config('app.url', '');
            $appParsed = parse_url($appUrl);
            $appHost = $appParsed['host'] ?? null;

            // Si l'URL de redirection ne pointe pas vers l'application, retourner le dashboard
            if ($redirectHost && $appHost && $redirectHost !== $appHost) {
                Log::debug('SSO redirect URL points to external domain, redirecting to dashboard', [
                    'redirect_url' => $redirectUrl,
                    'app_host' => $appHost
                ]);
                return route('dashboard');
            }

            // Nettoyer l'URL pour enlever les paramètres redirect internes
            $cleanUrl = $this->cleanCallbackUrl($redirectUrl);

            return $cleanUrl;

        } catch (\Throwable $e) {
            Log::debug('SSO redirect URL validation error', [
                'error' => $e->getMessage(),
                'redirect_url' => $redirectUrl
            ]);
            // En cas d'erreur, retourner le dashboard
            return route('dashboard');
        }
    }

    /**
     * Nettoyer l'URL de callback pour enlever les paramètres redirect internes
     * 
     * @param string $url
     * @return string
     */
    protected function cleanCallbackUrl(string $url): string
    {
        try {
            $parsed = parse_url($url);
            
            if (!$parsed) {
                return $url;
            }

            // Si pas de query string, retourner l'URL telle quelle
            if (!isset($parsed['query'])) {
                return $url;
            }

            // Parser les paramètres de requête
            parse_str($parsed['query'], $params);

            // Extraire le domaine SSO
            $ssoBaseUrl = config('services.sso.base_url', '');
            $ssoParsed = parse_url($ssoBaseUrl);
            $ssoHost = $ssoParsed['host'] ?? null;

            // Si le paramètre redirect pointe vers le domaine SSO, le supprimer
            if (isset($params['redirect']) && $ssoHost) {
                $redirectParsed = parse_url($params['redirect']);
                $redirectHost = $redirectParsed['host'] ?? null;
                
                if ($redirectHost === $ssoHost) {
                    unset($params['redirect']);
                }
            }

            // Reconstruire l'URL
            $cleanQuery = http_build_query($params);
            $cleanUrl = $parsed['scheme'] . '://' . $parsed['host'];
            
            if (isset($parsed['port'])) {
                $cleanUrl .= ':' . $parsed['port'];
            }
            
            if (isset($parsed['path'])) {
                $cleanUrl .= $parsed['path'];
            }
            
            if ($cleanQuery) {
                $cleanUrl .= '?' . $cleanQuery;
            }
            
            if (isset($parsed['fragment'])) {
                $cleanUrl .= '#' . $parsed['fragment'];
            }

            return $cleanUrl;

        } catch (\Throwable $e) {
            Log::debug('SSO callback URL cleaning error', [
                'error' => $e->getMessage(),
                'url' => $url
            ]);
            // En cas d'erreur, retourner l'URL originale
            return $url;
        }
    }

    /**
     * Rediriger vers la page de connexion SSO
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirectToSSO(Request $request)
    {
        $redirectUrl = $request->query('redirect') 
            ?: $request->header('Referer') 
            ?: url()->previous() 
            ?: route('dashboard');

        // Valider l'URL de redirection
        $validatedRedirect = $this->validateRedirectUrl($redirectUrl);

        // Construire l'URL de callback complète
        $callbackUrl = route('sso.callback', [
            'redirect' => $validatedRedirect
        ]);

        $ssoLoginUrl = $this->ssoService->getLoginUrl($callbackUrl);

        return redirect($ssoLoginUrl);
    }
}

