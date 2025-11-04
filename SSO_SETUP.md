# 🚀 Configuration SSO - Guide Rapide

## ✅ Configuration Immédiate

Ajoutez ces lignes dans votre fichier `.env` :

```env
# SSO Configuration
SSO_ENABLED=true
SSO_BASE_URL=https://compte.herime.com
SSO_SECRET=1d69dac265aab9b5633e96af6f2e4f27f082824f1512b2f7a047bf8f4365e3db
SSO_TIMEOUT=10
```

## 🔑 Détails de la Clé Secrète

- **Longueur** : 64 caractères hexadécimaux (256 bits)
- **Type** : Cryptographiquement sécurisée
- **Usage** : Utilisée comme Bearer token dans l'Authorization header
- **Partagée** : Identique sur compte.herime.com et academie.herime.com

## 📡 Endpoint API

L'endpoint suivant est configuré sur **compte.herime.com** :

**POST** `https://compte.herime.com/api/validate-token`

### Test avec curl

```bash
curl -X POST https://compte.herime.com/api/validate-token \
  -H "Authorization: Bearer 1d69dac265aab9b5633e96af6f2e4f27f082824f1512b2f7a047bf8f4365e3db" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"token": "eyJhbGciOiJIUzI1..."}'
```

### Réponse attendue

**Succès (200):**
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

**Échec (200 ou 400):**
```json
{
  "valid": false,
  "message": "Token invalide ou expiré"
}
```

## 🧪 Test de l'Intégration

1. **Vérifiez la configuration** :
   ```bash
   php artisan tinker
   >>> config('services.sso.base_url')
   => "https://compte.herime.com"
   >>> config('services.sso.secret')
   => "1d69dac265aab9b5633e96af6f2e4f27f082824f1512b2f7a047bf8f4365e3db"
   ```

2. **Testez la redirection** :
   - Visitez `https://academie.herime.com/login`
   - Vous devriez être redirigé vers `compte.herime.com/login`

3. **Testez le callback** :
   - Après connexion sur compte.herime.com
   - Vous devriez être redirigé vers `academie.herime.com/sso/callback?token=...`

## ✅ Checklist de Déploiement

- [ ] Variables SSO ajoutées dans `.env`
- [ ] `SSO_SECRET` identique sur compte.herime.com et academie.herime.com
- [ ] Endpoint `/api/validate-token` fonctionne sur compte.herime.com
- [ ] HTTPS activé sur tous les sous-domaines
- [ ] Test de connexion réussi
- [ ] Test de déconnexion réussi
- [ ] Logs vérifiés (storage/logs/laravel.log)

## 🔍 Vérification des Logs

Les opérations SSO sont loggées dans `storage/logs/laravel.log`. Recherchez :
- `SSO login successful`
- `SSO Token Validation Failed`
- `SSO callback error`

## 🆘 Dépannage

### Erreur "SSO credentials not configured"
→ Vérifiez que `SSO_SECRET` et `SSO_BASE_URL` sont dans `.env`

### Erreur "Token SSO invalide"
→ Vérifiez que `SSO_SECRET` est identique sur les deux sites
→ Vérifiez que l'endpoint `/api/validate-token` fonctionne

### Redirection en boucle
→ Vérifiez que `SSO_ENABLED=true` (ou désactivez avec `false` pour tester)

## 📚 Documentation Complète

Consultez `SSO_INTEGRATION.md` pour la documentation complète du système SSO.

