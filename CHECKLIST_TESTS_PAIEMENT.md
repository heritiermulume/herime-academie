# Checklist de Tests - Corrections des Statuts de Paiement

## 🎯 Objectif
Vérifier que tous les statuts de paiement sont correctement gérés après les corrections apportées.

---

## ✅ Tests Fonctionnels

### 1. Test de Paiement Réussi
- [ ] Ajouter un cours au panier
- [ ] Procéder au checkout
- [ ] Compléter le paiement avec succès
- [ ] **Vérifications :**
  - [ ] Redirection vers la page de succès
  - [ ] Commande marquée comme "paid" dans la BDD
  - [ ] Paiement marqué comme "completed" dans la BDD
  - [ ] Inscriptions créées pour tous les cours
  - [ ] Panier vidé (BDD et session)
  - [ ] Email de confirmation reçu
  - [ ] Email de facture reçu
  - [ ] Notification dans la navbar
  - [ ] Accès aux cours dans le dashboard étudiant
  - [ ] Commission ambassadeur créée (si code promo utilisé)

### 2. Test de Solde Insuffisant
- [ ] Ajouter un cours au panier
- [ ] Procéder au checkout
- [ ] Utiliser un compte avec solde insuffisant
- [ ] **Vérifications :**
  - [ ] Webhook reçoit le statut 'rejected' ou 'failed'
  - [ ] Commande marquée comme "cancelled" dans la BDD
  - [ ] Paiement marqué comme "failed" dans la BDD
  - [ ] Raison d'échec enregistrée : "solde insuffisant" ou similaire
  - [ ] Email d'échec reçu avec raison détaillée
  - [ ] Redirection vers la page d'échec
  - [ ] Message d'erreur clair affiché
  - [ ] Panier toujours présent
  - [ ] Aucune inscription créée
  - [ ] Log détaillé dans storage/logs/laravel.log

### 3. Test de Paiement en Cours
- [ ] Ajouter un cours au panier
- [ ] Procéder au checkout
- [ ] Initier le paiement mais ne pas compléter
- [ ] Revenir sur le site via le bouton "Retour"
- [ ] **Vérifications :**
  - [ ] Page d'attente affichée
  - [ ] Message "Paiement en cours de traitement"
  - [ ] Rafraîchissement automatique toutes les 10 secondes
  - [ ] Référence de paiement affichée
  - [ ] Commande reste en statut "pending"
  - [ ] Paiement reste en statut "pending"
  - [ ] Après 5 minutes, message d'alerte affiché

### 4. Test d'Annulation par l'Utilisateur
- [ ] Ajouter un cours au panier
- [ ] Procéder au checkout
- [ ] Initier le paiement
- [ ] Annuler le paiement sur la page Moneroo
- [ ] **Vérifications :**
  - [ ] Webhook reçoit le statut 'cancelled'
  - [ ] Commande marquée comme "cancelled"
  - [ ] Paiement marqué comme "failed"
  - [ ] Raison : "Annulation par l'utilisateur"
  - [ ] Email d'annulation reçu
  - [ ] Redirection vers la page d'annulation
  - [ ] Panier toujours présent
  - [ ] Possibilité de réessayer

### 5. Test de Délai Expiré
- [ ] Ajouter un cours au panier
- [ ] Procéder au checkout
- [ ] Initier le paiement
- [ ] Attendre l'expiration du délai (selon configuration Moneroo)
- [ ] **Vérifications :**
  - [ ] Webhook reçoit le statut 'expired'
  - [ ] Commande marquée comme "cancelled"
  - [ ] Paiement marqué comme "failed"
  - [ ] Raison : "Délai expiré"
  - [ ] Email d'expiration reçu
  - [ ] Log de l'expiration

### 6. Test de Paiement Rejeté (Carte Invalide)
- [ ] Ajouter un cours au panier
- [ ] Procéder au checkout
- [ ] Utiliser une carte invalide ou expirée
- [ ] **Vérifications :**
  - [ ] Webhook reçoit le statut 'rejected' ou 'failed'
  - [ ] Commande marquée comme "cancelled"
  - [ ] Paiement marqué comme "failed"
  - [ ] Raison d'échec détaillée enregistrée
  - [ ] Email d'échec reçu
  - [ ] Message d'erreur approprié affiché

---

## 🔍 Tests de Sécurité

### 7. Test de Manipulation de l'URL de Retour
- [ ] Ajouter un cours au panier
- [ ] Procéder au checkout
- [ ] Initier le paiement
- [ ] Modifier manuellement l'URL de retour pour forcer le succès
- [ ] **Vérifications :**
  - [ ] La vérification du statut auprès de Moneroo empêche la fraude
  - [ ] Commande reste "pending" si paiement non complété
  - [ ] Aucune inscription créée
  - [ ] Log de la tentative de manipulation

### 8. Test de Webhook Sans Signature
- [ ] Envoyer un webhook sans signature valide
- [ ] **Vérifications :**
  - [ ] Webhook rejeté (ou accepté avec warning selon config)
  - [ ] Log de sécurité créé
  - [ ] Aucune modification de la commande

### 9. Test de Double Paiement
- [ ] Compléter un paiement avec succès
- [ ] Tenter de payer à nouveau la même commande
- [ ] **Vérifications :**
  - [ ] Deuxième paiement refusé ou ignoré
  - [ ] Aucune inscription en double
  - [ ] Aucun email en double
  - [ ] Log de la tentative

---

## 🔄 Tests d'Idempotence

### 10. Test de Webhook en Double
- [ ] Compléter un paiement
- [ ] Simuler la réception du même webhook deux fois
- [ ] **Vérifications :**
  - [ ] Commande reste "paid" (pas de doublon)
  - [ ] Une seule inscription créée par cours
  - [ ] Un seul email envoyé
  - [ ] Log indiquant que la commande était déjà finalisée

### 11. Test de Redirection Multiple
- [ ] Compléter un paiement
- [ ] Revenir sur la page de succès plusieurs fois
- [ ] **Vérifications :**
  - [ ] Page de succès affichée à chaque fois
  - [ ] Aucune opération en double
  - [ ] Pas d'erreur affichée

---

## 📊 Tests de Logs et Monitoring

### 12. Vérification des Logs
- [ ] Pour chaque test ci-dessus, vérifier dans `storage/logs/laravel.log` :
  - [ ] Log d'initialisation du paiement
  - [ ] Log de réception du webhook
  - [ ] Log du statut vérifié
  - [ ] Log de finalisation (succès) ou d'annulation (échec)
  - [ ] Log d'envoi des emails
  - [ ] Payload complet enregistré pour les échecs

### 13. Vérification de la Base de Données
- [ ] Pour chaque test, vérifier dans la BDD :
  - [ ] Table `orders` : statut correct
  - [ ] Table `payments` : statut correct et raison d'échec si applicable
  - [ ] Table `enrollments` : créées uniquement si paiement réussi
  - [ ] Table `cart_items` : vidée uniquement si paiement réussi
  - [ ] Table `ambassador_commissions` : créée si code promo utilisé et paiement réussi

---

## 📧 Tests des Emails

### 14. Email de Confirmation de Paiement
- [ ] Reçu après paiement réussi
- [ ] Contient le numéro de commande
- [ ] Contient la liste des cours
- [ ] Contient les liens vers les cours
- [ ] Design correct et responsive

### 15. Email de Facture
- [ ] Reçu après paiement réussi
- [ ] Contient tous les détails de la commande
- [ ] Montant correct
- [ ] Format PDF attaché (si applicable)

### 16. Email d'Échec de Paiement
- [ ] Reçu après échec
- [ ] Contient la raison d'échec détaillée
- [ ] Contient des instructions pour réessayer
- [ ] Lien vers le support

### 17. Email d'Annulation
- [ ] Reçu après annulation
- [ ] Message approprié
- [ ] Lien pour retourner au panier

---

## 🌐 Tests Multi-Devises

### 18. Test avec USD
- [ ] Paiement en USD
- [ ] Montant correct affiché
- [ ] Conversion correcte (si applicable)
- [ ] Facture en USD

### 19. Test avec XOF (Franc CFA)
- [ ] Paiement en XOF
- [ ] Montant correct (pas de centimes)
- [ ] Affichage correct
- [ ] Facture en XOF

### 20. Test avec EUR
- [ ] Paiement en EUR
- [ ] Montant correct affiché
- [ ] Conversion correcte (si applicable)
- [ ] Facture en EUR

---

## 📱 Tests Responsive

### 21. Test sur Mobile
- [ ] Page de checkout responsive
- [ ] Page de succès responsive
- [ ] Page d'échec responsive
- [ ] Page d'attente responsive
- [ ] Emails responsive

### 22. Test sur Tablette
- [ ] Toutes les pages s'affichent correctement
- [ ] Navigation fluide

---

## ⚡ Tests de Performance

### 23. Test de Charge
- [ ] Simuler 10 paiements simultanés
- [ ] Vérifier que tous sont traités correctement
- [ ] Aucune perte de données
- [ ] Temps de réponse acceptable

### 24. Test de Timeout
- [ ] Simuler un timeout de l'API Moneroo
- [ ] Vérifier la gestion de l'erreur
- [ ] Message d'erreur approprié
- [ ] Possibilité de réessayer

---

## 🔧 Tests de Récupération

### 25. Test de Commande Bloquée
- [ ] Identifier une commande en "pending" depuis > 30 minutes
- [ ] Vérifier l'annulation automatique
- [ ] Email d'annulation automatique envoyé

### 26. Test de Webhook Manqué
- [ ] Simuler un webhook non reçu
- [ ] Vérifier que la vérification manuelle fonctionne
- [ ] Possibilité de synchroniser manuellement

---

## 📋 Checklist de Déploiement

### Avant le Déploiement
- [ ] Tous les tests ci-dessus passent
- [ ] Code review effectué
- [ ] Documentation à jour
- [ ] Variables d'environnement configurées
- [ ] Webhook URL configurée dans Moneroo
- [ ] Clés API testées en sandbox

### Après le Déploiement
- [ ] Test de paiement réel en production
- [ ] Vérification des logs en production
- [ ] Monitoring actif
- [ ] Alertes configurées
- [ ] Équipe support informée

---

## 📝 Notes de Test

### Environnement de Test
- **URL** : _____________________
- **Clé API Moneroo** : Sandbox / Production
- **Email de test** : _____________________
- **Numéro de test** : _____________________

### Résultats
- **Date du test** : _____________________
- **Testeur** : _____________________
- **Version** : _____________________
- **Statut global** : ✅ Réussi / ❌ Échoué

### Problèmes Identifiés
1. _____________________
2. _____________________
3. _____________________

### Actions Correctives
1. _____________________
2. _____________________
3. _____________________

---

## 🎓 Ressources

- [Documentation Moneroo](https://docs.moneroo.io/fr)
- [Guide des Corrections](./CORRECTIONS_STATUTS_PAIEMENT.md)
- [Résumé des Corrections](./RESUME_CORRECTIONS_PAIEMENT.md)
- [Logs Laravel](storage/logs/laravel.log)

---

**Dernière mise à jour** : {{ date('d/m/Y H:i') }}

