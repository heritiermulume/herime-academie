# 🔧 Configuration API Moneroo - Problème Identifié

## ⚠️ Problème Actuel

Les endpoints testés pour récupérer les méthodes de payout renvoient **404 Not Found** :

```
❌ /payouts/available-methods → 404
❌ /payouts/methods → 404
```

**Réponse de l'API** :
```json
{
    "message": "Payout transaction not found",
    "data": null,
    "errors": []
}
```

## 🔍 Diagnostic

### Configuration Vérifiée
- ✅ `MONEROO_API_KEY` est présente et commence par `pvk_`
- ✅ Base URL correcte : `https://api.moneroo.io/v1`
- ✅ Headers corrects (Authorization, Content-Type, Accept)
- ✅ API Key valide (pas d'erreur 401)

### Problème Identifié
L'API Moneroo ne fournit **PAS** d'endpoint public pour lister les méthodes de payout disponibles selon leur structure actuelle.

## 📖 Documentation Moneroo

Selon la documentation Moneroo (https://docs.moneroo.io/fr/payouts), les endpoints disponibles sont :

### Pour Payments (Reçus)
- `POST /payments` - Initialiser un paiement
- `GET /payments/{id}` - Récupérer un paiement
- `GET /payments` - Lister les paiements

### Pour Payouts (Envois)
- `POST /payouts` - Initialiser un transfert
- `GET /payouts/{id}` - Récupérer un transfert
- `GET /payouts` - Lister les transferts

**❌ Aucun endpoint pour lister les méthodes disponibles**

## 🔧 Solutions Possibles

### Solution 1 : Données Statiques (Recommandée)
Utiliser une liste statique des méthodes de payout supportées par Moneroo, mise à jour manuellement.

**Avantages** :
- ✅ Fonctionne immédiatement
- ✅ Pas de dépendance API
- ✅ Performance optimale

**Inconvénients** :
- ⚠️ Nécessite mise à jour manuelle si Moneroo ajoute de nouveaux pays/opérateurs

### Solution 2 : Endpoint Custom/Privé
Contacter Moneroo pour obtenir un endpoint dédié ou une API Key spécifique avec accès aux méthodes.

**Avantages** :
- ✅ Données toujours à jour
- ✅ Aucune maintenance

**Inconvénients** :
- ⚠️ Nécessite contact avec Moneroo
- ⚠️ Peut ne pas être disponible

### Solution 3 : Configuration Locale
Stocker la liste des méthodes dans la base de données avec une interface admin pour les gérer.

**Avantages** :
- ✅ Flexible
- ✅ Configurable par l'admin
- ✅ Pas de dépendance externe

**Inconvénients** :
- ⚠️ Plus complexe à implémenter
- ⚠️ Nécessite interface admin

## 🚀 Prochaines Étapes

### Action Immédiate
1. **Contacter Moneroo Support** :
   - Email : support@moneroo.io
   - Question : "Comment récupérer la liste des méthodes de payout disponibles via l'API ?"
   - Indiquer que les endpoints `/payouts/methods` et `/payouts/available-methods` renvoient 404

2. **En attendant**, réimplémenter les données statiques avec les méthodes connues supportées par Moneroo

### Tests à Effectuer

```bash
# Tester la connexion API
php artisan moneroo:test-api

# Vérifier les logs Laravel
tail -f storage/logs/laravel.log | grep "Moneroo"

# Tester manuellement avec curl
curl -H "Authorization: Bearer pvk_VOTRE_CLE" \
     -H "Accept: application/json" \
     https://api.moneroo.io/v1/payouts/methods
```

## 📊 Méthodes Connues Supportées par Moneroo

D'après la documentation, Moneroo supporte :

### 🌍 Pays Confirmés
- 🇨🇩 RDC (République Démocratique du Congo)
- 🇨🇲 Cameroun
- 🇨🇮 Côte d'Ivoire
- 🇸🇳 Sénégal
- 🇧🇯 Bénin
- 🇧🇫 Burkina Faso
- 🇲🇱 Mali
- 🇳🇪 Niger
- 🇹🇬 Togo
- 🇰🇪 Kenya
- 🇷🇼 Rwanda
- 🇺🇬 Ouganda
- 🇹🇿 Tanzanie
- 🇬🇭 Ghana
- 🇳🇬 Nigeria

### 📱 Opérateurs Mobile Money Confirmés
- Vodacom M-Pesa (RDC)
- Airtel Money (multi-pays)
- Orange Money (multi-pays)
- MTN Mobile Money (multi-pays)
- M-Pesa (Kenya, Tanzanie)
- Moov Money
- Wave
- Free Money (Sénégal)

## 📞 Contact Moneroo

**Support** : support@moneroo.io  
**Documentation** : https://docs.moneroo.io  
**Dashboard** : https://dashboard.moneroo.io

## 💡 Solution Temporaire Implémentée

✅ **Données statiques réimplémentées** avec les méthodes connues supportées par Moneroo.

Le système fonctionne maintenant de la façon suivante :

### Comportement Actuel

```
1. Tentative d'appel API Moneroo
   ↓
2. Si API répond avec succès → Utiliser les données de l'API
   ↓
3. Si API échoue (404, timeout, etc.) → Utiliser les données statiques
   ↓
4. Log warning pour traçabilité
   ↓
5. Formulaire fonctionne normalement
```

### Données Statiques Incluses

✅ **15 Pays Africains**  
✅ **35+ Opérateurs Mobile Money**  
✅ **7 Devises** (USD, CDF, XAF, XOF, GHS, NGN, KES, RWF, UGX, TZS)

### Code Source

La méthode `getStaticMonerooMethods()` dans `WalletController.php` contient :
- Liste complète des pays supportés
- Tous les opérateurs Mobile Money connus
- Commentaires `// TEMPORAIRE` pour faciliter le remplacement futur

### Prochaines Actions

Une fois que Moneroo fournira l'endpoint correct :
1. Remplacer l'URL de l'endpoint dans `getMonerooConfiguration()`
2. Supprimer la méthode `getStaticMonerooMethods()`
3. Retirer les commentaires `// TEMPORAIRE`
4. Tester que les données de l'API sont correctes

### Recherche de Code

Pour identifier facilement le code temporaire à remplacer :

```bash
# Trouver tous les commentaires TEMPORAIRE
grep -r "TEMPORAIRE" app/Http/Controllers/WalletController.php

# Résultat attendu :
# - Line X: // TEMPORAIRE: Utiliser les données statiques...
# - Line Y: * TEMPORAIRE: Données statiques des méthodes Moneroo
```

---

**Date** : 17 Décembre 2025  
**Status** : 🟢 Système fonctionnel avec données statiques (temporaire)  
**Action requise** : Contacter Moneroo pour obtenir le bon endpoint

