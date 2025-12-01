# 🚀 Guide de démarrage rapide - WhatsApp avec Evolution API

## Installation en 3 étapes

### 1️⃣ Installer Evolution API

```bash
# Option A: Script automatique (recommandé)
./install-evolution-api.sh

# Option B: Installation manuelle
git clone https://github.com/EvolutionAPI/evolution-api.git
cd evolution-api
cp .env.example .env
# Éditer .env avec votre configuration
docker-compose up -d
```

### 2️⃣ Créer et connecter une instance

```bash
# Récupérer la clé API depuis evolution-api/.env
# (cherchez AUTHENTICATION_API_KEY)

# Créer une instance
curl -X POST http://localhost:8080/instance/create \
  -H "apikey: VOTRE_CLE_API" \
  -H "Content-Type: application/json" \
  -d '{
    "instanceName": "default",
    "token": "votre_token_secret",
    "qrcode": true
  }'

# Récupérer le QR code
curl -X GET http://localhost:8080/instance/connect/default \
  -H "apikey: VOTRE_CLE_API"

# Scanner le QR code avec WhatsApp
```

### 3️⃣ Configurer Laravel

Ajoutez dans votre `.env`:

```env
WHATSAPP_BASE_URL=http://localhost:8080
WHATSAPP_INSTANCE_NAME=default
WHATSAPP_API_KEY=VOTRE_CLE_API
```

## ✅ Tester la connexion

```bash
# Vérifier la connexion
php artisan whatsapp:test

# Tester l'envoi
php artisan whatsapp:test --phone=229XXXXXXXX --message="Test"
```

## 📱 Utiliser dans l'application

1. Allez sur `/admin/announcements`
2. Cliquez sur l'icône WhatsApp (vert)
3. Sélectionnez les destinataires
4. Rédigez votre message
5. Envoyez!

## 🔧 Dépannage

### Erreur: "Connexion non active"
- Vérifiez que Evolution API est démarré: `docker ps`
- Vérifiez que l'instance est connectée: `php artisan whatsapp:test`
- Vérifiez les logs: `docker logs evolution-api`

### Erreur: "401 Unauthorized"
- Vérifiez que `WHATSAPP_API_KEY` correspond à `AUTHENTICATION_API_KEY` dans evolution-api/.env

### Erreur: "Connection refused"
- Vérifiez que Evolution API écoute sur le bon port
- Vérifiez que `WHATSAPP_BASE_URL` est correct

## 📚 Documentation complète

Consultez `WHATSAPP_SETUP.md` pour plus de détails.

