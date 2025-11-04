# 🧪 Guide de Test SSO

Ce guide vous permet de tester et vérifier que l'intégration SSO fonctionne correctement.

## 📋 Checklist de Vérification

### 1. Configuration de Base

- [ ] Fichier `.env` mis à jour avec les variables SSO
- [ ] `SSO_ENABLED=true`
- [ ] `SSO_BASE_URL=https://compte.herime.com`
- [ ] `SSO_SECRET=1d69dac265aab9b5633e96af6f2e4f27f082824f1512b2f7a047bf8f4365e3db`
- [ ] `SSO_TIMEOUT=10`

### 2. Vérification Automatique

#### Option A: Script Shell

```bash
./scripts/check-sso-config.sh
```

#### Option B: Commande Artisan

```bash
php artisan sso:test
```

Cette commande vérifie :
- ✅ Configuration complète
- ✅ Connexion à l'API SSO
- ✅ URLs générées
- ✅ Validation de token (si fourni)

#### Option C: Test avec Token

```bash
php artisan sso:test --token="votre_token_jwt_ici"
```

### 3. Test Manuel

#### Étape 1: Vérifier la Configuration

```bash
php artisan tinker
```

Dans tinker, exécutez :

```php
config('services.sso.enabled')
config('services.sso.base_url')
config('services.sso.secret')
config('services.sso.timeout')
```

Tous doivent retourner les valeurs attendues.

#### Étape 2: Tester la Redirection

1. Visitez `https://academie.herime.com/login`
2. Vous devriez être automatiquement redirigé vers `compte.herime.com/login?redirect=...`
3. Vérifiez que l'URL de callback est correcte dans le paramètre `redirect`

#### Étape 3: Tester le Flux Complet

1. **Sur academie.herime.com** :
   - Visitez une page protégée (ex: `/dashboard`)
   - Vous devriez être redirigé vers `compte.herime.com/login`

2. **Sur compte.herime.com** :
   - Connectez-vous avec vos identifiants
   - Après connexion, vous devriez être redirigé vers `academie.herime.com/sso/callback?token=...`

3. **Retour sur academie.herime.com** :
   - Le token est validé automatiquement
   - Vous êtes connecté
   - Vous êtes redirigé vers la page demandée

#### Étape 4: Tester la Déconnexion

1. Sur academie.herime.com, cliquez sur "Déconnexion"
2. Vous devriez être redirigé vers `compte.herime.com/logout?redirect=https://academie.herime.com`
3. La session est invalidée sur tous les sites

## 🔍 Vérification des Logs

### Consulter les Logs

```bash
tail -f storage/logs/laravel.log
```

### Rechercher les Entrées SSO

```bash
grep "SSO" storage/logs/laravel.log
```

### Messages Attendus

**Succès :**
```
SSO login successful
SSO Token Validation Successful
SSO user created
```

**Erreurs :**
```
SSO credentials not configured
SSO Token Validation Failed
SSO callback error
SSO Token Validation Exception
```

## 🧪 Test de l'API Directement

### Avec curl

```bash
curl -X POST https://compte.herime.com/api/validate-token \
  -H "Authorization: Bearer 1d69dac265aab9b5633e96af6f2e4f27f082824f1512b2f7a047bf8f4365e3db" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"token": "votre_token_de_test"}'
```

### Réponse Attendue

**Token valide :**
```json
{
  "valid": true,
  "user": {
    "id": 1,
    "email": "user@herime.com",
    "name": "John Doe",
    "role": "student",
    "is_verified": true,
    "is_active": true
  }
}
```

**Token invalide :**
```json
{
  "valid": false,
  "message": "Token invalide ou expiré"
}
```

## 🐛 Dépannage

### Problème: "SSO credentials not configured"

**Solution :**
1. Vérifiez que les variables sont dans `.env`
2. Exécutez `php artisan config:clear`
3. Exécutez `php artisan config:cache` (en production)

### Problème: "Token SSO invalide"

**Solutions :**
1. Vérifiez que `SSO_SECRET` est identique sur les deux sites
2. Testez l'endpoint `/api/validate-token` directement avec curl
3. Vérifiez que le token n'a pas expiré
4. Consultez les logs pour plus de détails

### Problème: Redirection en boucle

**Solutions :**
1. Vérifiez que `SSO_ENABLED=true` (ou `false` pour désactiver)
2. Vérifiez que l'URL de callback est correcte
3. Vérifiez les logs pour les erreurs

### Problème: "Endpoint non trouvé (404)"

**Solutions :**
1. Vérifiez que l'endpoint `/api/validate-token` existe sur compte.herime.com
2. Vérifiez que `SSO_BASE_URL` est correct
3. Testez l'endpoint avec curl directement

### Problème: Utilisateur non créé

**Solutions :**
1. Vérifiez que l'email est fourni dans la réponse de l'API
2. Vérifiez les logs pour les erreurs de création
3. Vérifiez que la base de données est accessible

## ✅ Validation Finale

Une fois tous les tests passés, vérifiez :

- [ ] Configuration correcte dans `.env`
- [ ] Commande `php artisan sso:test` réussie
- [ ] Redirection vers compte.herime.com fonctionne
- [ ] Connexion SSO fonctionne
- [ ] Utilisateur créé/mis à jour automatiquement
- [ ] Déconnexion SSO fonctionne
- [ ] Logs sans erreurs critiques

## 📞 Support

Si vous rencontrez des problèmes :

1. Consultez les logs : `storage/logs/laravel.log`
2. Exécutez les tests : `php artisan sso:test`
3. Vérifiez la configuration : `./scripts/check-sso-config.sh`
4. Testez l'API directement avec curl

