# Détection des Échecs Côté Client - Documentation

## 🎯 Problème Résolu

Lorsqu'un utilisateur tente un paiement sur Moneroo et que celui-ci échoue immédiatement (ex: solde insuffisant), Moneroo affiche un message d'erreur sur sa page de checkout. **Cependant**, le webhook n'est pas encore appelé à ce moment-là, donc l'utilisateur ne reçoit pas d'email ni de notification.

### Exemple de Message Moneroo
```
Il semble que le solde de votre compte mobile money est insuffisant 
pour ce paiement. Veuillez recharger votre compte et réessayer ou 
choisir une autre méthode de paiement.
```

## ✅ Solution Implémentée

### 1. Détection Automatique des Erreurs

**Fichier**: `public/js/moneroo-failure-detector.js`

Un script JavaScript surveille automatiquement la page Moneroo et détecte les messages d'erreur affichés.

**Fonctionnement**:
1. ✅ S'active automatiquement sur les pages Moneroo
2. ✅ Vérifie toutes les 2 secondes pendant 1 minute
3. ✅ Détecte les messages d'erreur dans les éléments HTML
4. ✅ Identifie le type d'échec (solde insuffisant, carte rejetée, etc.)
5. ✅ Signale immédiatement l'échec au backend
6. ✅ Arrête la surveillance une fois l'échec signalé

### 2. Endpoint API de Signalement

**Route**: `POST /moneroo/report-failure`  
**Contrôleur**: `MonerooController::reportClientSideFailure()`

Reçoit les signalements d'échec du frontend et envoie les notifications.

**Paramètres**:
```json
{
    "payment_id": "pay_ABC123",
    "failure_message": "Solde insuffisant...",
    "failure_type": "insufficient_funds"
}
```

**Réponse**:
```json
{
    "success": true,
    "message": "Échec signalé et notifications envoyées"
}
```

### 3. Amélioration de `failedRedirect()`

La méthode `failedRedirect()` vérifie maintenant le statut auprès de Moneroo pour obtenir la raison d'échec exacte avant d'envoyer les notifications.

---

## 🔍 Types d'Échecs Détectés

| Type | Mots-clés Détectés | Message Utilisateur |
|------|-------------------|---------------------|
| `insufficient_funds` | solde, insuffisant, balance | Solde insuffisant. Veuillez recharger votre compte. |
| `invalid_card` | carte, card, invalide, expiré | Carte invalide ou expirée. Vérifiez vos informations. |
| `transaction_declined` | refusé, declined, rejeté | Transaction refusée par votre banque. |
| `network_error` | connexion, network, internet | Erreur de connexion. Vérifiez votre internet. |
| `timeout` | timeout, délai, temps | Délai d'attente dépassé. Réessayez. |
| `user_cancelled` | annulé, cancel | Paiement annulé par l'utilisateur. |
| `unknown` | (autres cas) | Le paiement n'a pas pu être complété. |

---

## 📊 Flux de Détection

### Scénario 1: Échec Immédiat (Solde Insuffisant)

```
1. Utilisateur clique sur "Payer"
   ↓
2. Moneroo affiche: "Solde insuffisant"
   ↓
3. Script JS détecte le message (< 2 secondes)
   ↓
4. POST /moneroo/report-failure
   ↓
5. Backend marque paiement comme "failed"
   ↓
6. Backend envoie:
   - 📧 Email
   - 💬 WhatsApp
   - 🔔 Notification in-app
   ↓
7. Utilisateur informé immédiatement
```

### Scénario 2: Échec Après Redirection

```
1. Utilisateur redirigé vers page d'échec
   ↓
2. failedRedirect() appelé
   ↓
3. Vérification du statut auprès de Moneroo API
   ↓
4. Extraction de la raison d'échec exacte
   ↓
5. Backend envoie:
   - 📧 Email
   - 💬 WhatsApp
   - 🔔 Notification in-app
   ↓
6. Utilisateur informé avec raison détaillée
```

### Scénario 3: Webhook Reçu Plus Tard

```
1. Moneroo envoie webhook (peut-être 30s-2min plus tard)
   ↓
2. webhook() vérifie si notifications déjà envoyées
   ↓
3. Si déjà envoyées: ignore (idempotence)
   ↓
4. Si pas encore envoyées: envoie maintenant
```

---

## 🔧 Configuration du Script

**Fichier**: `public/js/moneroo-failure-detector.js`

```javascript
const CONFIG = {
    checkInterval: 2000,    // Vérifier toutes les 2 secondes
    maxChecks: 30,          // Maximum 30 vérifications (1 minute)
    reportEndpoint: '/moneroo/report-failure',
    debug: true,            // Logs en console (désactiver en prod)
};
```

### Sélecteurs d'Erreur Surveillés

```javascript
const errorSelectors = [
    '.alert-danger',
    '.error-message',
    '.payment-error',
    '.alert.alert-danger',
    '[class*="error"]',
    '[class*="danger"]',
    '[class*="failed"]',
    '[role="alert"]',
];
```

---

## 📝 Logs Générés

### Log de Détection

```javascript
[Moneroo Failure Detector] Démarrage de la surveillance des erreurs Moneroo
[Moneroo Failure Detector] Payment ID détecté: pay_ABC123
[Moneroo Failure Detector] Message d'erreur détecté: Solde insuffisant...
[Moneroo Failure Detector] Signalement de l'échec au backend: {
    paymentId: "pay_ABC123",
    failureMessage: "Solde insuffisant...",
    failureType: "insufficient_funds"
}
[Moneroo Failure Detector] Échec signalé avec succès, notifications envoyées
```

### Log Backend

```
[INFO] Moneroo: Client-side failure reported
{
    "payment_id": "pay_ABC123",
    "failure_message": "Solde insuffisant...",
    "failure_type": "insufficient_funds",
    "user_agent": "Mozilla/5.0...",
    "ip": "192.168.1.1"
}

[INFO] Moneroo: Client-side failure processed and notifications sent
{
    "payment_id": "pay_ABC123",
    "order_id": 456,
    "failure_reason": "Solde insuffisant. Veuillez recharger votre compte."
}

[INFO] Email et WhatsApp d'échec envoyés pour la commande MON-ABC123
[INFO] Notification PaymentFailed envoyée pour la commande MON-ABC123
```

---

## 🧪 Tests Recommandés

### Test 1: Solde Insuffisant

```
1. Créer une commande
2. Utiliser un compte avec solde insuffisant
3. Cliquer sur "Payer"
4. Vérifier:
   ✅ Message "Solde insuffisant" affiché par Moneroo
   ✅ Console JS montre la détection (< 2 secondes)
   ✅ Email reçu immédiatement
   ✅ WhatsApp reçu immédiatement
   ✅ Notification visible dans la navbar
   ✅ Logs backend générés
```

### Test 2: Carte Invalide

```
1. Créer une commande
2. Utiliser une carte expirée
3. Vérifier:
   ✅ Message d'erreur détecté
   ✅ Type identifié comme "invalid_card"
   ✅ Notifications envoyées
```

### Test 3: Annulation Utilisateur

```
1. Créer une commande
2. Cliquer sur "Annuler" sur la page Moneroo
3. Vérifier:
   ✅ Redirection vers page d'échec
   ✅ failedRedirect() vérifie le statut
   ✅ Notifications envoyées avec raison "Annulation"
```

### Test 4: Webhook Tardif

```
1. Créer une commande avec échec
2. Attendre que le script JS envoie les notifications
3. Simuler l'arrivée du webhook 1 minute plus tard
4. Vérifier:
   ✅ Webhook ne renvoie pas les notifications (idempotence)
   ✅ Log indique "déjà signalé"
```

---

## 🔒 Sécurité

### Protection CSRF

Le script inclut automatiquement le token CSRF:

```javascript
headers: {
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
}
```

### Authentification

L'endpoint `/moneroo/report-failure` nécessite l'authentification:

```php
Route::middleware('auth')->group(function () {
    Route::post('/report-failure', [MonerooController::class, 'reportClientSideFailure']);
});
```

### Validation

- ✅ Vérification du `payment_id`
- ✅ Vérification que le paiement appartient à l'utilisateur connecté
- ✅ Idempotence (ne pas envoyer plusieurs fois)
- ✅ Logs de toutes les tentatives

---

## 📊 Avantages

### 1. Réactivité
- ✅ Détection en **< 2 secondes**
- ✅ Notifications **immédiates**
- ✅ Pas d'attente du webhook

### 2. Couverture Complète
- ✅ Échecs immédiats (solde insuffisant)
- ✅ Échecs après redirection
- ✅ Échecs signalés par webhook
- ✅ **Triple filet de sécurité**

### 3. Expérience Utilisateur
- ✅ Informé immédiatement
- ✅ Raison d'échec claire
- ✅ Instructions pour réessayer
- ✅ Support disponible

### 4. Traçabilité
- ✅ Logs JS dans la console
- ✅ Logs backend détaillés
- ✅ Horodatage précis
- ✅ Facilite le débogage

---

## 🔄 Idempotence

Le système garantit qu'un utilisateur ne reçoit qu'**une seule fois** les notifications, même si:
- Le script JS signale l'échec
- La redirection appelle `failedRedirect()`
- Le webhook arrive plus tard

**Mécanisme**:
1. Vérifier si `payment.status === 'failed'`
2. Si oui, ne pas renvoyer les notifications
3. Logger "déjà signalé"

---

## 📱 Compatibilité

### Navigateurs Supportés
- ✅ Chrome 80+
- ✅ Firefox 75+
- ✅ Safari 13+
- ✅ Edge 80+
- ✅ Mobile (iOS Safari, Chrome Mobile)

### Fonctionnalités Utilisées
- `fetch()` API
- `URLSearchParams`
- `querySelector()`
- `setInterval()`
- ES6+ (arrow functions, const/let)

---

## 🚀 Déploiement

### Checklist

- [ ] Vérifier que `moneroo-failure-detector.js` est dans `public/js/`
- [ ] Vérifier que le script est chargé dans `layouts/app.blade.php`
- [ ] Vérifier la route `/moneroo/report-failure` dans `routes/web.php`
- [ ] Vérifier la méthode `reportClientSideFailure()` dans `MonerooController`
- [ ] Tester en environnement de développement
- [ ] Tester avec un vrai échec (solde insuffisant)
- [ ] Vérifier les logs JS et backend
- [ ] Vérifier réception des emails/notifications
- [ ] Désactiver `debug: true` en production
- [ ] Déployer en production

---

## 🔗 Fichiers Modifiés/Créés

1. ✅ `public/js/moneroo-failure-detector.js` - Script de détection
2. ✅ `app/Http/Controllers/MonerooController.php` - Endpoint + amélioration failedRedirect
3. ✅ `routes/web.php` - Route `/moneroo/report-failure`
4. ✅ `resources/views/layouts/app.blade.php` - Chargement du script

---

## 📞 Support

Pour toute question:
- 📧 Email: support@herime-academie.com
- 📱 WhatsApp: [Numéro]
- 💬 Chat: [Lien]

---

**Date de création**: {{ date('d/m/Y') }}  
**Auteur**: Assistant IA  
**Version**: 1.0

