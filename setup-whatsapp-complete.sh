#!/bin/bash

# Script complet pour installer et configurer Evolution API

set -e

PROJECT_DIR="$(cd "$(dirname "$0")" && pwd)"
EVOLUTION_DIR="$PROJECT_DIR/evolution-api"
API_KEY="e20d827cf706399860c46f6b9f11e55ac4cbb77d0cbe5548648937727a4e55d2"

echo "🚀 Configuration complète d'Evolution API pour WhatsApp"
echo ""

# 1. Vérifier les prérequis
echo "📋 Vérification des prérequis..."
if ! command -v node &> /dev/null; then
    echo "❌ Node.js n'est pas installé"
    exit 1
fi

if ! command -v mysql &> /dev/null; then
    echo "❌ MySQL n'est pas installé"
    exit 1
fi

echo "✅ Prérequis OK"
echo ""

# 2. Installer Evolution API si nécessaire
if [ ! -d "$EVOLUTION_DIR" ]; then
    echo "📦 Installation d'Evolution API..."
    cd "$PROJECT_DIR"
    git clone https://github.com/EvolutionAPI/evolution-api.git evolution-api
    cd "$EVOLUTION_DIR"
    npm install
    echo "✅ Evolution API installé"
else
    echo "✅ Evolution API déjà installé"
    cd "$EVOLUTION_DIR"
fi

echo ""

# 3. Configurer la base de données
echo "🗄️  Configuration de la base de données MySQL..."
mysql -u root -e "CREATE DATABASE IF NOT EXISTS evolution_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null || \
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS evolution_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null || true

echo "✅ Base de données créée"
echo ""

# 4. Configurer .env
echo "⚙️  Configuration du fichier .env..."
cat > .env << EOF
# Server Configuration
SERVER_NAME=evolution
SERVER_TYPE=http
SERVER_PORT=8080
SERVER_URL=http://localhost:8080

# Database
DATABASE_ENABLED=true
DATABASE_PROVIDER=mysql
DATABASE_CONNECTION_URI=mysql://root@localhost:3306/evolution_db

# Redis (disabled for simplicity)
REDIS_ENABLED=false

# Authentication
AUTHENTICATION_API_KEY=$API_KEY
AUTHENTICATION_EXPOSE_IN_FETCH_INSTANCES=true

# Webhook
WEBHOOK_GLOBAL_ENABLED=false

# Logs
LOG_LEVEL=ERROR
EOF

echo "✅ Fichier .env configuré"
echo ""

# 5. Générer Prisma Client
echo "🔧 Génération du client Prisma..."
npx prisma generate --schema=./prisma/mysql-schema.prisma > /dev/null 2>&1
echo "✅ Prisma Client généré"
echo ""

# 6. Créer les tables
echo "📊 Création des tables de base de données..."
npx prisma db push --schema=./prisma/mysql-schema.prisma --accept-data-loss --skip-generate > /dev/null 2>&1
echo "✅ Tables créées"
echo ""

# 7. Démarrer Evolution API
echo "🚀 Démarrage d'Evolution API..."
if [ -f "/tmp/evolution-api.pid" ]; then
    kill $(cat /tmp/evolution-api.pid) 2>/dev/null || true
    sleep 2
fi

npm start > /tmp/evolution-api.log 2>&1 &
echo $! > /tmp/evolution-api.pid

echo "✅ Evolution API démarré (PID: $(cat /tmp/evolution-api.pid))"
echo "⏳ Attente du démarrage complet (20 secondes)..."
sleep 20
echo ""

# 8. Créer l'instance
echo "📱 Création de l'instance WhatsApp..."
INSTANCE_RESPONSE=$(curl -s -X POST http://localhost:8080/instance/create \
  -H "apikey: $API_KEY" \
  -H "Content-Type: application/json" \
  -d '{"instanceName":"default","integration":"WHATSAPP-BAILEYS","qrcode":true}' 2>&1)

if echo "$INSTANCE_RESPONSE" | grep -q "status.*200\|instanceName.*default"; then
    echo "✅ Instance créée avec succès"
else
    echo "⚠️  Réponse de création d'instance:"
    echo "$INSTANCE_RESPONSE" | head -5
fi
echo ""

# 9. Récupérer le QR code
echo "📲 Récupération du QR code..."
QR_RESPONSE=$(curl -s http://localhost:8080/instance/connect/default \
  -H "apikey: $API_KEY" 2>&1)

if echo "$QR_RESPONSE" | grep -q "base64\|qrcode"; then
    echo "✅ QR code disponible"
    echo ""
    echo "📋 Pour connecter WhatsApp:"
    echo "   1. Ouvrez: http://localhost:8080/instance/connect/default"
    echo "   2. Scannez le QR code avec WhatsApp"
    echo "   3. Attendez que l'instance soit connectée"
else
    echo "⚠️  QR code non disponible. Vérifiez les logs:"
    echo "$QR_RESPONSE" | head -5
fi
echo ""

# 10. Configurer Laravel
echo "🔧 Configuration de Laravel..."
cd "$PROJECT_DIR"

# Vérifier si les variables sont déjà dans .env
if ! grep -q "WHATSAPP_BASE_URL" .env 2>/dev/null; then
    cat >> .env << EOF

# WhatsApp Evolution API Configuration
WHATSAPP_BASE_URL=http://localhost:8080
WHATSAPP_INSTANCE_NAME=default
WHATSAPP_API_KEY=$API_KEY
EOF
    echo "✅ Variables WhatsApp ajoutées au .env Laravel"
else
    echo "✅ Variables WhatsApp déjà configurées"
fi

# Nettoyer le cache
php artisan config:clear > /dev/null 2>&1
php artisan cache:clear > /dev/null 2>&1

echo ""
echo "✅ Configuration Laravel terminée"
echo ""

# 11. Test de connexion
echo "🧪 Test de la connexion..."
php artisan whatsapp:test 2>&1
echo ""

echo "✨ Configuration terminée!"
echo ""
echo "📚 Prochaines étapes:"
echo "   1. Connectez l'instance à WhatsApp (voir QR code ci-dessus)"
echo "   2. Testez avec: php artisan whatsapp:test --phone=229XXXXXXXX"
echo "   3. Utilisez l'interface: http://127.0.0.1:8000/admin/announcements"
echo ""
echo "📖 Documentation: WHATSAPP_SETUP.md"

