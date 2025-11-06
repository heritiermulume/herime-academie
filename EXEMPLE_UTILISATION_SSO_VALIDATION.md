# Exemple d'Activation de la Validation SSO

## ✅ État Actuel

L'implémentation est **complète et fonctionnelle**, mais **non activée par défaut** pour ne pas casser le code existant.

## 🚀 Comment Activer

### Option 1 : Activer sur des Routes Spécifiques (Recommandé)

Ajoutez le middleware `sso.validate` aux routes importantes dans `routes/web.php` :

```php
// Exemple : Validation SSO pour les commandes
Route::middleware(['auth', 'sso.validate'])->group(function () {
    Route::post('/orders', [OrderController::class, 'store']);
    Route::put('/orders/{order}', [OrderController::class, 'update']);
    Route::delete('/orders/{order}', [OrderController::class, 'destroy']);
});

// Exemple : Validation SSO pour le panier
Route::middleware(['auth', 'sso.validate'])->group(function () {
    Route::post('/cart/add', [CartController::class, 'add']);
    Route::post('/cart/checkout', [CartController::class, 'checkout']);
});

// Exemple : Validation SSO pour les actions administratives
Route::prefix('admin')->middleware(['auth', 'role:admin', 'sso.validate'])->group(function () {
    Route::post('/courses', [AdminController::class, 'storeCourse']);
    Route::put('/courses/{course}', [AdminController::class, 'updateCourse']);
    Route::delete('/courses/{course}', [AdminController::class, 'deleteCourse']);
});
```

### Option 2 : Utiliser le Trait dans un Contrôleur

```php
<?php

namespace App\Http\Controllers;

use App\Traits\ValidatesSSOToken;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    use ValidatesSSOToken;

    public function store(Request $request)
    {
        // Valider le token SSO avant l'action
        if (!$this->validateSSOTokenBeforeAction()) {
            return redirect()->back()
                ->with('error', 'Votre session a expiré. Veuillez vous reconnecter.');
        }

        // Votre code de création de commande...
    }
}
```

## ⚠️ Prérequis

Pour que la validation fonctionne, il faut :

1. **L'endpoint `/api/sso/check-token` doit exister sur `compte.herime.com`**
   - Format attendu : `POST https://compte.herime.com/api/sso/check-token`
   - Body : `{"token": "VOTRE_TOKEN"}`
   - Réponse : `{"success": true, "valid": true}` ou `{"success": false, "valid": false}`

2. **Le token SSO doit être stocké dans la session**
   - ✅ Déjà implémenté dans `SSOController::callback()`
   - Le token est stocké lors de la connexion SSO

3. **SSO doit être activé**
   - Vérifiez `config/services.php` : `SSO_ENABLED=true`

## 🧪 Test de Fonctionnement

### Test 1 : Vérifier que le middleware est enregistré

```bash
php artisan route:list | grep sso
```

### Test 2 : Vérifier que le token est stocké

Après connexion SSO, vérifiez dans la session :
```php
// Dans tinker
php artisan tinker
>>> session('sso_token')
```

### Test 3 : Tester la validation manuellement

```php
// Dans tinker
php artisan tinker
>>> $ssoService = app(\App\Services\SSOService::class);
>>> $token = session('sso_token');
>>> $ssoService->checkToken($token);
```

## 📝 Exemple Complet d'Activation

Voici un exemple pour activer la validation sur les routes critiques :

```php
// routes/web.php

// Routes de commande avec validation SSO
Route::middleware(['auth', 'sso.validate'])->group(function () {
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
    Route::put('/orders/{order}', [OrderController::class, 'update'])->name('orders.update');
    Route::delete('/orders/{order}', [OrderController::class, 'destroy'])->name('orders.destroy');
});

// Routes de panier avec validation SSO
Route::middleware(['auth', 'sso.validate'])->group(function () {
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::post('/cart/checkout', [CartController::class, 'checkout'])->name('cart.checkout');
});

// Routes d'inscription aux cours avec validation SSO
Route::middleware(['auth', 'sso.validate'])->group(function () {
    Route::post('/student/courses/{course:slug}/enroll', [StudentController::class, 'enroll'])
        ->name('student.courses.enroll');
});
```

## 🔍 Vérification

Pour vérifier que tout fonctionne :

1. **Connectez-vous via SSO**
2. **Le token doit être dans la session** (vérifiez avec `session('sso_token')`)
3. **Essayez une action protégée** (ex: ajouter au panier)
4. **Si le token est invalide**, vous serez redirigé vers le SSO

## ⚡ Note Importante

Le système fonctionne **même si l'endpoint `/api/sso/check-token` n'existe pas encore** :
- Il utilise un fallback vers la validation locale JWT
- Le code ne casse pas si l'API n'est pas disponible
- Les logs indiquent si l'API est disponible ou non

