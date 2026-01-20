<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\User;

class SSOCallbackController extends Controller
{
    public function handle(Request $request)
    {
        try {
            $token = $request->query('token');
            $finalRedirect = $request->query('redirect', url('/'));

            if (!$token) {
                Log::info('SSO callback: no token, redirecting to SSO for token generation', [
                    'final_redirect' => $finalRedirect,
                ]);
                $callback = route('sso.callback', ['redirect' => $finalRedirect]);
                $loginUrl = 'https://compte.herime.com/login?force_token=1&redirect=' . urlencode($callback);
                return redirect()->away($loginUrl);
            }

            // Valider le token côté compte.herime.com (Passport) - endpoint recommandé /api/me
            $resp = Http::acceptJson()
                ->withToken($token)
                ->get('https://compte.herime.com/api/me');

            if (!$resp->ok()) {
                // Token invalide → relancer SSO une seule fois
                Log::warning('SSO callback: token invalid, redirecting back to SSO', [
                    'status' => $resp->status(),
                    'body' => $resp->body(),
                ]);
                $callback = route('sso.callback', ['redirect' => $finalRedirect]);
                $loginUrl = 'https://compte.herime.com/login?force_token=1&redirect=' . urlencode($callback);
                return redirect()->away($loginUrl);
            }

            // Succès SSO: créer une session locale fonctionnelle
            $payload = $resp->json();
            if (!is_array($payload)) {
                Log::warning('SSO callback: unexpected /api/me payload (not JSON object)', ['payload' => $resp->body()]);
                $callback = route('sso.callback', ['redirect' => $finalRedirect]);
                $loginUrl = 'https://compte.herime.com/login?force_token=1&redirect=' . urlencode($callback);
                return redirect()->away($loginUrl);
            }

            // Le contrat attendu: { success: true, data: { user: {...} } }
            $success = (bool) data_get($payload, 'success', false);
            $remoteUser = data_get($payload, 'data.user', []);
            if (!$success || empty($remoteUser) || !is_array($remoteUser)) {
                Log::warning('SSO callback: /api/me returned invalid structure or not success', ['payload' => $payload]);
                $callback = route('sso.callback', ['redirect' => $finalRedirect]);
                $loginUrl = 'https://compte.herime.com/login?force_token=1&redirect=' . urlencode($callback);
                return redirect()->away($loginUrl);
            }

            $email = data_get($remoteUser, 'email');
            $name  = data_get($remoteUser, 'name') ?? trim((string) data_get($remoteUser, 'first_name').' '.(string) data_get($remoteUser, 'last_name'));
            
            // Récupérer le rôle depuis les données SSO (peut être dans role, privilege, ou privileges)
            $ssoRole = data_get($remoteUser, 'role') 
                ?? data_get($remoteUser, 'privilege') 
                ?? data_get($remoteUser, 'privileges')
                ?? 'customer';
            
            // Normaliser le rôle
            $role = $this->normalizeRole($ssoRole);

            if (!$email) {
                Log::warning('SSO callback: missing email in /api/me response');
                // Si les données sont insuffisantes, relancer le flux SSO
                $callback = route('sso.callback', ['redirect' => $finalRedirect]);
                $loginUrl = 'https://compte.herime.com/login?force_token=1&redirect=' . urlencode($callback);
                return redirect()->away($loginUrl);
            }

            // Récupérer le bio depuis SSO
            $bio = data_get($remoteUser, 'bio') 
                ?? data_get($remoteUser, 'biography') 
                ?? data_get($remoteUser, 'about')
                ?? null;

            // Récupérer le numéro de téléphone depuis SSO
            $phone = data_get($remoteUser, 'phone') 
                ?? data_get($remoteUser, 'telephone') 
                ?? data_get($remoteUser, 'mobile') 
                ?? data_get($remoteUser, 'phone_number')
                ?? data_get($remoteUser, 'tel')
                ?? null;
            
            // Nettoyer le numéro de téléphone si fourni (sans ajouter d'indicatif)
            if ($phone) {
                $phone = trim($phone);
                // Supprimer uniquement les espaces, tirets, points, etc. mais garder les chiffres et le +
                $phone = preg_replace('/[^0-9+]/', '', $phone);
                // Si le résultat est vide après nettoyage, mettre à null
                if (empty($phone)) {
                    $phone = null;
                }
            }

            // Récupérer la date de naissance depuis SSO
            $dateOfBirth = data_get($remoteUser, 'date_of_birth')
                ?? data_get($remoteUser, 'birthdate')
                ?? data_get($remoteUser, 'birth_date')
                ?? data_get($remoteUser, 'date_naissance')
                ?? data_get($remoteUser, 'dob')
                ?? null;

            // Normaliser la date de naissance si fournie
            if ($dateOfBirth) {
                try {
                    // Si c'est déjà une date valide, la convertir en Carbon
                    if (is_string($dateOfBirth)) {
                        $dateOfBirth = Carbon::parse($dateOfBirth)->format('Y-m-d');
                    } elseif ($dateOfBirth instanceof \DateTime) {
                        $dateOfBirth = $dateOfBirth->format('Y-m-d');
                    } else {
                        $dateOfBirth = null;
                    }
                } catch (\Exception $e) {
                    Log::warning('SSO callback: invalid date_of_birth format', [
                        'date_of_birth' => $dateOfBirth,
                        'error' => $e->getMessage()
                    ]);
                    $dateOfBirth = null;
                }
            }

            // Récupérer le sexe/genre depuis SSO
            $gender = data_get($remoteUser, 'gender')
                ?? data_get($remoteUser, 'sexe')
                ?? data_get($remoteUser, 'sex')
                ?? null;

            // Normaliser le genre si fourni
            if ($gender) {
                $gender = strtolower(trim($gender));
                // Mapper les valeurs possibles vers les valeurs attendues
                $genderMap = [
                    'm' => 'male',
                    'male' => 'male',
                    'homme' => 'male',
                    'masculin' => 'male',
                    'f' => 'female',
                    'female' => 'female',
                    'femme' => 'female',
                    'féminin' => 'female',
                    'feminin' => 'female',
                    'o' => 'other',
                    'other' => 'other',
                    'autre' => 'other',
                    'non-binaire' => 'other',
                    'nonbinaire' => 'other',
                ];
                $gender = $genderMap[$gender] ?? null;
                // Si la valeur n'est pas reconnue, mettre à null
                if (!in_array($gender, ['male', 'female', 'other'])) {
                    $gender = null;
                }
            }

            // Upsert utilisateur local avec le rôle depuis SSO
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name ?: $email,
                    'password' => bcrypt(Str::random(32)),
                    'role' => $role, // Assigner le rôle depuis SSO
                    'bio' => $bio, // Ajouter le bio depuis SSO
                    'phone' => $phone, // Ajouter le numéro de téléphone depuis SSO
                    'date_of_birth' => $dateOfBirth, // Ajouter la date de naissance depuis SSO
                    'gender' => $gender, // Ajouter le sexe depuis SSO
                ]
            );
            
            // Mettre à jour le rôle et autres informations si l'utilisateur existe déjà
            if ($user->wasRecentlyCreated === false) {
                $updateData = [
                    'name' => $name ?: $user->name,
                    'role' => $role, // Mettre à jour le rôle depuis SSO
                    'last_login_at' => now(),
                ];
                
                // Mettre à jour l'avatar si fourni
                $avatar = data_get($remoteUser, 'avatar') 
                    ?? data_get($remoteUser, 'photo') 
                    ?? data_get($remoteUser, 'picture')
                    ?? null;
                if ($avatar !== null) {
                    $updateData['avatar'] = $avatar;
                }
                
                // Mettre à jour le bio si fourni
                if ($bio !== null) {
                    $updateData['bio'] = $bio;
                }
                
                // Mettre à jour le numéro de téléphone si fourni
                if ($phone !== null) {
                    $updateData['phone'] = $phone;
                }
                
                // Mettre à jour la date de naissance si fournie
                if ($dateOfBirth !== null) {
                    $updateData['date_of_birth'] = $dateOfBirth;
                }
                
                // Mettre à jour le sexe/genre si fourni
                if ($gender !== null) {
                    $updateData['gender'] = $gender;
                }
                
                // Mettre à jour is_verified et is_active si fournis
                if (isset($remoteUser['is_verified'])) {
                    $updateData['is_verified'] = (bool) $remoteUser['is_verified'];
                }
                if (isset($remoteUser['is_active'])) {
                    $updateData['is_active'] = (bool) $remoteUser['is_active'];
                }
                
                $user->update($updateData);
            }

            // Connexion locale + sécurisation de session
            Auth::login($user, true);
            $request->session()->regenerate();
            // Conserver le token SSO pour la validation ultérieure
            try {
                $request->session()->put('sso_token', $token);
            } catch (\Throwable $e) {
                Log::debug('SSO callback: unable to store sso_token in session', ['error' => $e->getMessage()]);
            }

            Log::info('SSO callback: authenticated locally and redirecting', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
                'sso_role' => $ssoRole,
                'final_redirect' => $finalRedirect,
            ]);

            // Toujours rester sur academie.herime.com pour la redirection finale
            $finalUrl = $this->safeRedirect($finalRedirect);
            return redirect()->to($finalUrl);
        } catch (\Throwable $e) {
            Log::error('SSO callback error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return redirect()->to(url('/'));
        }
    }

    /**
     * Normaliser le rôle utilisateur depuis SSO
     * Convertit les anciens rôles (student, instructor) vers les nouveaux (customer, provider)
     * Conserve super_user comme tel (il a accès à l'admin via isAdmin())
     *
     * @param string|null $role
     * @return string
     */
    private function normalizeRole(?string $role): string
    {
        $validRoles = ['customer', 'provider', 'admin', 'affiliate', 'super_user'];
        
        // Si aucun rôle fourni, retourner customer par défaut
        if (empty($role)) {
            return 'customer';
        }
        
        // Normaliser la casse
        $role = strtolower(trim($role));
        
        // Convertir les anciens rôles vers les nouveaux rôles
        $roleMapping = [
            'student' => 'customer',
            'instructor' => 'provider',
        ];
        
        if (isset($roleMapping[$role])) {
            $oldRole = $role;
            $role = $roleMapping[$role];
            Log::info('SSO callback: Converted old role to new role', [
                'old_role' => $oldRole,
                'new_role' => $role
            ]);
        }
        
        // Conserver super_user tel quel (il aura accès à l'admin via isAdmin())
        // S'assurer que le rôle est valide
        if (!in_array($role, $validRoles)) {
            Log::warning('SSO callback: Invalid role provided, defaulting to customer', [
                'invalid_role' => $role,
                'valid_roles' => $validRoles
            ]);
            return 'customer';
        }
        
        return $role;
    }

    private function safeRedirect(string $url): string
    {
        $parsed = parse_url($url);
        if (!$parsed || empty($parsed['host'])) {
            return url('/'); // relatif
        }
        $host = strtolower(preg_replace('/^www\./', '', $parsed['host']));
        if ($host === 'academie.herime.com') {
            return $url;
        }
        return url('/'); // fallback sûr
    }
}


