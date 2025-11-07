# Guide d'Utilisation - Validation SSO Avant Actions Importantes

## 📋 Vue d'ensemble

Ce système permet de valider le token SSO **avant les actions importantes** (POST, PUT, PATCH, DELETE) pour détecter immédiatement si l'utilisateur a été déconnecté sur `compte.herime.com`.

## ✅ Avantages

- ✅ **Charge serveur minimale** : Pas de requêtes inutiles
- ✅ **Détection immédiate** : La déconnexion est détectée avant chaque action
- ✅ **Meilleure expérience utilisateur** : Pas de latence inutile
- ✅ **Sécurité renforcée** : Validation systématique avant les actions critiques
- ✅ **Simple à implémenter** : Deux méthodes d'utilisation disponibles

## 🔧 Méthodes d'Utilisation

### Méthode 1 : Middleware (Recommandé pour les routes)

Appliquez le middleware `sso.validate` aux routes qui nécessitent une validation SSO.

#### Exemple dans `routes/web.php`

```php
// Validation SSO pour toutes les routes de modification
Route::middleware(['auth', 'sso.validate'])->group(function () {
    Route::post('/orders', [OrderController::class, 'store']);
    Route::put('/orders/{order}', [OrderController::class, 'update']);
    Route::delete('/orders/{order}', [OrderController::class, 'destroy']);
    
    Route::post('/courses/{course}/enroll', [CourseController::class, 'enroll']);
    Route::post('/cart/add', [CartController::class, 'add']);
    Route::post('/cart/checkout', [CartController::class, 'checkout']);
});

// Validation SSO pour les actions administratives
Route::prefix('admin')->middleware(['auth', 'role:admin', 'sso.validate'])->group(function () {
    Route::post('/courses', [AdminController::class, 'storeCourse']);
    Route::put('/courses/{course}', [AdminController::class, 'updateCourse']);
    Route::delete('/courses/{course}', [AdminController::class, 'deleteCourse']);
});
```

#### Comportement du Middleware

- ✅ Valide automatiquement pour POST, PUT, PATCH, DELETE
- ✅ Ignore GET (lecture seule)
- ✅ Ne valide que si SSO est activé
- ✅ Déconnecte et redirige vers SSO si le token est invalide
- ✅ Ne casse pas le code existant (optionnel)

### Méthode 2 : Trait dans les Contrôleurs (Recommandé pour un contrôle fin)

Utilisez le trait `ValidatesSSOToken` dans vos contrôleurs pour valider manuellement avant des actions spécifiques.

#### Exemple d'utilisation

```php
<?php

namespace App\Http\Controllers;

use App\Traits\ValidatesSSOToken;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    use ValidatesSSOToken;

    /**
     * Créer une nouvelle commande
     */
    public function store(Request $request)
    {
        // Valider le token SSO avant l'action
        if (!$this->validateSSOTokenBeforeAction()) {
            return redirect()->back()
                ->with('error', 'Votre session a expiré. Veuillez vous reconnecter.');
        }

        // Votre code de création de commande ici...
        $order = Order::create($request->all());
        
        return redirect()->route('orders.show', $order)
            ->with('success', 'Commande créée avec succès');
    }

    /**
     * Annuler une commande
     */
    public function cancel(Request $request, Order $order)
    {
        // Valider avec un callback personnalisé
        if (!$this->validateSSOTokenBeforeAction(function() {
            // Action personnalisée si le token est invalide
            Log::warning('Tentative d\'annulation avec token SSO invalide');
        })) {
            return response()->json([
                'error' => 'Votre session a expiré'
            ], 401);
        }

        $order->update(['status' => 'cancelled']);
        
        return response()->json([
            'message' => 'Commande annulée avec succès'
        ]);
    }
}
```

## 📝 Exemples par Type d'Action

## 🔌 Tests API & erreurs 401 sur /api/logout

### Tests manuels via Postman ou un outil externe

- Ajouter systématiquement l’en-tête `Authorization: Bearer <token>` pour toutes les routes protégées (dont `/api/logout`).
- Sans cet en-tête, le backend répond `401 Unauthorized` (comportement attendu).

### À propos des 401 en cascade sur `/api/logout`

- Côté frontend, Axios relance la requête et déclenche la déconnexion forcée après la première réponse `200`.
- Les requêtes suivantes utilisent un token déjà révoqué : elles renvoient logiquement `401` et sont ignorées par l’intercepteur (`resources/js/bootstrap.js`).
- Ces 401 supplémentaires peuvent donc être ignorées dans les logs / la console : ils indiquent simplement que la session a bien été terminée.

### Vérification côté `compte.herime.com`

- L’endpoint `https://compte.herime.com/api/validate-token` accepte désormais `POST` **et** `GET`.
- En `POST`, envoyer `{"token": "<token>"}` dans le corps + l’en-tête `Authorization: Bearer {SSO_SECRET}`.
- Si l’API répond `200` avec `{"valid": true, "user": { ... }}`, le token est toujours valide.
- Si elle répond `{"valid": false}`, le token est expiré ou révoqué.
- Pour les clients encore en `GET`, la réponse sera également `{"valid": false}` au lieu d’un `405` (évite les boucles de redirection).

### Actions Critiques (Toujours valider)

#### 1. Création de Données (POST)

```php
public function store(Request $request)
{
    if (!$this->validateSSOTokenBeforeAction()) {
        return redirect()->back()->with('error', 'Session expirée');
    }
    
    // Créer la ressource...
}
```

#### 2. Modification de Données (PUT/PATCH)

```php
public function update(Request $request, $id)
{
    if (!$this->validateSSOTokenBeforeAction()) {
        return redirect()->back()->with('error', 'Session expirée');
    }
    
    // Modifier la ressource...
}
```

#### 3. Suppression de Données (DELETE)

```php
public function destroy($id)
{
    if (!$this->validateSSOTokenBeforeAction()) {
        return response()->json(['error' => 'Session expirée'], 401);
    }
    
    // Supprimer la ressource...
}
```

#### 4. Actions de Paiement

```php
public function processPayment(Request $request)
{
    if (!$this->validateSSOTokenBeforeAction()) {
        return redirect()->route('cart.index')
            ->with('error', 'Votre session a expiré. Veuillez vous reconnecter.');
    }
    
    // Traiter le paiement...
}
```

#### 5. Actions Administratives

```php
public function approveInstructorApplication($id)
{
    if (!$this->validateSSOTokenBeforeAction()) {
        return redirect()->route('admin.instructor-applications')
            ->with('error', 'Session expirée');
    }
    
    // Approuver la candidature...
}
```

## 🎯 Exemples Concrets pour Herime Académie

### Exemple 1 : Inscription à un Cours

```php
// app/Http/Controllers/CourseController.php
use App\Traits\ValidatesSSOToken;

class CourseController extends Controller
{
    use ValidatesSSOToken;

    public function enroll(Request $request, Course $course)
    {
        // Valider le token SSO avant l'inscription
        if (!$this->validateSSOTokenBeforeAction()) {
            return redirect()->route('courses.show', $course)
                ->with('error', 'Votre session a expiré. Veuillez vous reconnecter pour vous inscrire.');
        }

        // Vérifier si l'utilisateur est déjà inscrit
        if ($request->user()->enrollments()->where('course_id', $course->id)->exists()) {
            return redirect()->route('courses.show', $course)
                ->with('info', 'Vous êtes déjà inscrit à ce cours.');
        }

        // Créer l'inscription
        $enrollment = Enrollment::create([
            'user_id' => $request->user()->id,
            'course_id' => $course->id,
        ]);

        return redirect()->route('learning.course', $course)
            ->with('success', 'Inscription réussie !');
    }
}
```

### Exemple 2 : Ajout au Panier

```php
// app/Http/Controllers/CartController.php
use App\Traits\ValidatesSSOToken;

class CartController extends Controller
{
    use ValidatesSSOToken;

    public function add(Request $request, Course $course)
    {
        // Valider le token SSO avant d'ajouter au panier
        if (!$this->validateSSOTokenBeforeAction()) {
            return response()->json([
                'error' => 'Votre session a expiré'
            ], 401);
        }

        // Ajouter au panier...
        $cart = session()->get('cart', []);
        $cart[$course->id] = [
            'course_id' => $course->id,
            'title' => $course->title,
            'price' => $course->price,
        ];
        session()->put('cart', $cart);

        return response()->json([
            'success' => true,
            'message' => 'Cours ajouté au panier'
        ]);
    }
}
```

### Exemple 3 : Passage de Commande

```php
// app/Http/Controllers/OrderController.php
use App\Traits\ValidatesSSOToken;

class OrderController extends Controller
{
    use ValidatesSSOToken;

    public function store(Request $request)
    {
        // Valider le token SSO avant le passage de commande
        if (!$this->validateSSOTokenBeforeAction()) {
            return redirect()->route('cart.checkout')
                ->with('error', 'Votre session a expiré. Veuillez vous reconnecter.');
        }

        $request->validate([
            'payment_method' => 'required|string',
            // ... autres règles
        ]);

        // Créer la commande...
        $order = Order::create([
            'user_id' => $request->user()->id,
            'total' => $request->input('total'),
            'status' => 'pending',
        ]);

        return redirect()->route('orders.show', $order)
            ->with('success', 'Commande créée avec succès');
    }
}
```

## ⚙️ Configuration

### Activer/Désactiver la Validation SSO

La validation SSO est automatiquement désactivée si SSO n'est pas activé dans la configuration :

```php
// config/services.php
'sso' => [
    'enabled' => env('SSO_ENABLED', true), // Doit être true
    // ...
],
```

### Stockage du Token SSO

Le token SSO est stocké dans la session lors du callback SSO. Si vous avez besoin de le stocker ailleurs, modifiez la méthode `getSSOTokenForUser()` dans le trait.

## 🔒 Sécurité

### Bonnes Pratiques

1. **Toujours valider côté serveur** : La validation côté client est optionnelle, la validation serveur est obligatoire
2. **HTTPS obligatoire** : Toutes les communications doivent être chiffrées
3. **Ne pas exposer le token** : Le token n'est jamais exposé dans les logs ou les réponses
4. **Gérer les erreurs** : Messages d'erreur génériques pour ne pas exposer d'informations sensibles

### Validation Côté Serveur

Le système valide toujours le token côté serveur via l'API SSO (`/api/sso/check-token`). Même si vous validez côté client, la validation serveur est obligatoire.

## 📊 Comparaison des Méthodes

| Aspect | Middleware | Trait |
|--------|-----------|-------|
| **Facilité d'utilisation** | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐ |
| **Contrôle fin** | ⭐⭐⭐ | ⭐⭐⭐⭐⭐ |
| **Réutilisabilité** | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐ |
| **Performance** | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ |
| **Recommandation** | Routes groupées | Actions spécifiques |

## 🎯 Recommandations

### Utilisez le Middleware pour :
- ✅ Routes groupées (ex: toutes les routes `/admin/*`)
- ✅ Routes API avec validation systématique
- ✅ Routes de modification en masse

### Utilisez le Trait pour :
- ✅ Actions spécifiques nécessitant un contrôle fin
- ✅ Actions avec logique de validation personnalisée
- ✅ Actions nécessitant des callbacks personnalisés

## 🚀 Migration Progressive

Vous pouvez migrer progressivement :

1. **Phase 1** : Ajouter le middleware aux routes critiques (paiements, commandes)
2. **Phase 2** : Ajouter le trait aux contrôleurs avec actions spécifiques
3. **Phase 3** : Étendre à toutes les routes de modification

Le code existant continue de fonctionner normalement, la validation SSO est **optionnelle** et **non-intrusive**.

## 📝 Notes Importantes

- ⚠️ Le middleware ne valide que pour POST, PUT, PATCH, DELETE (pas GET)
- ⚠️ Si SSO est désactivé, la validation est automatiquement ignorée
- ⚠️ Si l'utilisateur n'a pas de token SSO (connexion locale), la validation passe
- ✅ Le code existant continue de fonctionner sans modification

## 🔧 Dépannage

### Le middleware ne fonctionne pas

Vérifiez que :
1. Le middleware est enregistré dans `bootstrap/app.php`
2. SSO est activé dans `config/services.php`
3. Le token est stocké dans la session (vérifiez `SSOController::callback`)

### La validation échoue toujours

Vérifiez que :
1. L'endpoint `/api/sso/check-token` existe sur `compte.herime.com`
2. Les credentials SSO sont correctement configurés
3. Le token est bien stocké dans la session

### Logs de débogage

Les logs sont disponibles dans `storage/logs/laravel.log` :
- `SSO token validation failed before important action` : Token invalide
- `SSO check-token exception` : Erreur lors de la validation

