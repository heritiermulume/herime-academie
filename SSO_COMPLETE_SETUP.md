# ✅ Guide Complet de Configuration SSO

Ce guide vous accompagne étape par étape pour finaliser la configuration SSO.

## 📝 Étape 1: Configuration du fichier .env

Ouvrez votre fichier `.env` et ajoutez ces lignes :

```env
# ============================================
# Configuration SSO (Single Sign-On)
# ============================================
SSO_ENABLED=true
SSO_BASE_URL=https://compte.herime.com
SSO_SECRET=1d69dac265aab9b5633e96af6f2e4f27f082824f1512b2f7a047bf8f4365e3db
SSO_TIMEOUT=10
```

**⚠️ Important :**
- La clé `SSO_SECRET` doit être **identique** sur compte.herime.com et academie.herime.com
- Assurez-vous qu'il n'y a pas d'espaces avant ou après les valeurs
- Utilisez des guillemets si vos valeurs contiennent des caractères spéciaux

## 🔍 Étape 2: Vérifier la Configuration

### Option A: Script Shell (Recommandé)

```bash
chmod +x scripts/check-sso-config.sh
./scripts/check-sso-config.sh
```

### Option B: Commande Artisan

```bash
php artisan config:clear
php artisan sso:test
```

Vous devriez voir :
```
✅ SSO_ENABLED: Activé
✅ SSO_BASE_URL: https://compte.herime.com
✅ SSO_SECRET: Configuré (64 caractères)
✅ SSO_TIMEOUT: 10 secondes
✅ Connexion à l'API réussie
```

## 🧪 Étape 3: Test de l'Endpoint API

Vérifiez que l'endpoint sur compte.herime.com fonctionne :

```bash
curl -X POST https://compte.herime.com/api/validate-token \
  -H "Authorization: Bearer 1d69dac265aab9b5633e96af6f2e4f27f082824f1512b2f7a047bf8f4365e3db" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"token": "test_token"}'
```

**Réponse attendue :**
- Si le token est invalide : `{"valid": false, "message": "..."}`
- Si le token est valide : `{"valid": true, "user": {...}}`

## 🌐 Étape 4: Test du Flux Complet

### 4.1 Test de Redirection

1. Ouvrez votre navigateur en mode navigation privée
2. Visitez : `https://academie.herime.com/login`
3. **Résultat attendu :** Vous êtes redirigé vers `compte.herime.com/login?redirect=...`

### 4.2 Test de Connexion

1. Sur compte.herime.com, connectez-vous avec vos identifiants
2. Après connexion, vous devriez être redirigé vers `academie.herime.com/sso/callback?token=...`
3. **Résultat attendu :** Vous êtes automatiquement connecté sur academie.herime.com

### 4.3 Test de Déconnexion

1. Sur academie.herime.com, cliquez sur "Déconnexion"
2. **Résultat attendu :** Vous êtes redirigé vers `compte.herime.com/logout?redirect=...`
3. La session est invalidée sur tous les sites

## 🔍 Étape 5: Vérification des Logs

Consultez les logs pour vérifier que tout fonctionne :

```bash
tail -f storage/logs/laravel.log | grep SSO
```

**Messages de succès attendus :**
- `SSO login successful`
- `SSO user created` (première connexion)
- `SSO Token Validation Successful`

**Messages d'erreur à surveiller :**
- `SSO credentials not configured` → Vérifiez le .env
- `SSO Token Validation Failed` → Vérifiez la clé secrète
- `SSO callback error` → Consultez les détails dans les logs

## 🚀 Étape 6: Mise en Cache (Production)

En production, n'oubliez pas de mettre en cache la configuration :

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

**⚠️ Important :** Après chaque modification du `.env`, exécutez :
```bash
php artisan config:clear
php artisan config:cache
```

## ✅ Checklist Finale

Avant de considérer la configuration comme terminée, vérifiez :

- [ ] Variables SSO ajoutées dans `.env`
- [ ] `SSO_SECRET` identique sur compte.herime.com et academie.herime.com
- [ ] Commande `php artisan sso:test` réussie
- [ ] Endpoint `/api/validate-token` accessible sur compte.herime.com
- [ ] Redirection vers compte.herime.com fonctionne
- [ ] Connexion SSO fonctionne
- [ ] Utilisateur créé/mis à jour automatiquement
- [ ] Déconnexion SSO fonctionne
- [ ] Logs sans erreurs critiques
- [ ] Configuration mise en cache (production)

## 🐛 Résolution de Problèmes

### Problème: "SSO_SECRET: ❌ Non configuré"

**Solution :**
1. Vérifiez que la ligne `SSO_SECRET=...` est bien dans `.env`
2. Vérifiez qu'il n'y a pas d'espaces : `SSO_SECRET=1d69...` (pas `SSO_SECRET = 1d69...`)
3. Exécutez : `php artisan config:clear`

### Problème: "Connexion à l'API échouée"

**Solutions :**
1. Vérifiez que `compte.herime.com` est accessible
2. Vérifiez que l'endpoint `/api/validate-token` existe
3. Testez l'endpoint avec curl (voir étape 3)
4. Vérifiez que `SSO_SECRET` est correct

### Problème: Redirection en boucle

**Solutions :**
1. Vérifiez que `SSO_ENABLED=true` dans `.env`
2. Vérifiez que l'URL de callback est correcte
3. Consultez les logs pour les erreurs
4. Testez avec `SSO_ENABLED=false` temporairement

### Problème: Utilisateur non créé

**Solutions :**
1. Vérifiez que l'email est fourni dans la réponse de l'API SSO
2. Vérifiez les logs : `grep "SSO user" storage/logs/laravel.log`
3. Vérifiez que la base de données est accessible
4. Vérifiez les permissions d'écriture

## 📚 Documentation Complémentaire

- **Guide d'intégration complet :** `SSO_INTEGRATION.md`
- **Guide de test :** `SSO_TESTING_GUIDE.md`
- **Configuration rapide :** `SSO_SETUP.md`

## 🎉 Félicitations !

Si tous les tests passent, votre intégration SSO est opérationnelle ! 

Les utilisateurs peuvent maintenant :
- ✅ Se connecter une seule fois sur compte.herime.com
- ✅ Être automatiquement connectés sur academie.herime.com
- ✅ Se déconnecter globalement depuis n'importe quel site

