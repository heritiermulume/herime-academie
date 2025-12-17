# Notifications d'Échec de Paiement - Documentation

## 🎯 Objectif

Envoyer systématiquement un **email ET une notification in-app** à l'utilisateur dans **TOUS** les cas d'échec de paiement, pour garantir qu'il soit toujours informé du problème.

---

## ✅ Implémentation

### 1. Nouvelle Notification Créée

**Fichier**: `app/Notifications/PaymentFailed.php`

Cette notification est envoyée dans **tous** les cas d'échec:
- ❌ Échec d'initialisation
- ❌ Solde insuffisant  
- ❌ Carte rejetée
- ❌ Paiement annulé par l'utilisateur
- ❌ Délai expiré
- ❌ Erreur technique
- ❌ Annulation automatique (timeout)

**Canaux de notification**:
- 📧 **Email** (via `PaymentFailedMail`)
- 💬 **WhatsApp** (via `CommunicationService`)
- 🔔 **Notification in-app** (affichée dans la navbar)

**Données de la notification**:
```php
[
    'type' => 'payment_failed',
    'order_id' => $order->id,
    'order_number' => $order->order_number,
    'amount' => $order->total,
    'currency' => $order->currency,
    'failure_reason' => $failureReason,
    'message' => 'Votre paiement pour la commande #XXX a échoué. Raison: ...',
    'icon' => 'fas fa-times-circle',
    'color' => 'danger',
    'action_url' => route('cart.index'),
    'action_text' => 'Retour au panier',
]
```

### 2. Méthode Centralisée

**Méthode**: `sendPaymentFailureNotifications(Order $order, ?string $failureReason)`

Cette méthode centralise l'envoi des notifications pour éviter la duplication de code.

**Emplacement**:
- `MonerooController::sendPaymentFailureNotifications()`
- `PaymentController::sendPaymentFailureNotifications()`

**Fonctionnement**:
1. ✅ Charge les relations nécessaires (user, orderItems, payments)
2. ✅ Vérifie que l'utilisateur existe et a un email
3. ✅ Envoie l'email ET WhatsApp via `CommunicationService`
4. ✅ Envoie la notification in-app via `Notification::sendNow()`
5. ✅ Log toutes les actions (succès et erreurs)
6. ✅ Ne bloque jamais le processus (gestion des exceptions)

---

## 📍 Points d'Envoi des Notifications

### MonerooController

#### 1. Échec d'Initialisation
**Ligne**: ~431  
**Cas**: L'API Moneroo refuse d'initialiser le paiement
```php
// Exemple: Aucune méthode de paiement activée pour la devise
$this->sendPaymentFailureNotifications($order, $failureReason);
```

#### 2. Erreur Technique
**Ligne**: ~546  
**Cas**: Exception lors de l'appel API
```php
// Exemple: Timeout, erreur réseau
$this->sendPaymentFailureNotifications($order, $failureReason);
```

#### 3. Webhook - Échec de Paiement
**Ligne**: ~725  
**Cas**: Moneroo notifie que le paiement a échoué
```php
// Statuts: failed, cancelled, expired, rejected
$this->sendPaymentFailureNotifications($payment->order, $failureReason);
```

#### 4. Annulation Manuelle
**Ligne**: ~818  
**Cas**: L'utilisateur annule le paiement
```php
$this->sendPaymentFailureNotifications($payment->order, $failureReason);
```

#### 5. Redirection - Échec Détecté
**Ligne**: ~1151  
**Cas**: Vérification du statut révèle un échec
```php
// Statuts: failed, cancelled, expired, rejected
$this->sendPaymentFailureNotifications($payment->order, $failureReason);
```

#### 6. Redirection Échec
**Ligne**: ~1237  
**Cas**: Redirection vers la page d'échec
```php
$this->sendPaymentFailureNotifications($payment->order, $failureReason);
```

#### 7. Annulation Automatique (Timeout)
**Ligne**: ~1367  
**Cas**: Commande en attente depuis trop longtemps
```php
// Après 30 minutes par défaut
$this->sendPaymentFailureNotifications($order, $failureReason);
```

### PaymentController

#### 1. Vérification - Échec Détecté
**Ligne**: ~318  
**Cas**: Vérification du statut révèle un échec
```php
$this->sendPaymentFailureNotifications($order, $failureReason);
```

#### 2. Annulation par l'Utilisateur
**Ligne**: ~381  
**Cas**: L'utilisateur annule depuis la page de paiement
```php
$this->sendPaymentFailureNotifications($order, 'Paiement annulé par l\'utilisateur');
```

#### 3. Webhook Stripe - Échec
**Ligne**: ~475  
**Cas**: Stripe notifie un échec de paiement
```php
$this->sendPaymentFailureNotifications($order, $failureReason);
```

---

## 📊 Raisons d'Échec Capturées

| Raison | Description | Source |
|--------|-------------|--------|
| **Solde insuffisant** | Le compte n'a pas assez de fonds | Moneroo webhook |
| **Carte rejetée** | La carte est invalide ou expirée | Moneroo/Stripe |
| **Paiement annulé** | Annulation par l'utilisateur | Action utilisateur |
| **Délai expiré** | Temps de paiement dépassé | Moneroo webhook |
| **Erreur technique** | Problème de communication API | Exception |
| **Méthode non activée** | Devise non supportée | Moneroo API |
| **Annulation automatique** | Timeout de 30 minutes | Système |

---

## 🔔 Affichage des Notifications

### Dans la Navbar

Les notifications apparaissent dans le menu déroulant des notifications avec:
- 🔴 **Icône rouge** (`fas fa-times-circle`)
- 📝 **Message clair** avec numéro de commande et raison
- 🔗 **Lien d'action** vers le panier
- ⏰ **Horodatage** de la notification

### Format d'Affichage

```html
<div class="notification-item payment-failed">
    <i class="fas fa-times-circle text-danger"></i>
    <div class="notification-content">
        <strong>Échec de paiement</strong>
        <p>Votre paiement pour la commande #MON-XXX a échoué.</p>
        <p class="text-muted">Raison: Solde insuffisant</p>
        <a href="/cart" class="btn btn-sm btn-primary">Retour au panier</a>
    </div>
    <span class="notification-time">Il y a 2 minutes</span>
</div>
```

---

## 📧 Contenu de l'Email

### Sujet
```
Échec de paiement - Commande #MON-XXX
```

### Corps de l'Email

```
Bonjour [Nom de l'utilisateur],

Votre paiement pour la commande #MON-XXX a échoué.

Raison: [Raison détaillée de l'échec]

Montant: [Montant] [Devise]

Vous pouvez réessayer le paiement en retournant à votre panier.

[Bouton: Retour au panier]

Si le problème persiste, veuillez contacter notre support.

Cordialement,
L'équipe Herime Académie
```

---

## 🔍 Logs Générés

### Log de Succès
```
[INFO] Email et WhatsApp d'échec envoyés pour la commande MON-XXX
{
    "order_id": 123,
    "user_id": 456,
    "user_email": "user@example.com",
    "failure_reason": "Solde insuffisant"
}

[INFO] Notification PaymentFailed envoyée à l'utilisateur 456 pour la commande 123
{
    "order_id": 123,
    "order_number": "MON-XXX",
    "user_id": 456,
    "user_email": "user@example.com",
    "failure_reason": "Solde insuffisant"
}
```

### Log d'Erreur
```
[ERROR] Erreur lors de l'envoi de l'email d'échec
{
    "order_id": 123,
    "user_id": 456,
    "user_email": "user@example.com",
    "error": "SMTP connection failed",
    "trace": "..."
}
```

---

## ✅ Avantages de cette Implémentation

### 1. Couverture Complète
- ✅ **Tous** les cas d'échec sont couverts
- ✅ Aucun échec ne passe inaperçu
- ✅ L'utilisateur est **toujours** informé

### 2. Multi-Canal
- ✅ Email (notification persistante)
- ✅ WhatsApp (notification instantanée)
- ✅ In-app (notification visible immédiatement)

### 3. Robustesse
- ✅ Gestion des exceptions
- ✅ Ne bloque jamais le processus
- ✅ Logs détaillés pour le débogage

### 4. Expérience Utilisateur
- ✅ Messages clairs et compréhensibles
- ✅ Raisons d'échec détaillées
- ✅ Actions suggérées (retour au panier)
- ✅ Support disponible

### 5. Traçabilité
- ✅ Tous les envois sont loggés
- ✅ Succès et erreurs enregistrés
- ✅ Facilite le support client

---

## 🧪 Tests Recommandés

### Test 1: Solde Insuffisant
```
1. Créer une commande
2. Utiliser un compte avec solde insuffisant
3. Vérifier:
   ✅ Email reçu avec raison "Solde insuffisant"
   ✅ WhatsApp reçu
   ✅ Notification visible dans la navbar
   ✅ Logs générés
```

### Test 2: Annulation Utilisateur
```
1. Créer une commande
2. Annuler le paiement
3. Vérifier:
   ✅ Email reçu avec raison "Annulation par l'utilisateur"
   ✅ Notification visible
   ✅ Logs générés
```

### Test 3: Timeout Automatique
```
1. Créer une commande
2. Attendre 30 minutes sans payer
3. Vérifier:
   ✅ Email reçu avec raison "Annulation automatique"
   ✅ Notification visible
   ✅ Logs générés
```

### Test 4: Erreur Technique
```
1. Simuler une erreur API (déconnecter internet)
2. Tenter un paiement
3. Vérifier:
   ✅ Email reçu avec raison "Erreur technique"
   ✅ Notification visible
   ✅ Logs générés
```

---

## 📝 Checklist de Déploiement

- [ ] Vérifier que `PaymentFailed.php` est créé
- [ ] Vérifier que `sendPaymentFailureNotifications()` existe dans les deux contrôleurs
- [ ] Tester l'envoi d'email en sandbox
- [ ] Tester l'envoi de WhatsApp en sandbox
- [ ] Tester l'affichage de la notification in-app
- [ ] Vérifier les logs générés
- [ ] Tester tous les cas d'échec
- [ ] Valider avec un utilisateur réel

---

## 🔗 Fichiers Modifiés

1. ✅ `app/Notifications/PaymentFailed.php` - Nouvelle notification
2. ✅ `app/Http/Controllers/MonerooController.php` - Méthode centralisée + 7 points d'appel
3. ✅ `app/Http/Controllers/PaymentController.php` - Méthode centralisée + 3 points d'appel

---

## 📞 Support

Pour toute question sur cette implémentation:
- 📧 Email: support@herime-academie.com
- 📱 WhatsApp: [Numéro]
- 💬 Chat: [Lien]

---

**Date de création**: {{ date('d/m/Y') }}  
**Auteur**: Assistant IA  
**Version**: 1.0

