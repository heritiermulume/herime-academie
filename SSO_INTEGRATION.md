# Intégration SSO (Single Sign-On)

Ce document explique comment le système SSO est intégré à academie.herime.com pour se connecter avec compte.herime.com.

## 🎯 Fonctionnement

Le système SSO permet aux utilisateurs de se connecter une seule fois sur compte.herime.com et d'être automatiquement connectés sur academie.herime.com (et autres sous-domaines).

## 📋 Configuration

### Variables d'environnement

Ajoutez ces variables dans votre fichier `.env` :

```env
# SSO Configuration
SSO_ENABLED=true
SSO_BASE_URL=https://compte.herime.com
SSO_SECRET=1d69dac265aab9b5633e96af6f2e4f27f082824f1512b2f7a047bf8f4365e3db
SSO_TIMEOUT=10
```

**⚠️ Important** : La clé secrète ci-dessus est la clé partagée entre compte.herime.com et academie.herime.com. Elle doit être identique sur les deux sites.

### Explication des variables

- **SSO_ENABLED** : Active ou désactive le SSO (par défaut: `true`)
- **SSO_BASE_URL** : URL de base du serveur SSO (compte.herime.com)
- **SSO_SECRET** : Clé secrète partagée entre les deux sites pour valider les tokens
- **SSO_TIMEOUT** : Timeout en secondes pour les appels API SSO (par défaut: `10`)

## 🔄 Flux d'authentification

### 1. Connexion

1. L'utilisateur accède à academie.herime.com sans être connecté
2. Il est automatiquement redirigé vers `compte.herime.com/login?redirect=https://academie.herime.com/sso/callback?redirect=...`
3. L'utilisateur se connecte sur compte.herime.com
4. Après connexion, compte.herime.com génère un token JWT et redirige vers :
   `https://academie.herime.com/sso/callback?token=eyJhbGciOiJIUzI1...`
5. academie.herime.com valide le token auprès de compte.herime.com via l'API
6. L'utilisateur est automatiquement connecté sur academie.herime.com

### 2. Déconnexion

1. L'utilisateur se déconnecte sur academie.herime.com
2. Il est redirigé vers `compte.herime.com/logout?redirect=https://academie.herime.com`
3. La session est invalidée sur tous les sites

## 🔧 API requise sur compte.herime.com

Le serveur SSO (compte.herime.com) doit exposer l'endpoint suivant :

### POST /api/validate-token

**Headers:**
```
Accept: application/json
Content-Type: application/json
Authorization: Bearer {SSO_SECRET}
```

**Body:**
```json
{
  "token": "eyJhbGciOiJIUzI1..."
}
```

**Exemple avec curl:**
```bash
curl -X POST https://compte.herime.com/api/validate-token \
  -H "Authorization: Bearer 1d69dac265aab9b5633e96af6f2e4f27f082824f1512b2f7a047bf8f4365e3db" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"token": "eyJhbGciOiJIUzI1..."}'
```

**Response (succès):**
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

**Response (échec):**
```json
{
  "valid": false,
  "message": "Token invalide ou expiré"
}
```

## 📁 Fichiers créés/modifiés

### Nouveaux fichiers

1. **app/Services/SSOService.php**
   - Service pour communiquer avec le serveur SSO
   - Méthodes: `validateToken()`, `getLoginUrl()`, `getLogoutUrl()`

2. **app/Http/Controllers/Auth/SSOController.php**
   - Contrôleur pour gérer le callback SSO
   - Méthodes: `callback()`, `redirectToSSO()`, `findOrCreateUser()`

3. **app/Http/Middleware/RedirectToSSO.php**
   - Middleware pour rediriger vers SSO si non connecté

### Fichiers modifiés

1. **config/services.php**
   - Ajout de la configuration SSO

2. **routes/web.php**
   - Ajout des routes SSO (`/sso/callback`, `/sso/redirect`)

3. **app/Http/Controllers/Auth/AuthenticatedSessionController.php**
   - Modification de `create()` pour rediriger vers SSO
   - Modification de `destroy()` pour rediriger vers la déconnexion SSO

4. **app/Http/Middleware/RoleMiddleware.php**
   - Modification pour rediriger vers SSO au lieu de la page de login locale

## 🛡️ Sécurité

1. **HTTPS requis** : Tous les échanges doivent se faire en HTTPS
2. **Secret partagé** : Le `SSO_SECRET` doit être identique sur les deux sites
3. **Validation du token** : Chaque token est validé auprès du serveur SSO avant utilisation
4. **Régénération de session** : La session est régénérée après chaque connexion SSO

## 🔄 Désactiver le SSO (mode développement)

Pour désactiver le SSO et utiliser l'authentification locale :

```env
SSO_ENABLED=false
```

Dans ce cas, le système utilisera les pages de connexion locales normales.

## 📝 Notes importantes

1. **Synchronisation des utilisateurs** : Les utilisateurs sont automatiquement créés ou mis à jour lors de la première connexion SSO
2. **Rôles** : Les rôles peuvent être synchronisés depuis compte.herime.com si fournis dans la réponse API
3. **Panier** : Le panier de session est automatiquement synchronisé avec la base de données après connexion SSO
4. **Logs** : Toutes les opérations SSO sont loggées pour le débogage

## 🐛 Dépannage

### L'utilisateur n'est pas redirigé vers SSO

- Vérifiez que `SSO_ENABLED=true` dans `.env`
- Vérifiez que `SSO_BASE_URL` est correct
- Vérifiez les logs Laravel pour les erreurs

### Erreur "Token SSO invalide"

- Vérifiez que `SSO_SECRET` est identique sur les deux sites
- Vérifiez que l'endpoint `/api/validate-token` fonctionne sur compte.herime.com
- Vérifiez les logs pour plus de détails

### L'utilisateur n'est pas créé

- Vérifiez que l'email est fourni dans la réponse de l'API SSO
- Vérifiez les logs Laravel pour les erreurs de création d'utilisateur

