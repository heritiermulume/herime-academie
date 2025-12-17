# 🔄 Système de Fallback Moneroo

## 📋 Vue d'ensemble

Pour garantir la **disponibilité maximale** du système de retrait Wallet, un **système de fallback** a été mis en place avec des données statiques. Cela permet aux ambassadeurs de continuer à utiliser le système même si l'API Moneroo est temporairement indisponible.

## 🎯 Quand le Fallback est Utilisé

Le système utilise automatiquement les données par défaut dans ces cas :

### 1️⃣ **API Key Manquante**
```
Condition: MONEROO_API_KEY non configurée dans .env
Action: Utilisation immédiate des données par défaut
Log: "MONEROO_API_KEY non configurée. Utilisation des données par défaut."
```

### 2️⃣ **API Moneroo Échoue**
```
Condition: L'endpoint /payouts/methods renvoie une erreur (4xx, 5xx)
Action: Utilisation des données par défaut
Log: "Échec de la récupération de la configuration Moneroo. Utilisation des données par défaut."
Détails loggés: status code, response body
```

### 3️⃣ **Exception Réseau**
```
Condition: Timeout, connexion refusée, DNS failure, etc.
Action: Utilisation des données par défaut
Log: "Erreur lors de la récupération de la configuration Moneroo. Utilisation des données par défaut."
Détails loggés: message d'erreur, stack trace
```

## 📊 Données Incluses dans le Fallback

### 🌍 **10 Pays Africains**

| Pays | Code | Préfixe | Devise | Opérateurs |
|------|------|---------|--------|------------|
| 🇨🇩 RDC | CD | +243 | USD, CDF | 4 opérateurs |
| 🇨🇲 Cameroun | CM | +237 | XAF | 2 opérateurs |
| 🇨🇮 Côte d'Ivoire | CI | +225 | XOF | 4 opérateurs |
| 🇸🇳 Sénégal | SN | +221 | XOF | 3 opérateurs |
| 🇲🇱 Mali | ML | +223 | XOF | - |
| 🇧🇯 Bénin | BJ | +229 | XOF | - |
| 🇬🇭 Ghana | GH | +233 | GHS | 3 opérateurs |
| 🇰🇪 Kenya | KE | +254 | KES | 2 opérateurs |
| 🇷🇼 Rwanda | RW | +250 | RWF | 2 opérateurs |
| 🇺🇬 Ouganda | UG | +256 | UGX | - |

### 📱 **20+ Opérateurs Mobile Money**

#### 🇨🇩 **République Démocratique du Congo**
- ✅ Vodacom M-Pesa (USD, CDF)
- ✅ Airtel Money (USD, CDF)
- ✅ Orange Money (USD, CDF)
- ✅ Africell Money (USD, CDF)

#### 🇨🇲 **Cameroun**
- ✅ MTN Mobile Money (XAF)
- ✅ Orange Money (XAF)

#### 🇨🇮 **Côte d'Ivoire**
- ✅ MTN Mobile Money (XOF)
- ✅ Orange Money (XOF)
- ✅ Moov Money (XOF)
- ✅ Wave (XOF)

#### 🇸🇳 **Sénégal**
- ✅ Orange Money (XOF)
- ✅ Free Money (XOF)
- ✅ Wave (XOF)

#### 🇬🇭 **Ghana**
- ✅ MTN Mobile Money (GHS)
- ✅ Vodafone Cash (GHS)
- ✅ AirtelTigo Money (GHS)

#### 🇰🇪 **Kenya**
- ✅ M-Pesa (KES)
- ✅ Airtel Money (KES)

#### 🇷🇼 **Rwanda**
- ✅ MTN Mobile Money (RWF)
- ✅ Airtel Money (RWF)

### 💱 **6 Devises Supportées**

- 💵 **USD** - Dollar américain (RDC)
- 💵 **CDF** - Franc congolais (RDC)
- 💵 **XAF** - Franc CFA BEAC (Cameroun)
- 💵 **XOF** - Franc CFA BCEAO (CI, SN, ML, BJ)
- 💵 **GHS** - Cedi ghanéen (Ghana)
- 💵 **KES** - Shilling kenyan (Kenya)
- 💵 **RWF** - Franc rwandais (Rwanda)
- 💵 **UGX** - Shilling ougandais (Ouganda)

## 🔍 Vérifier l'État du Système

### Méthode 1 : Via les Logs

```bash
# Se connecter au serveur
ssh user@votre-serveur.com

# Rechercher les logs Moneroo
tail -f storage/logs/laravel.log | grep "Moneroo"

# Voir si le fallback est utilisé
grep "Utilisation des données par défaut" storage/logs/laravel.log

# Voir les erreurs API
grep "Échec de la récupération de la configuration Moneroo" storage/logs/laravel.log
```

### Méthode 2 : Via l'Interface

1. Accédez à `/wallet/payout/create`
2. Si vous voyez **10 pays** dans le dropdown → ✅ Fallback actif
3. Si vous voyez **moins de pays** → API Moneroo fonctionne (réponse réelle)

### Méthode 3 : Tester l'API Directement

```bash
# Tester l'endpoint Moneroo
curl -H "Authorization: Bearer VOTRE_API_KEY" \
     -H "Accept: application/json" \
     https://api.moneroo.io/v1/payouts/methods
```

## 📊 Logs Détaillés

### En Cas de Succès API

```
Aucun log (système silencieux)
Les données viennent de l'API Moneroo
```

### En Cas d'Échec API

```
[2025-12-17 15:30:45] production.WARNING: Échec de la récupération de la configuration Moneroo. Utilisation des données par défaut.
{
    "status": 401,
    "response": "{\"error\":\"Invalid API key\"}"
}
```

### En Cas d'Exception

```
[2025-12-17 15:30:45] production.ERROR: Erreur lors de la récupération de la configuration Moneroo. Utilisation des données par défaut.
{
    "error": "Connection timed out after 10000 milliseconds"
}
```

## 🛠️ Résolution des Problèmes

### Problème 1 : API Key Invalide

**Symptôme** : Logs montrent `"Invalid API key"`

**Solution** :
```bash
# Vérifier l'API key dans .env
grep MONEROO_API_KEY .env

# Si manquante ou invalide, la mettre à jour
# Puis redémarrer l'application
php artisan config:clear
php artisan cache:clear
```

### Problème 2 : Timeout

**Symptôme** : Logs montrent `"Connection timed out"`

**Solution** :
- Vérifier la connectivité réseau du serveur
- Vérifier que `api.moneroo.io` est accessible
- Augmenter le timeout si nécessaire (dans `Http::timeout(30)`)

### Problème 3 : API Moneroo Down

**Symptôme** : Logs montrent erreurs 5xx (500, 502, 503)

**Solution** :
- ✅ Aucune action requise ! Le fallback fonctionne
- Les ambassadeurs peuvent continuer à utiliser le système
- Moneroo traitera les payouts quand l'API sera rétablie
- Surveiller le statut : https://status.moneroo.io (hypothétique)

## ✅ Avantages du Système de Fallback

### 🚀 **Disponibilité Maximale**
- Le système reste **100% fonctionnel** même si l'API Moneroo est down
- Aucune interruption de service pour les utilisateurs

### 🔒 **Sécurité**
- Les payouts sont toujours envoyés via l'API Moneroo
- Le fallback ne concerne que l'affichage des options
- Validation complète maintenue

### 📊 **Traçabilité**
- Tous les cas d'utilisation du fallback sont loggés
- Facile de diagnostiquer les problèmes API
- Métriques sur la disponibilité de l'API

### 🎯 **Expérience Utilisateur**
- Pas de message d'erreur frustrant
- Formulaire toujours accessible
- Processus de retrait fluide

## 🔄 Mise à Jour des Données de Fallback

Si de nouveaux pays/opérateurs sont ajoutés à Moneroo, mettre à jour :

```php
// app/Http/Controllers/WalletController.php
private function getDefaultMonerooData(): array
{
    return [
        'countries' => [
            // Ajouter un nouveau pays
            [
                'code' => 'TZ',
                'name' => 'Tanzanie',
                'prefix' => '+255',
                'flag' => '🇹🇿',
                'currency' => 'TZS',
            ],
        ],
        'providers' => [
            // Ajouter un nouvel opérateur
            [
                'code' => 'tigo_pesa',
                'name' => 'Tigo Pesa',
                'country' => 'TZ',
                'currencies' => ['TZS'],
                'currency' => 'TZS',
                'logo' => '',
            ],
        ],
    ];
}
```

## 📞 Support

Pour toute question :
- Email : academie@herime.com
- Logs : `storage/logs/laravel.log`
- Documentation Moneroo : https://docs.moneroo.io

---

**Date de création** : 17 Décembre 2025  
**Version** : 1.0

