# ⚙️ Configuration Dynamique du Wallet

## 📋 Vue d'ensemble

Les paramètres du système Wallet sont **configurables en temps réel** depuis l'interface d'administration, sans besoin de modifier le code ou redémarrer l'application.

## 🎯 Accès à la Configuration

### Via l'Interface Admin

1. Connectez-vous en tant qu'administrateur
2. Allez dans **Administration → Paramètres**
3. Faites défiler jusqu'à la section **"Configuration du Wallet Ambassadeurs"**

**URL directe** : `https://academie.herime.com/admin/settings`

## 🔧 Paramètres Configurables

### 1. **Période de Blocage (Holding Period)**

- **Nom** : `wallet_holding_period_days`
- **Type** : Nombre entier
- **Unité** : Jours
- **Plage** : 0 à 365 jours
- **Par défaut** : 7 jours

**Description** : Durée pendant laquelle les nouveaux gains sont bloqués avant d'être disponibles au retrait.

**Recommandations** :
- ✅ **7 jours** : Standard pour la plupart des plateformes
- ✅ **14 jours** : Sécurité renforcée
- ✅ **30 jours** : Maximum de sécurité pour activités à risque
- ⚠️ **0 jour** : Aucun blocage (non recommandé sauf cas spécifiques)

**Impact** :
- Période courte = Ambassadeurs satisfaits mais risque accru
- Période longue = Sécurité maximale mais peut frustrer les ambassadeurs

### 2. **Montant Minimum de Retrait**

- **Nom** : `wallet_minimum_payout_amount`
- **Type** : Nombre décimal
- **Unité** : Devise de base du site (USD, CDF, etc.)
- **Plage** : 0 et plus
- **Par défaut** : 5

**Description** : Montant minimum que les ambassadeurs doivent avoir pour effectuer un retrait.

**Recommandations** :
- ✅ **5-10** : Raisonnable pour la plupart des devises
- ✅ **1** : Très accessible pour les ambassadeurs
- ⚠️ **50+** : Peut décourager les petits ambassadeurs

**Impact** :
- Montant bas = Plus de retraits fréquents (coûts de transaction)
- Montant élevé = Moins de retraits mais frustration possible

### 3. **Libération Automatique**

- **Nom** : `wallet_auto_release_enabled`
- **Type** : Booléen (Oui/Non)
- **Par défaut** : Activé

**Description** : Active ou désactive la libération automatique des fonds bloqués après la période de blocage.

**Recommandations** :
- ✅ **Activé** : Expérience utilisateur optimale (automatique)
- ⚠️ **Désactivé** : Nécessite une libération manuelle (plus de contrôle)

**Impact** :
- Activé = Les fonds sont automatiquement libérés (recommandé)
- Désactivé = Nécessite une action admin pour libérer les fonds

## 📊 Exemple de Configuration

### Configuration Standard (Recommandée)

```
Période de blocage : 7 jours
Montant minimum : 5 USD
Libération automatique : ✅ Activée
```

### Configuration Sécurisée

```
Période de blocage : 14 jours
Montant minimum : 10 USD
Libération automatique : ✅ Activée
```

### Configuration Flexible

```
Période de blocage : 3 jours
Montant minimum : 1 USD
Libération automatique : ✅ Activée
```

## 🔄 Modification des Paramètres

### Étapes

1. Accédez à **Admin → Paramètres**
2. Modifiez les valeurs dans la section **"Configuration du Wallet"**
3. Cliquez sur **"Enregistrer les modifications"**
4. ✅ Les changements sont **immédiatement effectifs**

### Points Importants

- ✅ **Pas de redémarrage nécessaire** : Les changements sont instantanés
- ✅ **Traçabilité** : Les modifications sont enregistrées dans la base de données
- ✅ **Valeurs par défaut** : Si un paramètre n'est pas défini, une valeur par défaut est utilisée
- ⚠️ **Impact immédiat** : Les nouveaux crédits utiliseront les nouveaux paramètres

### Que se passe-t-il lors d'une modification ?

#### Changement de la Période de Blocage

- **Holds existants** : Conservent leur période initiale
- **Nouveaux crédits** : Utiliseront la nouvelle période

**Exemple** :
- Avant : 7 jours
- Après : 14 jours
- Résultat : Les fonds déjà bloqués seront libérés selon l'ancien délai (7 jours), les nouveaux selon le nouveau délai (14 jours)

#### Changement du Montant Minimum

- **Retraits en cours** : Non affectés
- **Nouveaux retraits** : Doivent respecter le nouveau minimum

**Exemple** :
- Avant : 5 USD
- Après : 10 USD
- Résultat : Les ambassadeurs devront avoir au moins 10 USD pour initier un nouveau retrait

#### Désactivation de la Libération Automatique

- **Holds en attente** : Ne seront plus libérés automatiquement
- **Action requise** : Libération manuelle via commande artisan

```bash
php artisan wallet:release-holds
```

## 🛠️ Commandes Admin Utiles

### Initialiser les Paramètres

Si les paramètres n'existent pas dans la base de données :

```bash
php artisan wallet:init-settings
```

### Libérer Manuellement les Fonds

Pour forcer la libération de tous les fonds éligibles :

```bash
# Libération réelle
php artisan wallet:release-holds

# Mode simulation (voir sans appliquer)
php artisan wallet:release-holds --dry-run

# Forcer la libération même si pas encore expiré
php artisan wallet:release-holds --force
```

## 📱 Impact sur l'Expérience Utilisateur

### Ce que Voient les Ambassadeurs

#### Dashboard Wallet

```
┌─────────────────────────────────────┐
│ Disponible au retrait    50.00 USD  │
│ En période de blocage    25.00 USD  │
│ Solde total              75.00 USD  │
└─────────────────────────────────────┘

💡 Pourquoi certains fonds sont-ils bloqués ?
Pour garantir la sécurité des transactions, les nouveaux 
gains sont bloqués pendant 7 jours avant d'être disponibles 
au retrait.
```

**Note** : Le nombre de jours affiché correspond au paramètre configuré.

#### Formulaire de Retrait

```
Solde disponible au retrait
    100.00 USD

Montant minimum de retrait : 5 USD

🔒 25.00 USD en période de blocage
```

### Messages d'Erreur Dynamiques

Si un ambassadeur tente de retirer avec un solde insuffisant :

```
❌ Solde disponible insuffisant. 
Vous avez 3.00 USD disponibles, mais vous essayez de 
retirer 5.00 USD.

Vous avez 10.00 USD en période de blocage qui seront 
bientôt disponibles.
```

## 🔐 Sécurité et Validation

### Validation des Valeurs

- **Période de blocage** : Entre 0 et 365 jours
- **Montant minimum** : 0 ou plus, décimal à 2 chiffres
- **Libération auto** : true ou false uniquement

### Protection des Données

- ✅ Les paramètres sont stockés de manière sécurisée dans la base de données
- ✅ Seuls les administrateurs peuvent modifier ces paramètres
- ✅ Les modifications sont tracées

## 📞 Support

Pour toute question sur la configuration :
- Email : academie@herime.com
- Documentation Moneroo : https://docs.moneroo.io

---

**Date de création** : 17 Décembre 2025  
**Version** : 1.0

