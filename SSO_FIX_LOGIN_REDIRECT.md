# 🔧 Fix: Redirection vers compte.herime.com/dashboard

## Problème

Quand un utilisateur déjà connecté sur `compte.herime.com` accède à `academie.herime.com/login`, il est redirigé vers `compte.herime.com/dashboard` au lieu de recevoir un token SSO.

## Solution Immédiate

Le code génère maintenant l'URL : `https://compte.herime.com/login?redirect=...&force_token=1`

**Vous devez modifier l'endpoint `/login` sur `compte.herime.com` pour gérer le paramètre `force_token`.**

## Code à Ajouter sur compte.herime.com

Dans votre contrôleur de login sur `compte.herime.com`, ajoutez cette logique :

```php
// Dans votre méthode login() ou create()
public function login(Request $request)
{
    $redirectUrl = $request->query('redirect');
    $forceToken = $request->boolean('force_token', false);
    
    // Si l'utilisateur est déjà connecté ET force_token est présent
    if (Auth::check() && $forceToken) {
        // Générer un token SSO et rediriger immédiatement
        $user = Auth::user();
        $token = $this->generateSSOToken($user);
        
        // Construire l'URL de callback avec le token
        $callbackUrl = $redirectUrl . (strpos($redirectUrl, '?') !== false ? '&' : '?') . 'token=' . $token;
        
        return redirect($callbackUrl);
    }
    
    // Si l'utilisateur est déjà connecté SANS force_token
    if (Auth::check() && !$forceToken) {
        // Comportement normal : rediriger vers le dashboard
        return redirect()->route('dashboard');
    }
    
    // Si l'utilisateur n'est pas connecté, afficher la page de login
    return view('auth.login', [
        'redirect' => $redirectUrl
    ]);
}

// Méthode pour générer le token SSO
private function generateSSOToken($user)
{
    // Utiliser la même clé secrète que SSO_SECRET
    $secret = config('services.sso.secret');
    
    // Générer un JWT avec les données utilisateur
    $payload = [
        'user_id' => $user->id,
        'email' => $user->email,
        'name' => $user->name,
        'role' => $user->role ?? 'student',
        'is_verified' => $user->is_verified ?? false,
        'is_active' => $user->is_active ?? true,
        'iat' => time(),
        'exp' => time() + 300, // 5 minutes
    ];
    
    // Utiliser votre bibliothèque JWT (ex: firebase/php-jwt)
    return JWT::encode($payload, $secret, 'HS256');
}
```

## Flux Complet

### Cas 1 : Utilisateur non connecté
1. `academie.herime.com/login` → `compte.herime.com/login?redirect=...&force_token=1`
2. Affiche la page de login
3. Après connexion → Génère token → `academie.herime.com/sso/callback?token=...`

### Cas 2 : Utilisateur déjà connecté
1. `academie.herime.com/login` → `compte.herime.com/login?redirect=...&force_token=1`
2. Détecte `force_token=1` et utilisateur connecté
3. Génère token immédiatement → `academie.herime.com/sso/callback?token=...`
4. Connexion automatique sur academie.herime.com

## Installation de la Bibliothèque JWT (si nécessaire)

Si vous n'avez pas encore de bibliothèque JWT :

```bash
composer require firebase/php-jwt
```

Puis dans votre contrôleur :

```php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
```

## Test

1. Connectez-vous sur `compte.herime.com`
2. Visitez `academie.herime.com/login`
3. Vous devriez être automatiquement connecté sur `academie.herime.com` sans avoir à vous reconnecter

## Vérification

Vérifiez que l'URL générée contient bien `force_token=1` :

```
https://compte.herime.com/login?redirect=https://academie.herime.com/sso/callback?redirect=...&force_token=1
```

