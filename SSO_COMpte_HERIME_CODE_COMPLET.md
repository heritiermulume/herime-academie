# 🔧 Code Complet à Ajouter sur compte.herime.com

## ⚠️ Problème Actuel

Après connexion sur `compte.herime.com`, l'utilisateur est redirigé vers `/dashboard` au lieu de recevoir un token SSO et être redirigé vers `academie.herime.com`.

## ✅ Solution : Modifier le Contrôleur de Login

### 1. Installer la Bibliothèque JWT (si nécessaire)

```bash
composer require firebase/php-jwt
```

### 2. Modifier le Contrôleur de Login

Dans votre contrôleur `AuthenticatedSessionController` ou `LoginController` sur `compte.herime.com`, modifiez la méthode qui gère la connexion :

```php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthenticatedSessionController extends Controller
{
    /**
     * Afficher la page de login
     */
    public function create(Request $request)
    {
        $redirectUrl = $request->query('redirect');
        $forceToken = $request->boolean('force_token', false);
        
        // Si l'utilisateur est déjà connecté ET force_token est présent
        if (Auth::check() && $forceToken) {
            // Générer un token SSO et rediriger immédiatement
            $user = Auth::user();
            $token = $this->generateSSOToken($user);
            
            // Construire l'URL de callback avec le token
            $callbackUrl = $redirectUrl . (strpos($redirectUrl, '?') !== false ? '&' : '?') . 'token=' . urlencode($token);
            
            return redirect($callbackUrl);
        }
        
        // Si l'utilisateur est déjà connecté SANS force_token
        if (Auth::check() && !$forceToken) {
            // Comportement normal : rediriger vers le dashboard
            return redirect()->route('dashboard');
        }
        
        // Si l'utilisateur n'est pas connecté, afficher la page de login
        return view('auth.login', [
            'redirect' => $redirectUrl,
            'force_token' => $forceToken
        ]);
    }
    
    /**
     * Traiter la connexion
     */
    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);
        
        $redirectUrl = $request->query('redirect') ?? $request->input('redirect');
        $forceToken = $request->boolean('force_token', false) || $request->input('force_token') == '1';
        
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            
            // Si force_token est présent, générer un token SSO et rediriger
            if ($forceToken && $redirectUrl) {
                $user = Auth::user();
                $token = $this->generateSSOToken($user);
                
                // Construire l'URL de callback avec le token
                $callbackUrl = $redirectUrl . (strpos($redirectUrl, '?') !== false ? '&' : '?') . 'token=' . urlencode($token);
                
                return redirect($callbackUrl);
            }
            
            // Sinon, redirection normale
            return redirect()->intended(route('dashboard'));
        }
        
        return back()->withErrors([
            'email' => 'Les identifiants fournis ne correspondent à aucun compte.',
        ])->onlyInput('email');
    }
    
    /**
     * Générer un token SSO JWT
     */
    private function generateSSOToken($user)
    {
        // Utiliser la même clé secrète que SSO_SECRET
        $secret = config('services.sso.secret');
        
        if (!$secret) {
            \Log::error('SSO_SECRET not configured on compte.herime.com');
            throw new \Exception('SSO_SECRET not configured');
        }
        
        // Payload JWT
        $payload = [
            'user_id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
            'role' => $user->role ?? 'student',
            'is_verified' => $user->is_verified ?? false,
            'is_active' => $user->is_active ?? true,
            'iat' => time(),
            'exp' => time() + 300, // 5 minutes d'expiration
        ];
        
        // Générer le token JWT
        return JWT::encode($payload, $secret, 'HS256');
    }
}
```

### 3. Modifier le Formulaire de Login (si nécessaire)

Si vous devez passer `force_token` dans le formulaire, ajoutez un champ caché :

```blade
{{-- Dans votre vue auth/login.blade.php --}}
@if(request()->has('force_token'))
    <input type="hidden" name="force_token" value="1">
@endif

@if(request()->has('redirect'))
    <input type="hidden" name="redirect" value="{{ request()->query('redirect') }}">
@endif
```

### 4. Vérifier la Configuration

Dans votre fichier `.env` sur `compte.herime.com`, assurez-vous d'avoir :

```env
SSO_SECRET=1d69dac265aab9b5633e96af6f2e4f27f082824f1512b2f7a047bf8f4365e3db
```

Et dans `config/services.php` :

```php
'sso' => [
    'secret' => env('SSO_SECRET'),
],
```

## 🔄 Flux Complet Après Modification

### Cas 1 : Utilisateur non connecté
1. `academie.herime.com/login` → `compte.herime.com/login?redirect=...&force_token=1`
2. Utilisateur se connecte
3. Après connexion → Génère token → `academie.herime.com/sso/callback?token=...`
4. Connexion automatique sur `academie.herime.com`

### Cas 2 : Utilisateur déjà connecté
1. `academie.herime.com/login` → `compte.herime.com/login?redirect=...&force_token=1`
2. Détecte utilisateur connecté + `force_token=1`
3. Génère token immédiatement → `academie.herime.com/sso/callback?token=...`
4. Connexion automatique sur `academie.herime.com`

## ✅ Checklist

- [ ] Bibliothèque JWT installée (`composer require firebase/php-jwt`)
- [ ] Méthode `create()` modifiée pour gérer `force_token`
- [ ] Méthode `store()` modifiée pour générer token après connexion
- [ ] Méthode `generateSSOToken()` ajoutée
- [ ] `SSO_SECRET` configuré dans `.env`
- [ ] Formulaire de login passe `force_token` et `redirect` (si nécessaire)
- [ ] Test : Connexion → Redirection vers academie.herime.com

## 🧪 Test

1. Connectez-vous sur `compte.herime.com` (ou déconnectez-vous)
2. Visitez `academie.herime.com/login`
3. Si déjà connecté : Connexion automatique sur `academie.herime.com`
4. Si non connecté : Connexion sur `compte.herime.com` → Connexion automatique sur `academie.herime.com`

## 🐛 Dépannage

### Erreur "Class 'Firebase\JWT\JWT' not found"
→ Exécutez : `composer require firebase/php-jwt`

### Erreur "SSO_SECRET not configured"
→ Vérifiez que `SSO_SECRET` est dans le `.env` de `compte.herime.com`

### Redirection toujours vers dashboard
→ Vérifiez que `force_token` est bien passé dans la requête et traité dans `store()`

### Token invalide
→ Vérifiez que `SSO_SECRET` est identique sur les deux sites


