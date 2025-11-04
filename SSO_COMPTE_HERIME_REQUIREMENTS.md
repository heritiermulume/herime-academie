# 🔧 Configuration Requise sur compte.herime.com

## ⚠️ Problème Identifié

Quand un utilisateur déjà connecté sur `compte.herime.com` accède à `academie.herime.com/login`, il est redirigé vers `compte.herime.com/dashboard` au lieu de recevoir un token SSO pour se connecter automatiquement sur `academie.herime.com`.

## ✅ Solution : Endpoint `/sso/authorize`

### Option 1 : Créer l'endpoint `/sso/authorize` (Recommandé)

Sur `compte.herime.com`, créez un endpoint `/sso/authorize` qui :

1. **Vérifie si l'utilisateur est connecté**
   - Si oui → Génère un token JWT et redirige directement
   - Si non → Redirige vers `/login` avec le paramètre `redirect`

2. **Génère toujours un token** même si l'utilisateur est déjà connecté

### Exemple d'implémentation sur compte.herime.com

```php
// Route
Route::get('/sso/authorize', [SSOController::class, 'authorize']);

// Contrôleur
public function authorize(Request $request)
{
    $redirectUrl = $request->query('redirect');
    $forceToken = $request->query('force_token', false);
    
    // Si l'utilisateur n'est pas connecté
    if (!Auth::check()) {
        // Rediriger vers la page de login avec le redirect
        return redirect()->route('login', ['redirect' => $redirectUrl]);
    }
    
    // L'utilisateur est connecté, générer un token SSO
    $user = Auth::user();
    $token = $this->generateSSOToken($user);
    
    // Rediriger vers l'URL de callback avec le token
    $callbackUrl = $redirectUrl . (strpos($redirectUrl, '?') !== false ? '&' : '?') . 'token=' . $token;
    
    return redirect($callbackUrl);
}

private function generateSSOToken($user)
{
    // Générer un JWT avec les données utilisateur
    // Utiliser la même clé secrète que SSO_SECRET
    return JWT::encode([
        'user_id' => $user->id,
        'email' => $user->email,
        'name' => $user->name,
        'role' => $user->role,
        'is_verified' => $user->is_verified,
        'is_active' => $user->is_active,
        'exp' => time() + 300, // 5 minutes
    ], config('services.sso.secret'), 'HS256');
}
```

### Option 2 : Modifier l'endpoint `/login` existant

Si vous préférez modifier l'endpoint `/login` existant :

```php
public function login(Request $request)
{
    $redirectUrl = $request->query('redirect');
    $forceToken = $request->query('force_token', false);
    
    // Si l'utilisateur est déjà connecté ET force_token est présent
    if (Auth::check() && $forceToken) {
        // Générer un token SSO et rediriger
        $user = Auth::user();
        $token = $this->generateSSOToken($user);
        $callbackUrl = $redirectUrl . '?token=' . $token;
        return redirect($callbackUrl);
    }
    
    // Si l'utilisateur est déjà connecté SANS force_token
    if (Auth::check() && !$forceToken) {
        return redirect()->route('dashboard');
    }
    
    // Sinon, afficher la page de login normale
    return view('auth.login', ['redirect' => $redirectUrl]);
}
```

## 🔄 Flux Complet

### Cas 1 : Utilisateur non connecté
1. `academie.herime.com/login` → Redirige vers `compte.herime.com/sso/authorize?redirect=...`
2. `compte.herime.com/sso/authorize` → Utilisateur non connecté → Redirige vers `/login?redirect=...`
3. Utilisateur se connecte → Génère token → Redirige vers `academie.herime.com/sso/callback?token=...`

### Cas 2 : Utilisateur déjà connecté
1. `academie.herime.com/login` → Redirige vers `compte.herime.com/sso/authorize?redirect=...&force_token=1`
2. `compte.herime.com/sso/authorize` → Utilisateur connecté → Génère token immédiatement → Redirige vers `academie.herime.com/sso/callback?token=...`
3. `academie.herime.com` valide le token → Connecte l'utilisateur automatiquement

## 📝 URL Générées

Le code actuel génère maintenant :
- `https://compte.herime.com/sso/authorize?redirect=https://academie.herime.com/sso/callback?redirect=...&force_token=1`

## ✅ Checklist pour compte.herime.com

- [ ] Créer l'endpoint `/sso/authorize` OU modifier `/login` pour gérer `force_token`
- [ ] Endpoint vérifie si l'utilisateur est connecté
- [ ] Si connecté → Génère token SSO immédiatement
- [ ] Si non connecté → Redirige vers `/login`
- [ ] Token JWT contient : user_id, email, name, role, is_verified, is_active
- [ ] Token signé avec `SSO_SECRET` (identique sur les deux sites)
- [ ] Token expire après 5 minutes (recommandé)

## 🧪 Test

1. Connectez-vous sur `compte.herime.com`
2. Visitez `academie.herime.com/login`
3. Vous devriez être automatiquement connecté sur `academie.herime.com` sans avoir à vous reconnecter

