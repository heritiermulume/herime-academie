# ✅ Vérification SSO en Production

## 🔍 Checklist de Vérification

### 1. Configuration .env

Vérifiez que toutes les variables sont présentes et correctes :

```bash
# Sur le serveur de production
php artisan tinker
>>> config('services.sso.enabled')
=> true
>>> config('services.sso.base_url')
=> "https://compte.herime.com"
>>> config('services.sso.secret')
=> "1d69dac265aab9b5633e96af6f2e4f27f082824f1512b2f7a047bf8f4365e3db"
```

### 2. Test de la Configuration

```bash
php artisan config:clear
php artisan config:cache  # En production
php artisan sso:test
```

**Résultats attendus :**
- ✅ SSO_ENABLED: Activé
- ✅ SSO_BASE_URL: https://compte.herime.com
- ✅ SSO_SECRET: Configuré (64 caractères)
- ✅ Connexion à l'API réussie

### 3. Test du Flux Complet

#### Test 1: Redirection
- Visitez `https://academie.herime.com/login`
- **Résultat attendu :** Redirection vers `compte.herime.com/login?redirect=...`

#### Test 2: Connexion
- Connectez-vous sur compte.herime.com
- **Résultat attendu :** Redirection vers `academie.herime.com/sso/callback?token=...` puis connexion automatique

#### Test 3: Déconnexion
- Déconnectez-vous sur academie.herime.com
- **Résultat attendu :** Redirection vers `compte.herime.com/logout?redirect=...`

### 4. Vérification des Logs

```bash
tail -f storage/logs/laravel.log | grep SSO
```

**Messages de succès attendus :**
- `SSO login successful`
- `SSO user created` (première connexion)
- `SSO Token Validation Successful`

## 🐛 Problèmes Courants

### Problème: "SSO credentials not configured"

**Solution :**
```bash
php artisan config:clear
php artisan config:cache
```

### Problème: Token invalide

**Vérifications :**
1. `SSO_SECRET` identique sur compte.herime.com et academie.herime.com
2. Endpoint `/api/validate-token` accessible
3. Testez avec curl :
```bash
curl -X POST https://compte.herime.com/api/validate-token \
  -H "Authorization: Bearer 1d69dac265aab9b5633e96af6f2e4f27f082824f1512b2f7a047bf8f4365e3db" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"token": "test"}'
```

### Problème: Redirection en boucle

**Solution :**
- Vérifiez que `SSO_ENABLED=true`
- Vérifiez les logs pour les erreurs
- Testez temporairement avec `SSO_ENABLED=false`

## 📊 Rapport de Test

Utilisez ce template pour documenter vos tests :

```
✅ Configuration .env: [OK / KO]
✅ Test de configuration (artisan sso:test): [OK / KO]
✅ Test de redirection: [OK / KO]
✅ Test de connexion: [OK / KO]
✅ Test de déconnexion: [OK / KO]
✅ Vérification des logs: [OK / KO]
✅ Utilisateur créé/mis à jour: [OK / KO]
```

## 🎉 Si Tout Fonctionne

Félicitations ! Votre intégration SSO est opérationnelle en production ! 🚀

Les utilisateurs peuvent maintenant :
- ✅ Se connecter une seule fois sur compte.herime.com
- ✅ Être automatiquement connectés sur academie.herime.com
- ✅ Se déconnecter globalement depuis n'importe quel site

