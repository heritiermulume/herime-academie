# Configuration WhatsApp avec Evolution API (Open Source)

## À propos d'Evolution API

Evolution API est une solution **open source** et **gratuite** pour intégrer WhatsApp dans vos applications. Elle peut être auto-hébergée sur votre propre serveur.

## Installation d'Evolution API

### Option 1: Installation automatique (Script)

Utilisez le script d'installation fourni:

```bash
./install-evolution-api.sh
```

Ce script va:
- Vérifier les prérequis (Docker, Docker Compose)
- Cloner le repository Evolution API
- Créer le fichier .env avec une configuration par défaut
- Générer une clé API aléatoire sécurisée
- Vous donner les instructions pour démarrer

### Option 2: Installation manuelle via Docker

```bash
# Cloner le repository
git clone https://github.com/EvolutionAPI/evolution-api.git
cd evolution-api

# Copier le fichier d'environnement
cp .env.example .env

# Éditer le fichier .env et configurer:
# - DATABASE_ENABLED=true
# - DATABASE_PROVIDER=postgresql ou mysql
# - DATABASE_CONNECTION_URI=postgresql://user:pass@postgres:5432/dbname
# - AUTHENTICATION_API_KEY=votre_cle_secrete
# - REDIS_ENABLED=true (recommandé)
# - REDIS_URI=redis://redis:6379

# Démarrer avec Docker Compose
docker-compose up -d
```

### Option 2: Installation via NPM

```bash
# Cloner le repository
git clone https://github.com/EvolutionAPI/evolution-api.git
cd evolution-api

# Installer les dépendances
npm install

# Configurer le fichier .env
cp .env.example .env

# Démarrer le serveur
npm start
```

## Configuration dans Laravel

### 1. Variables d'environnement

Ajoutez ces variables dans votre fichier `.env`:

```env
# URL de base de votre instance Evolution API
WHATSAPP_BASE_URL=http://localhost:8080

# Nom de l'instance (par défaut: default)
WHATSAPP_INSTANCE_NAME=default

# Clé API (configurée dans Evolution API)
WHATSAPP_API_KEY=votre_cle_api
```

### 2. Créer une instance WhatsApp

Une fois Evolution API démarré, vous devez créer une instance:

```bash
# Créer une instance
curl -X POST http://localhost:8080/instance/create \
  -H "apikey: votre_cle_api" \
  -H "Content-Type: application/json" \
  -d '{
    "instanceName": "default",
    "token": "votre_token_secret",
    "qrcode": true
  }'
```

### 3. Connecter l'instance à WhatsApp

1. Récupérez le QR code:
```bash
curl -X GET http://localhost:8080/instance/connect/default \
  -H "apikey: votre_cle_api"
```

2. Scannez le QR code avec votre téléphone WhatsApp
3. Une fois connecté, l'instance sera prête à envoyer des messages

## Test de la connexion

Une fois Evolution API configuré, testez la connexion avec la commande Artisan:

```bash
# Vérifier uniquement la connexion
php artisan whatsapp:test

# Tester l'envoi d'un message
php artisan whatsapp:test --phone=229XXXXXXXX --message="Message de test"
```

## Utilisation

L'application Laravel est maintenant configurée pour utiliser Evolution API. Vous pouvez:

1. Aller sur `/admin/announcements`
2. Cliquer sur l'icône WhatsApp
3. Envoyer des messages à vos utilisateurs

## Avantages d'Evolution API

- ✅ **100% Open Source** - Code source disponible sur GitHub
- ✅ **Gratuit** - Aucun coût
- ✅ **Auto-hébergé** - Contrôle total sur vos données
- ✅ **Illimité** - Pas de limites de messages
- ✅ **Sécurisé** - Vos données restent sur votre serveur

## Documentation officielle

- GitHub: https://github.com/EvolutionAPI/evolution-api
- Documentation: https://doc.evolution-api.com/

## Dépannage

### L'instance n'est pas connectée

Vérifiez l'état de connexion:
```bash
curl -X GET http://localhost:8080/instance/connectionState/default \
  -H "apikey: votre_cle_api"
```

### Erreur 401 (Non autorisé)

Vérifiez que votre `WHATSAPP_API_KEY` correspond à la clé configurée dans Evolution API.

### Erreur de connexion

Assurez-vous que:
1. Evolution API est démarré et accessible
2. L'URL dans `.env` est correcte
3. Le port 8080 (ou celui configuré) est ouvert

