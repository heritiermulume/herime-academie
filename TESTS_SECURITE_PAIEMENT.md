# 🧪 TESTS DE SÉCURITÉ - FLUX DE PAIEMENT MONEROO

**Date**: {{ date('Y-m-d H:i:s') }}  
**Priorité**: 🔴 **CRITIQUE**  
**Objectif**: Vérifier que toutes les failles ont été corrigées

---

## 📋 SCÉNARIOS DE TEST

### ✅ Test 1 : Paiement Normal Réussi (Cas Nominal)

**Objectif** : Vérifier que le flux nominal fonctionne correctement

**Étapes** :
1. Se connecter avec un compte test
2. Ajouter un cours au panier
3. Aller au checkout
4. Sélectionner Moneroo comme méthode de paiement
5. Compléter le paiement avec succès
6. Vérifier la redirection vers `/moneroo/success?payment_id=XXX`

**Résultat Attendu** :
- ✅ Page de succès affichée
- ✅ Détails de la commande visibles
- ✅ Numéro de commande affiché
- ✅ Montant correct
- ✅ Statut = "Payée"
- ✅ Email de confirmation reçu
- ✅ Accès au cours débloqué
- ✅ Panier vidé

**Vérifications DB** :
```sql
-- Vérifier la commande
SELECT id, order_number, status, total, paid_at 
FROM orders 
WHERE order_number = 'XXX';
-- Status devrait être 'paid'

-- Vérifier le paiement
SELECT id, payment_id, status, amount, processed_at 
FROM payments 
WHERE order_id = [ID_COMMANDE];
-- Status devrait être 'completed'

-- Vérifier l'inscription
SELECT id, user_id, course_id, status, created_at 
FROM enrollments 
WHERE order_id = [ID_COMMANDE];
-- Status devrait être 'active'
```

---

### 🔴 Test 2 : Actualisation de la Page Après Succès (FAILLE CORRIGÉE)

**Objectif** : Vérifier qu'on ne peut PAS voir la page de succès après actualisation sans payment_id

**Étapes** :
1. Compléter un paiement avec succès
2. Arriver sur `/moneroo/success?payment_id=XXX`
3. Copier l'URL et supprimer `?payment_id=XXX`
4. Accéder à `/moneroo/success` (sans paramètre)

**Résultat Attendu AVANT Correction** :
- ❌ Page de succès affichée sans détails
- ❌ Utilisateur confus

**Résultat Attendu APRÈS Correction** :
- ✅ Redirection immédiate vers `/orders` (liste des commandes)
- ✅ Message flash : "Impossible de retrouver les détails de votre paiement. Veuillez vérifier vos commandes ci-dessous."
- ✅ Log d'avertissement créé
- ✅ Aucune page de succès affichée

**Vérifications Logs** :
```bash
# Chercher dans storage/logs/laravel.log
grep "successfulRedirect called without valid payment_id" storage/logs/laravel.log
```

**Log Attendu** :
```
[YYYY-MM-DD HH:MM:SS] local.WARNING: Moneroo: successfulRedirect called without valid payment_id or payment not found {"url":"https://...","user_id":123,...}
```

---

### 🔴 Test 3 : Accès Direct Sans payment_id (FAILLE CORRIGÉE)

**Objectif** : Vérifier qu'un utilisateur ne peut PAS taper manuellement `/moneroo/success`

**Étapes** :
1. Se connecter (ou pas)
2. Taper manuellement dans le navigateur : `https://herime-academie.com/moneroo/success`
3. Appuyer sur Entrée

**Résultat Attendu (Si Authentifié)** :
- ✅ Redirection vers `/orders`
- ✅ Message : "Impossible de retrouver les détails de votre paiement..."

**Résultat Attendu (Si Non Authentifié)** :
- ✅ Redirection vers `/` (home)
- ✅ Message : "Session expirée. Veuillez vous reconnecter..."

---

### 🔴 Test 4 : payment_id Invalide (FAILLE CORRIGÉE)

**Objectif** : Vérifier qu'un payment_id inexistant ne permet PAS d'afficher la page

**Étapes** :
1. Accéder à `/moneroo/success?payment_id=FAUX_ID_123456`

**Résultat Attendu** :
- ✅ Redirection vers `/orders` (si authentifié) ou `/` (si non authentifié)
- ✅ Message approprié
- ✅ Log d'avertissement

**Vérification Logs** :
```bash
grep "FAUX_ID_123456" storage/logs/laravel.log
```

---

### ✅ Test 5 : Paiement Encore en Attente (Cas Valide)

**Objectif** : Vérifier que le système gère correctement les paiements pending

**Étapes** :
1. Initier un paiement Moneroo
2. Ne PAS compléter le paiement (fermer la fenêtre Moneroo)
3. Utiliser l'URL `/moneroo/success?payment_id=XXX` (du paiement pending)

**Résultat Attendu** :
- ✅ Page affichée avec message "Paiement en cours de traitement"
- ✅ Icône spinner visible
- ✅ Message : "Votre paiement est en cours de traitement. Veuillez patienter..."
- ✅ PAS de détails de commande (car pas encore payée)

**Vérifications DB** :
```sql
SELECT status FROM payments WHERE payment_id = 'XXX';
-- Status devrait être 'pending'

SELECT status FROM orders WHERE id = [ORDER_ID];
-- Status devrait être 'pending'
```

---

### ✅ Test 6 : Paiement Échoué Détecté par Vérification API

**Objectif** : Vérifier que les échecs sont détectés via l'API Moneroo

**Étapes** :
1. Initier un paiement
2. Simuler un échec chez Moneroo (solde insuffisant, carte rejetée, etc.)
3. Moneroo redirige (par erreur) vers `/moneroo/success?payment_id=XXX`
4. Le contrôleur vérifie le statut via l'API

**Résultat Attendu** :
- ✅ Détection du statut 'failed' via l'API
- ✅ Redirection automatique vers `/moneroo/failed`
- ✅ Email d'échec envoyé
- ✅ Notification in-app créée

**Vérifications DB** :
```sql
SELECT status, failure_reason FROM payments WHERE payment_id = 'XXX';
-- Status = 'failed', failure_reason renseignée

SELECT status FROM orders WHERE id = [ORDER_ID];
-- Status = 'cancelled'
```

---

### ✅ Test 7 : Webhook Moneroo (Cas de Référence)

**Objectif** : Vérifier que le webhook continue de fonctionner normalement

**Étapes** :
1. Initier un paiement
2. Compléter le paiement
3. Attendre la réception du webhook de Moneroo

**Résultat Attendu** :
- ✅ Webhook reçu et validé (signature HMAC)
- ✅ Paiement marqué 'completed'
- ✅ Commande marquée 'paid'
- ✅ Inscriptions créées
- ✅ Email envoyé
- ✅ Panier vidé
- ✅ Log de confirmation

**Vérifications Logs** :
```bash
grep "Moneroo webhook received" storage/logs/laravel.log | tail -n 1
grep "Order finalized after successful payment" storage/logs/laravel.log | tail -n 1
```

---

### ✅ Test 8 : Échec Détecté Côté Client (JavaScript)

**Objectif** : Vérifier que le script moneroo-failure-detector.js fonctionne

**Étapes** :
1. Initier un paiement
2. Sur la page Moneroo, simuler une erreur (ex: "solde insuffisant")
3. Vérifier que le script détecte l'erreur
4. Vérifier que l'API `/moneroo/report-failure` est appelée

**Résultat Attendu** :
- ✅ Erreur détectée par le script JavaScript
- ✅ Requête POST envoyée à `/moneroo/report-failure`
- ✅ Paiement marqué 'failed' en DB
- ✅ Commande marquée 'cancelled'
- ✅ Email d'échec envoyé
- ✅ Notification in-app créée

**Vérifications Console Navigateur** :
```javascript
// Ouvrir DevTools > Console
// Chercher :
"[Moneroo Failure Detector] Payment ID détecté dans l'URL: XXX"
"[Moneroo Failure Detector] Message d'erreur détecté: ..."
"[Moneroo Failure Detector] Échec signalé avec succès."
```

**Vérifications Network** :
```
POST /moneroo/report-failure
Status: 200 OK
Response: {"success":true,"message":"Échec signalé avec succès"}
```

---

### 🔴 Test 9 : Tentative d'Injection payment_id (Sécurité)

**Objectif** : Vérifier qu'on ne peut PAS afficher la commande d'un autre utilisateur

**Étapes** :
1. Utilisateur A complète un paiement → `payment_id_A`
2. Utilisateur B (connecté avec un autre compte) essaie d'accéder à `/moneroo/success?payment_id=payment_id_A`

**Résultat Attendu** :
- ✅ Utilisateur B ne voit PAS la commande de l'utilisateur A
- ✅ Redirection vers `/orders` avec message d'erreur
- ✅ Log de sécurité créé

**Implémentation Requise** (si pas déjà fait) :
```php
// Dans successfulRedirect(), ajouter après avoir trouvé le payment :
if ($payment && $payment->order && auth()->check()) {
    // Vérifier que la commande appartient bien à l'utilisateur connecté
    if ($payment->order->user_id !== auth()->id()) {
        \Log::warning('Moneroo: Attempted access to another user order', [
            'payment_id' => $paymentId,
            'order_user_id' => $payment->order->user_id,
            'current_user_id' => auth()->id(),
        ]);
        
        return redirect()->route('orders.index')->with('error', 
            'Vous n\'avez pas l\'autorisation d\'accéder à cette commande.'
        );
    }
}
```

---

### ✅ Test 10 : Doublon de Finalisation (Idempotence)

**Objectif** : Vérifier qu'une commande ne peut pas être finalisée deux fois

**Étapes** :
1. Compléter un paiement (webhook + redirect success)
2. Simuler un second webhook avec le même payment_id
3. Accéder à nouveau à `/moneroo/success?payment_id=XXX`

**Résultat Attendu** :
- ✅ Page de succès affichée normalement
- ✅ AUCUNE duplication d'inscription
- ✅ AUCUN email en double
- ✅ Log "Order already finalized"

**Vérifications DB** :
```sql
-- Vérifier qu'il n'y a qu'UNE SEULE inscription par cours
SELECT course_id, COUNT(*) as count
FROM enrollments
WHERE user_id = [USER_ID]
GROUP BY course_id
HAVING count > 1;
-- Résultat : 0 lignes (aucun doublon)
```

---

## 🔧 OUTILS DE TEST

### 1. Test Manuel avec Navigateur

**Chrome DevTools** :
- Network tab : Observer les requêtes
- Console tab : Observer les logs JavaScript
- Application > Storage > Clear Site Data : Réinitialiser session

### 2. Test avec cURL

**Simuler un accès direct** :
```bash
# Test 1: Sans payment_id (devrait rediriger)
curl -I https://herime-academie.com/moneroo/success

# Test 2: Avec payment_id invalide
curl -I https://herime-academie.com/moneroo/success?payment_id=FAUX_ID

# Test 3: Avec payment_id valide
curl -I https://herime-academie.com/moneroo/success?payment_id=VALID_ID
```

### 3. Vérification des Logs

**Logs Laravel** :
```bash
# Suivre les logs en temps réel
tail -f storage/logs/laravel.log

# Chercher les avertissements de sécurité
grep "WARNING.*successfulRedirect" storage/logs/laravel.log

# Chercher les finalisations de commande
grep "Order finalized" storage/logs/laravel.log

# Chercher les échecs
grep "Payment failed" storage/logs/laravel.log
```

### 4. Requêtes SQL de Vérification

```sql
-- Commandes en suspens (devrait être vide après paiement)
SELECT id, order_number, status, created_at
FROM orders
WHERE status = 'pending'
AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR);

-- Paiements échoués récents
SELECT p.id, p.payment_id, p.status, p.failure_reason, o.order_number
FROM payments p
JOIN orders o ON p.order_id = o.id
WHERE p.status = 'failed'
AND p.created_at > DATE_SUB(NOW(), INTERVAL 1 DAY)
ORDER BY p.created_at DESC;

-- Inscriptions sans commande payée (anomalie)
SELECT e.id, e.user_id, e.course_id, o.status as order_status
FROM enrollments e
JOIN orders o ON e.order_id = o.id
WHERE o.status NOT IN ('paid', 'completed');
-- Résultat attendu : 0 lignes
```

---

## ✅ CHECKLIST DE VALIDATION

### Avant Déploiement en Production

- [ ] Test 1 : Paiement normal réussi → ✅ OK
- [ ] Test 2 : Actualisation sans payment_id → ✅ Redirige
- [ ] Test 3 : Accès direct → ✅ Redirige
- [ ] Test 4 : payment_id invalide → ✅ Redirige
- [ ] Test 5 : Paiement pending → ✅ Message approprié
- [ ] Test 6 : Échec détecté par API → ✅ Redirige vers failed
- [ ] Test 7 : Webhook fonctionne → ✅ Finalisation OK
- [ ] Test 8 : Détection client-side → ✅ Signalement OK
- [ ] Test 9 : Tentative d'injection → ✅ Bloquée
- [ ] Test 10 : Idempotence → ✅ Aucun doublon

### Vérifications Supplémentaires

- [ ] Tous les logs de sécurité sont créés
- [ ] Aucune erreur PHP dans les logs
- [ ] Les emails sont bien envoyés
- [ ] Les notifications in-app sont créées
- [ ] Le panier est vidé après succès
- [ ] Les inscriptions sont créées
- [ ] Les commissions d'ambassadeur sont créées (si applicable)

---

## 📊 RAPPORT DE TEST

**Template à remplir après chaque test** :

```
TEST #X: [Nom du test]
Date: [Date/Heure]
Testeur: [Nom]
Environnement: [Local/Staging/Production]

RÉSULTAT: [✅ PASS / ❌ FAIL]

Détails:
- Étape 1: [OK/KO] [Notes]
- Étape 2: [OK/KO] [Notes]
- ...

Logs pertinents:
[Copier les logs ici]

Screenshots:
[Attacher si nécessaire]

Problèmes détectés:
[Décrire les anomalies]

Actions correctives:
[Si applicable]
```

---

## 🆘 EN CAS D'ÉCHEC

### Si un test échoue :

1. **Ne PAS déployer en production**
2. **Analyser les logs** : `storage/logs/laravel.log`
3. **Vérifier la DB** : État des commandes/paiements
4. **Reproduire le bug** : Étapes exactes
5. **Corriger le code**
6. **Re-tester complètement** : Tous les tests, pas seulement celui qui a échoué
7. **Documenter la correction**

### Contacts Support

- **Développeur Principal** : [Email/Téléphone]
- **Support Moneroo** : support@moneroo.io
- **Logs Monitoring** : [URL du système de monitoring]

---

**Statut**: 🟡 **EN ATTENTE DE TESTS**  
**Prochaine étape**: Exécuter tous les tests et remplir le rapport


