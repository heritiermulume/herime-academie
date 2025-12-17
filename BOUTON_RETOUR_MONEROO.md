# 🔙 BOUTON RETOUR SUR PAGE MONEROO - ANALYSE

**Date**: {{ date('Y-m-d H:i:s') }}  
**Demande**: Ajouter un bouton "Retour au site" sur la page de paiement Moneroo  
**Statut**: ⚠️ **LIMITÉ PAR MONEROO**

---

## 🎯 DEMANDE

> "Vérifie la documentation si il y a possibilité d'ajouter le bouton retour au site sur la page de payement moneroo"

**Objectif** : Permettre à l'utilisateur de revenir au site depuis la page de checkout Moneroo

---

## 🔍 ANALYSE DE LA DOCUMENTATION MONEROO

### Paramètres Disponibles pour `/payments/initialize`

Selon la documentation officielle Moneroo ([docs.moneroo.io](https://docs.moneroo.io/fr/payments/integration-standard)), les paramètres disponibles sont :

#### Paramètres Requis
```json
{
  "amount": 1000,              // Montant (entier)
  "currency": "USD",           // Devise
  "description": "...",        // Description du paiement
  "return_url": "...",         // URL de redirection après paiement
  "customer": {                // Informations client
    "email": "...",
    "first_name": "...",
    "last_name": "..."
  }
}
```

#### Paramètres Optionnels
```json
{
  "customer": {
    "phone": "...",            // Téléphone (optionnel)
    "country": "..."           // Pays (optionnel)
  },
  "metadata": {                // Métadonnées personnalisées
    "order_id": "...",
    "user_id": "..."
  }
}
```

---

## ❌ PARAMÈTRES NON DISPONIBLES

### Ce qui N'EXISTE PAS dans l'API Moneroo

D'après mes recherches, Moneroo **ne propose PAS** les paramètres suivants :

- ❌ `cancel_url` - URL de redirection en cas d'annulation
- ❌ `back_url` - URL de retour au site
- ❌ `show_cancel_button` - Afficher un bouton d'annulation
- ❌ `cancel_redirect` - Redirection sur annulation
- ❌ `custom_buttons` - Personnalisation des boutons

**Conclusion** : Il n'y a **PAS** de paramètre API pour ajouter un bouton "Retour au site" sur la page de paiement Moneroo.

---

## 🔧 SOLUTIONS ALTERNATIVES

### Solution 1 : Utiliser le Paramètre `return_url` (Déjà Implémenté)

**Ce que nous avons déjà** :

```php
// app/Http/Controllers/MonerooController.php ligne 334
$payload = [
    'amount' => $amountInSmallestUnit,
    'currency' => $paymentCurrency,
    'description' => config('services.moneroo.company_name', 'Herime Académie') 
                   . ' - Paiement commande ' . $order->order_number,
    'return_url' => config('services.moneroo.successful_url', route('moneroo.success')) 
                  . '?payment_id=' . $paymentId,
    // ...
];
```

**Limitation** : `return_url` est utilisé **après** le paiement (succès ou échec), pas **pendant** le processus de paiement.

---

### Solution 2 : Ajouter un Lien dans la Description (Limité)

**Possible** : Ajouter une mention dans la description

```php
'description' => 'Herime Académie - Paiement commande ' . $order->order_number 
               . ' - En cas de problème, retournez sur herime-academie.com',
```

**Limitations** :
- ❌ Ce n'est pas un bouton cliquable
- ❌ L'utilisateur doit copier/coller l'URL
- ❌ Mauvaise expérience utilisateur
- ⚠️ Non recommandé

---

### Solution 3 : Contacter le Support Moneroo (Recommandé)

**Action** : Demander à Moneroo d'ajouter cette fonctionnalité

#### Comment Contacter Moneroo

**Email** : support@moneroo.io (à vérifier sur leur site)  
**Chat** : Disponible sur https://dashboard.moneroo.io  
**Documentation** : https://docs.moneroo.io

#### Message Type à Envoyer

```
Objet: Demande de fonctionnalité - Bouton de retour sur la page de paiement

Bonjour,

Je suis [Votre Nom], développeur pour Herime Académie, et nous utilisons 
l'API Moneroo pour nos paiements.

Nous aimerions ajouter un bouton "Retour au site" sur la page de checkout 
Moneroo, permettant aux utilisateurs de revenir sur notre site s'ils 
changent d'avis avant de finaliser le paiement.

Questions :
1. Est-ce que cette fonctionnalité existe déjà ?
2. Y a-t-il un paramètre API que nous aurions manqué ?
3. Si non, est-ce que cette fonctionnalité pourrait être ajoutée ?
4. Y a-t-il une solution de contournement recommandée ?

Détails de notre compte :
- Email marchand : [votre email]
- Site : herime-academie.com

Merci de votre aide,
[Votre Nom]
Herime Académie
```

---

### Solution 4 : Route d'Annulation Manuelle (Alternative)

**Idée** : Créer une route pour annuler manuellement un paiement en cours

#### Implémentation

**Route** : `/moneroo/cancel-pending` (déjà existe !)

```php
// routes/web.php ligne 831
Route::post('/cancel-latest', [MonerooController::class, 'cancelLatestPending'])
    ->name('cancel-latest');
```

**Méthode** : `MonerooController::cancelLatestPending()` (déjà existe !)

```php
// app/Http/Controllers/MonerooController.php ligne 834
public function cancelLatestPending(Request $request)
{
    if (!auth()->check()) {
        return response()->json(['success' => false, 'message' => 'Non authentifié'], 401);
    }
    
    $userId = auth()->id();
    $order = Order::where('user_id', $userId)
        ->where('status', 'pending')
        ->latest()
        ->first();
        
    if (!$order) {
        return response()->json(['success' => false, 'message' => 'Aucune commande en attente'], 404);
    }
    
    // Annuler seulement les commandes récentes (<10 min)
    if ($order->created_at->lt(now()->subMinutes(10))) {
        return response()->json(['success' => false, 'message' => 'Commande trop ancienne'], 422);
    }
    
    $order->update(['status' => 'cancelled']);
    return response()->json(['success' => true]);
}
```

**Comment l'utiliser** : Ajouter un bouton sur la page de checkout avant redirection Moneroo

---

## 💡 SOLUTION RECOMMANDÉE

### Approche Pratique

Étant donné que Moneroo ne propose pas de bouton de retour natif, voici l'approche recommandée :

#### 1. Ajouter un Bouton "Annuler" AVANT de rediriger vers Moneroo

**Page** : `resources/views/cart/checkout.blade.php`

**Ajouter** :
```html
<!-- Avant la redirection vers Moneroo -->
<div class="payment-info-box">
    <p>
        <i class="fas fa-info-circle"></i>
        Vous allez être redirigé vers la page de paiement sécurisée Moneroo.
    </p>
    <p>
        <strong>Note :</strong> Si vous changez d'avis pendant le paiement, 
        vous pouvez fermer la fenêtre et revenir ici pour annuler votre commande.
    </p>
</div>
```

#### 2. Ajouter un Bouton "Annuler la Commande" sur le Site

**Créer une page** : `/orders/{order}/cancel`

```php
// Route
Route::get('/orders/{order}/cancel', [OrderController::class, 'showCancel'])
    ->name('orders.cancel');
Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])
    ->name('orders.cancel.confirm');
```

**Méthode** :
```php
public function showCancel(Order $order)
{
    // Vérifier que la commande appartient à l'utilisateur
    if ($order->user_id !== auth()->id()) {
        abort(403);
    }
    
    // Vérifier que la commande est en attente
    if ($order->status !== 'pending') {
        return redirect()->route('orders.show', $order)
            ->with('error', 'Cette commande ne peut plus être annulée.');
    }
    
    return view('orders.cancel', compact('order'));
}

public function cancel(Order $order)
{
    // Vérifier et annuler
    if ($order->user_id !== auth()->id() || $order->status !== 'pending') {
        return redirect()->back()->with('error', 'Impossible d\'annuler cette commande.');
    }
    
    $order->update(['status' => 'cancelled']);
    
    return redirect()->route('orders.index')
        ->with('success', 'Commande annulée avec succès.');
}
```

**Vue** : `resources/views/orders/cancel.blade.php`

```blade
@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="card">
        <div class="card-body text-center">
            <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
            <h2>Annuler la Commande ?</h2>
            <p>Êtes-vous sûr de vouloir annuler la commande {{ $order->order_number }} ?</p>
            
            <form method="POST" action="{{ route('orders.cancel.confirm', $order) }}">
                @csrf
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-times me-2"></i>Oui, annuler la commande
                </button>
                <a href="{{ route('cart.checkout') }}" class="btn btn-primary">
                    <i class="fas fa-arrow-left me-2"></i>Non, retour au paiement
                </a>
            </form>
        </div>
    </div>
</div>
@endsection
```

#### 3. Afficher un Message Informatif

**Sur la page de checkout** :
```html
<div class="alert alert-info">
    <i class="fas fa-lightbulb me-2"></i>
    <strong>Conseil :</strong> Vous serez redirigé vers Moneroo pour finaliser le paiement. 
    Si vous fermez la fenêtre Moneroo, vous pourrez 
    <a href="{{ route('orders.index') }}">annuler votre commande ici</a>.
</div>
```

---

## 📊 COMPARAISON DES SOLUTIONS

| Solution | Faisabilité | UX | Effort |
|----------|-------------|-----|--------|
| **Paramètre API Moneroo** | ❌ N'existe pas | ⭐⭐⭐⭐⭐ | N/A |
| **Lien dans description** | ✅ Possible | ⭐ | ⏱️ 5 min |
| **Contacter Moneroo** | ✅ Recommandé | ⭐⭐⭐⭐⭐ | ⏱️ Variable |
| **Page d'annulation sur site** | ✅ Faisable | ⭐⭐⭐⭐ | ⏱️ 30 min |
| **Message informatif** | ✅ Facile | ⭐⭐⭐ | ⏱️ 10 min |

---

## 🚀 PLAN D'ACTION RECOMMANDÉ

### Court Terme (Aujourd'hui)

1. **Ajouter un message informatif** sur la page de checkout
   - Temps : 10 minutes
   - Informe l'utilisateur du processus
   - Lien vers annulation de commande

2. **Tester la route existante** `/moneroo/cancel-latest`
   - Temps : 5 minutes
   - Vérifier qu'elle fonctionne

### Moyen Terme (Cette Semaine)

3. **Créer la page d'annulation de commande**
   - Temps : 30 minutes
   - Permet à l'utilisateur d'annuler depuis le site
   - Meilleure UX

4. **Contacter le support Moneroo**
   - Temps : 10 minutes (rédaction email)
   - Demander s'ils peuvent ajouter cette fonctionnalité
   - Proposer un paramètre `cancel_url`

### Long Terme (Selon Réponse Moneroo)

5. **Implémenter la solution Moneroo** si disponible
   - Temps : Variable
   - Si Moneroo ajoute le paramètre
   - Meilleure intégration

---

## 📝 CODE À AJOUTER (Solution Immédiate)

### 1. Message Informatif sur Checkout

**Fichier** : `resources/views/cart/checkout.blade.php`

**Chercher** : La section avant le formulaire de paiement Moneroo

**Ajouter** :
```blade
@if($selectedMethod === 'moneroo')
<div class="alert alert-info mb-4">
    <h6 class="alert-heading">
        <i class="fas fa-info-circle me-2"></i>Paiement sécurisé via Moneroo
    </h6>
    <p class="mb-2">
        Vous allez être redirigé vers la page de paiement sécurisée Moneroo 
        pour finaliser votre transaction.
    </p>
    <p class="mb-0">
        <i class="fas fa-lightbulb me-1"></i>
        <small>
            <strong>Note :</strong> Si vous changez d'avis pendant le paiement, 
            vous pouvez fermer la fenêtre Moneroo et 
            <a href="{{ route('orders.index') }}" class="alert-link">
                annuler votre commande depuis votre espace
            </a>.
        </small>
    </p>
</div>
@endif
```

---

## ✅ RÉSUMÉ

### Ce qui EST Possible

1. ✅ Message informatif sur le site avant redirection
2. ✅ Page d'annulation de commande sur le site
3. ✅ Route existante `/moneroo/cancel-latest`
4. ✅ Lien vers commandes dans les emails

### Ce qui N'EST PAS Possible (Actuellement)

1. ❌ Bouton "Retour" directement sur la page Moneroo
2. ❌ Paramètre `cancel_url` dans l'API
3. ❌ Personnalisation des boutons Moneroo

### Action Recommandée

1. **Implémenter** le message informatif (10 min)
2. **Contacter** le support Moneroo (10 min)
3. **Créer** la page d'annulation (30 min)
4. **Attendre** la réponse de Moneroo

---

## 📞 CONTACT MONEROO

**Support** : Via dashboard Moneroo (https://dashboard.moneroo.io)  
**Documentation** : https://docs.moneroo.io  
**Email** : Disponible dans le dashboard

**Question à poser** :
> "Bonjour, est-il possible d'ajouter un paramètre `cancel_url` lors de l'initialisation 
> du paiement pour permettre aux utilisateurs de revenir au site sans finaliser ?"

---

**Statut** : 📝 **SOLUTION DE CONTOURNEMENT DISPONIBLE**  
**Limitation** : ⚠️ Moneroo ne supporte pas nativement le bouton de retour

**Dernière mise à jour** : {{ date('Y-m-d H:i:s') }}


