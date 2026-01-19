# Système d'Ambassadeur - Documentation Complète

## ✅ Composants Implémentés

### 1. Base de Données
- ✅ `ambassador_applications` - Candidatures d'ambassadeur
- ✅ `ambassadors` - Ambassadeurs confirmés
- ✅ `ambassador_promo_codes` - Codes promo des ambassadeurs
- ✅ `ambassador_commissions` - Commissions gagnées
- ✅ Champs ajoutés à `orders` : `ambassador_id`, `ambassador_promo_code_id`

### 2. Modèles Eloquent
- ✅ `AmbassadorApplication` - Gestion des candidatures
- ✅ `Ambassador` - Gestion des ambassadeurs
- ✅ `AmbassadorPromoCode` - Gestion des codes promo
- ✅ `AmbassadorCommission` - Gestion des commissions
- ✅ Relations ajoutées au modèle `Order`

### 3. Contrôleurs
- ✅ `AmbassadorApplicationController` - Candidatures utilisateurs
- ✅ `Admin\AmbassadorController` - Gestion admin complète
- ✅ Intégration dans `PawaPayController` pour validation des codes promo

### 4. Notifications
- ✅ `AmbassadorApplicationStatusUpdated` - Notification de changement de statut

### 5. Routes
- ✅ Routes publiques pour candidatures
- ✅ Routes authentifiées pour gestion
- ✅ Routes admin complètes

### 6. Paramètres
- ✅ Paramètre `ambassador_commission_rate` dans les settings admin
- ✅ Interface admin pour configurer le pourcentage de commission

### 7. Logique Métier
- ✅ Validation des codes promo lors du checkout
- ✅ Calcul automatique des commissions lors des achats
- ✅ Génération automatique de code promo lors de l'approbation
- ✅ Suivi des statistiques (références, ventes, gains)

## 📋 Fonctionnalités

### Pour les Utilisateurs
1. **Postuler pour devenir ambassadeur**
   - Route: `/become-ambassador`
   - Formulaire de candidature avec:
     - Téléphone
     - Motivation
     - Expérience
     - Présence sur les réseaux sociaux
     - Audience cible
     - Idées marketing

2. **Suivre le statut de candidature**
   - Route: `/ambassador-application/{application}/status`
   - Statuts: pending, under_review, approved, rejected

### Pour les Ambassadeurs (après approbation)
1. **Code promo unique** généré automatiquement
2. **Dashboard ambassadeur** (à créer)
   - Voir le code promo
   - Statistiques (références, ventes, gains)
   - Historique des commissions

### Pour l'Administration
1. **Gestion des candidatures**
   - Route: `/admin/ambassadors/applications`
   - Voir toutes les candidatures
   - Approuver/Rejeter avec notes
   - Génération automatique du code promo à l'approbation

2. **Gestion des ambassadeurs**
   - Route: `/admin/ambassadors`
   - Liste des ambassadeurs
   - Activer/Désactiver
   - Générer de nouveaux codes promo
   - Voir les statistiques

3. **Gestion des commissions**
   - Route: `/admin/ambassadors/commissions`
   - Liste de toutes les commissions
   - Approuver les commissions
   - Marquer comme payées
   - Filtres par statut et ambassadeur

4. **Paramètres**
   - Route: `/admin/settings`
   - Configurer le pourcentage de commission (défaut: 10%)

## 🔄 Flux de Fonctionnement

### 1. Candidature
```
Utilisateur → Postule → Admin examine → Approuve → Ambassadeur créé + Code promo généré
```

### 2. Utilisation du Code Promo
```
Client → Utilise code promo au checkout → Commande créée avec ambassador_id
→ Paiement confirmé → Commission créée (status: pending)
→ Admin approuve → Commission status: approved
→ Admin marque comme payée → Commission status: paid + Gains ajoutés à l'ambassadeur
```

### 3. Calcul de Commission
```
Montant commande × Pourcentage configuré = Commission
Exemple: 100€ × 10% = 10€ de commission
```

## 📝 Vues à Créer

### Vues Utilisateur
1. `resources/views/ambassador-application/index.blade.php`
   - Page d'information sur le programme ambassadeur
   - Lien vers le formulaire de candidature

2. `resources/views/ambassador-application/create.blade.php`
   - Formulaire de candidature

3. `resources/views/ambassador-application/status.blade.php`
   - Statut de la candidature

### Vues Admin
1. `resources/views/admin/ambassadors/applications/index.blade.php`
   - Liste des candidatures avec filtres

2. `resources/views/admin/ambassadors/applications/show.blade.php`
   - Détails d'une candidature
   - Formulaire d'approbation/rejet

3. `resources/views/admin/ambassadors/index.blade.php`
   - Liste des ambassadeurs

4. `resources/views/admin/ambassadors/show.blade.php`
   - Détails d'un ambassadeur
   - Statistiques et commissions

5. `resources/views/admin/ambassadors/commissions/index.blade.php`
   - Liste des commissions

### Dashboard Ambassadeur
1. `resources/views/ambassador/dashboard.blade.php`
   - Code promo
   - Statistiques
   - Historique des commissions

## 🔧 Intégration Checkout

Le code promo d'ambassadeur peut être utilisé lors du checkout. Il doit être passé dans la requête:
```javascript
{
    amount: 100,
    currency: 'USD',
    phoneNumber: '+1234567890',
    provider: 'mtn',
    country: 'CI',
    ambassador_promo_code: 'AMB123456' // Code promo optionnel
}
```

## 📊 Statistiques Suivies

Pour chaque ambassadeur:
- `total_earnings` - Total des gains
- `pending_earnings` - Gains en attente
- `paid_earnings` - Gains payés
- `total_referrals` - Nombre de références
- `total_sales` - Nombre de ventes

## 🎯 Prochaines Étapes

1. Créer les vues (utiliser les vues d'instructeur comme référence)
2. Créer le dashboard ambassadeur
3. Ajouter le champ code promo dans le formulaire de checkout
4. Tester le flux complet
5. Ajouter des notifications email pour les ambassadeurs

## 📌 Notes Importantes

- Le code promo ne donne PAS de réduction au client
- Il sert uniquement à attribuer la commission à l'ambassadeur
- Les commissions sont créées avec le statut "pending" par défaut
- L'admin doit approuver puis marquer comme payées
- Le pourcentage de commission est configurable dans les settings

## 🔐 Sécurité

- Validation SSO pour toutes les actions de modification
- Vérification que l'ambassadeur est actif avant attribution de commission
- Validation du code promo avant association à la commande
- Protection contre les doublons de commissions












