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

            // Rediriger vers la page demandée ou le dashboard
            return redirect()->intended($redirect);

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

        // Chercher l'utilisateur par email
        $user = User::where('email', $email)->first();

        if ($user) {
            // Mettre à jour les informations utilisateur depuis SSO
            $user->update([
                'name' => $userData['name'] ?? $user->name,
                'email' => $email,
                'is_verified' => $userData['is_verified'] ?? $user->is_verified,
                'is_active' => $userData['is_active'] ?? true,
                'last_login_at' => now(),
            ]);

            // Mettre à jour le rôle si fourni (optionnel, pour éviter les changements non désirés)
            if (isset($userData['role'])) {
                $role = $userData['role'];
                // Mapper super_user vers admin
                if ($role === 'super_user') {
                    $role = 'admin';
                }
                // S'assurer que le rôle est valide
                if (in_array($role, ['student', 'instructor', 'admin', 'affiliate'])) {
                    $user->role = $role;
                    $user->save();
                }
            }

            return $user;
        }

        // Normaliser le rôle (mapper super_user vers admin)
        $role = $userData['role'] ?? 'student';
        $validRoles = ['student', 'instructor', 'admin', 'affiliate'];
        
        // Mapper super_user vers admin
        if ($role === 'super_user') {
            $role = 'admin';
        }
        
        // S'assurer que le rôle est valide
        if (!in_array($role, $validRoles)) {
            $role = 'student';
        }
        
        // Créer un nouvel utilisateur
        $user = User::create([
            'name' => $userData['name'] ?? 'Utilisateur',
            'email' => $email,
            'password' => Hash::make(Str::random(32)), // Mot de passe aléatoire (non utilisé avec SSO)
            'role' => $role,
            'is_verified' => $userData['is_verified'] ?? false,
            'is_active' => $userData['is_active'] ?? true,
            'last_login_at' => now(),
        ]);

        Log::info('SSO user created', [
            'user_id' => $user->id,
            'email' => $user->email
        ]);

        return $user;
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

        // Construire l'URL de callback complète
        $callbackUrl = route('sso.callback', [
            'redirect' => $redirectUrl
        ]);

        $ssoLoginUrl = $this->ssoService->getLoginUrl($callbackUrl);

        return redirect($ssoLoginUrl);
    }
}

