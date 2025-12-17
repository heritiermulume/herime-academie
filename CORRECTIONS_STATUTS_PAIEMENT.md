# Corrections des Statuts de Paiement

## Problème Identifié

Lors du traitement des paiements Moneroo, certains statuts n'étaient pas correctement gérés. Par exemple :
- Lorsqu'un paiement était initié avec succès mais échouait ensuite (ex: solde insuffisant)
- L'opération était marquée comme "réussie" côté application alors que le paiement avait échoué
- Les utilisateurs étaient redirigés vers la page de succès même en cas d'échec

### Cause Racine

Le problème venait de deux sources principales :

1. **Confusion entre "initialisation réussie" et "paiement réussi"**
   - L'initialisation d'un paiement (obtenir un lien de paiement) peut réussir
   - Mais le paiement lui-même peut échouer (solde insuffisant, carte rejetée, etc.)
   - L'application confondait ces deux états

2. **Absence de vérification du statut réel**
   - Les pages de succès/annulation marquaient automatiquement les paiements sans vérifier le statut auprès du fournisseur
   - Violation des bonnes pratiques de sécurité des paiements en ligne

## Corrections Apportées

### 1. PaymentController.php

#### Méthode `success()` - Avant
```php
public function success(Request $request)
{
    $orderId = $request->get('order_id');
    $order = Order::findOrFail($orderId);

    // ❌ PROBLÈME: Marque automatiquement comme payé sans vérification
    $order->update([
        'status' => 'paid',
        'paid_at' => now(),
    ]);
    
    // Crée les inscriptions immédiatement
    // ...
}
```

#### Méthode `success()` - Après
```php
public function success(Request $request)
{
    $orderId = $request->get('order_id');
    $order = Order::findOrFail($orderId);

    // ✅ CORRECTION: Vérifie le statut réel auprès du fournisseur
    $payment = $order->payments()->first();
    $verifiedStatus = $this->verifyPaymentStatus($payment);
    
    // Traite selon le statut vérifié
    if ($verifiedStatus === 'completed') {
        // Finalise la commande uniquement si vraiment complété
        // ...
    } elseif (in_array($verifiedStatus, ['failed', 'cancelled', 'expired', 'rejected'])) {
        // Redirige vers la page d'échec
        // ...
    } else {
        // Affiche un message d'attente
        // ...
    }
}
```

#### Nouvelles Méthodes Ajoutées

1. **`verifyPaymentStatus(Payment $payment): string`**
   - Vérifie le statut réel auprès du fournisseur de paiement
   - Supporte Stripe, Moneroo, PayPal
   - Retourne le statut vérifié

2. **`verifyStripePayment(Payment $payment): string`**
   - Interroge l'API Stripe pour obtenir le statut réel
   - Mappe les statuts Stripe vers nos statuts locaux

3. **`verifyMonerooPayment(Payment $payment): string`**
   - Pour Moneroo, utilise le contrôleur dédié
   - Le webhook est la source de vérité

4. **`verifyPayPalPayment(Payment $payment): string`**
   - Placeholder pour future implémentation PayPal

#### Méthode `cancel()` - Améliorée
- Vérifie maintenant le statut réel avant d'annuler
- Empêche l'annulation de paiements déjà complétés
- Ajoute des logs détaillés pour le suivi

### 2. MonerooController.php

#### Amélioration du Webhook

**Nouvelle méthode `extractFailureReason()`**
```php
private function extractFailureReason(array $paymentData, array $payload, string $status): string
{
    // Cherche la raison d'échec dans plusieurs champs possibles
    $reason = $paymentData['failure_reason'] 
           ?? $paymentData['error_message'] 
           ?? $paymentData['error'] 
           ?? $paymentData['message'] 
           ?? $payload['message'] 
           ?? null;
    
    // Mappe le statut vers un message compréhensible
    return match ($status) {
        'failed' => 'Le paiement a échoué. Veuillez vérifier vos informations...',
        'rejected' => 'Le paiement a été rejeté. Cela peut être dû à un solde insuffisant...',
        // ...
    };
}
```

**Avantages :**
- Capture toutes les raisons d'échec possibles (solde insuffisant, carte rejetée, etc.)
- Fournit des messages d'erreur clairs et compréhensibles aux utilisateurs
- Logs détaillés pour le débogage

#### Amélioration de `successfulRedirect()`
- Utilise maintenant `extractFailureReason()` pour des messages d'erreur détaillés
- Logs enrichis avec le payload complet pour analyse
- Meilleure traçabilité des échecs

## Statuts Gérés

### Selon la Documentation Moneroo

| Statut | Description | Action |
|--------|-------------|--------|
| `pending` | Paiement en attente | Afficher message d'attente |
| `processing` | En cours de traitement | Afficher message d'attente |
| `completed` | Paiement réussi | Finaliser la commande |
| `failed` | Paiement échoué | Annuler et notifier |
| `cancelled` | Annulé par l'utilisateur | Annuler et notifier |
| `expired` | Délai expiré | Annuler et notifier |
| `rejected` | Rejeté (ex: solde insuffisant) | Annuler et notifier |

## Flux de Paiement Corrigé

### 1. Initialisation
```
Client → Backend → Moneroo API
                ↓
            Lien de paiement
                ↓
            Redirection client
```

**Statut**: `pending` (en attente)

### 2. Traitement du Paiement
```
Client → Page Moneroo → Traitement
                        ↓
                    Succès/Échec
```

**Statuts possibles**: `processing`, `completed`, `failed`, `rejected`

### 3. Notification (Webhook)
```
Moneroo → Webhook Backend
            ↓
        Mise à jour BDD
            ↓
        Emails/Notifications
```

**Source de vérité**: Le webhook est la référence finale

### 4. Redirection
```
Page Moneroo → Return URL Backend
                    ↓
            Vérification statut API
                    ↓
            Affichage page appropriée
```

**Important**: La redirection ne fait qu'afficher le résultat, le webhook a déjà traité

## Bonnes Pratiques Implémentées

### 1. Ne Jamais Faire Confiance à la Redirection
❌ **Mauvais**:
```php
// Sur la page de succès
$order->update(['status' => 'paid']); // DANGEREUX!
```

✅ **Bon**:
```php
// Sur la page de succès
$verifiedStatus = $this->verifyPaymentStatus($payment);
if ($verifiedStatus === 'completed') {
    // Alors seulement on finalise
}
```

### 2. Le Webhook est la Source de Vérité
- Le webhook reçoit les notifications directes de Moneroo
- Il est appelé même si l'utilisateur ferme son navigateur
- Il doit gérer tous les cas (succès, échec, etc.)

### 3. Vérification Côté Serveur
- Toujours interroger l'API du fournisseur pour le statut final
- Ne jamais se fier uniquement aux paramètres URL
- Logger tous les statuts pour audit

### 4. Gestion des Erreurs Détaillée
- Capturer toutes les raisons d'échec possibles
- Fournir des messages clairs aux utilisateurs
- Logger pour analyse et amélioration

## Tests Recommandés

### 1. Test de Solde Insuffisant
```
1. Créer une commande
2. Initier le paiement
3. Utiliser un compte avec solde insuffisant
4. Vérifier que:
   - Le webhook reçoit le statut 'rejected' ou 'failed'
   - La commande est annulée
   - L'utilisateur reçoit un email d'échec avec la raison
   - La page affiche le bon message d'erreur
```

### 2. Test de Paiement Réussi
```
1. Créer une commande
2. Initier le paiement
3. Compléter le paiement avec succès
4. Vérifier que:
   - Le webhook reçoit le statut 'completed'
   - La commande est marquée 'paid'
   - Les inscriptions sont créées
   - Les emails sont envoyés
   - Le panier est vidé
```

### 3. Test d'Annulation
```
1. Créer une commande
2. Initier le paiement
3. Annuler avant de payer
4. Vérifier que:
   - Le webhook reçoit le statut 'cancelled'
   - La commande est annulée
   - L'utilisateur reçoit un email d'annulation
```

### 4. Test de Délai Expiré
```
1. Créer une commande
2. Initier le paiement
3. Attendre l'expiration du délai
4. Vérifier que:
   - Le webhook reçoit le statut 'expired'
   - La commande est annulée automatiquement
   - L'utilisateur est notifié
```

## Logs pour Débogage

### Logs Ajoutés

1. **Lors de la vérification du statut**
```php
\Log::info('PaymentController: Vérification du statut de paiement', [
    'order_id' => $orderId,
    'payment_id' => $payment->payment_id,
    'local_status' => $payment->status,
    'verified_status' => $verifiedStatus,
]);
```

2. **Lors de l'échec du paiement**
```php
\Log::info('Moneroo: Order cancelled after failed payment', [
    'order_id' => $payment->order->id,
    'payment_id' => $paymentId,
    'status' => $status,
    'reason' => $failureReason,
    'full_payload' => $payload, // Pour analyse détaillée
]);
```

3. **Lors de l'envoi des emails**
```php
\Log::info("Email d'échec de paiement envoyé", [
    'order_number' => $order->order_number,
    'user_email' => $order->user->email,
    'failure_reason' => $failureReason,
    'status' => $status,
]);
```

## Monitoring

### Métriques à Surveiller

1. **Taux d'échec des paiements**
   - Filtrer par raison (solde insuffisant, carte rejetée, etc.)
   - Identifier les problèmes récurrents

2. **Temps de traitement**
   - De l'initialisation à la finalisation
   - Identifier les goulots d'étranglement

3. **Webhooks manqués**
   - Vérifier que tous les webhooks sont reçus
   - Alerter en cas de problème

4. **Incohérences de statut**
   - Comparer statut local vs statut Moneroo
   - Identifier les cas où la vérification a évité un problème

## Conclusion

Ces corrections garantissent que :
- ✅ Tous les statuts de paiement sont correctement gérés
- ✅ Les échecs (solde insuffisant, etc.) sont détectés et traités
- ✅ Les utilisateurs reçoivent des messages d'erreur clairs
- ✅ L'application suit les bonnes pratiques de sécurité
- ✅ Les logs permettent un débogage efficace

## Références

- [Documentation Moneroo - Initialiser un paiement](https://docs.moneroo.io/fr/payments/initialiser-un-paiement)
- [Documentation Moneroo - Intégration Standard](https://docs.moneroo.io/fr/payments/integration-standard)
- [Documentation Moneroo - Vérifier un paiement](https://docs.moneroo.io/fr/payments/verifier-un-paiement)
- [Documentation Moneroo - Statuts](https://docs.moneroo.io/fr/payments/statut)

