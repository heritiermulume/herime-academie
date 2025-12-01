#!/bin/bash

# Script d'installation d'Evolution API pour WhatsApp
# Ce script installe Evolution API via Docker (méthode recommandée)

echo "🚀 Installation d'Evolution API pour WhatsApp..."
echo ""

# Vérifier si Docker est installé
if ! command -v docker &> /dev/null; then
    echo "❌ Docker n'est pas installé. Veuillez installer Docker d'abord."
    echo "   Visitez: https://docs.docker.com/get-docker/"
    exit 1
fi

# Vérifier si Docker Compose est installé
if ! command -v docker-compose &> /dev/null; then
    echo "❌ Docker Compose n'est pas installé. Veuillez installer Docker Compose d'abord."
    exit 1
fi

# Créer le répertoire pour Evolution API
EVOLUTION_DIR="evolution-api"
if [ -d "$EVOLUTION_DIR" ]; then
    echo "⚠️  Le répertoire $EVOLUTION_DIR existe déjà."
    read -p "Voulez-vous le supprimer et réinstaller? (o/N): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Oo]$ ]]; then
        rm -rf "$EVOLUTION_DIR"
    else
        echo "Installation annulée."
        exit 0
    fi
fi

# Cloner le repository
echo "📦 Clonage du repository Evolution API..."
git clone https://github.com/EvolutionAPI/evolution-api.git "$EVOLUTION_DIR"
cd "$EVOLUTION_DIR"

# Copier le fichier .env.example
if [ -f ".env.example" ]; then
    cp .env.example .env
    echo "✅ Fichier .env créé"
else
    echo "⚠️  Fichier .env.example non trouvé, création d'un .env basique..."
    cat > .env << EOF
# Configuration Evolution API
SERVER_URL=http://localhost:8080
PORT=8080

# Base de données
DATABASE_ENABLED=true
DATABASE_PROVIDER=postgresql
DATABASE_CONNECTION_URI=postgresql://evolution_user:evolution_pass@postgres:5432/evolution_db

# Redis (optionnel mais recommandé)
REDIS_ENABLED=true
REDIS_URI=redis://redis:6379

# Authentification
AUTHENTICATION_API_KEY=evolution_api_key_change_me
AUTHENTICATION_EXPOSE_IN_FETCH_INSTANCES=true

# Webhook
WEBHOOK_GLOBAL_ENABLED=false
WEBHOOK_GLOBAL_URL=

# Logs
LOG_LEVEL=ERROR
EOF
    echo "✅ Fichier .env créé avec configuration par défaut"
fi

# Générer une clé API aléatoire
API_KEY=$(openssl rand -hex 32 2>/dev/null || cat /dev/urandom | tr -dc 'a-zA-Z0-9' | fold -w 64 | head -n 1)
sed -i.bak "s/AUTHENTICATION_API_KEY=.*/AUTHENTICATION_API_KEY=$API_KEY/" .env

echo ""
echo "✅ Evolution API installé avec succès!"
echo ""
echo "📝 Configuration:"
echo "   - Répertoire: $(pwd)"
echo "   - Clé API générée: $API_KEY"
echo ""
echo "⚠️  IMPORTANT: Notez cette clé API et ajoutez-la dans votre fichier .env Laravel:"
echo "   WHATSAPP_API_KEY=$API_KEY"
echo ""
echo "🚀 Pour démarrer Evolution API:"
echo "   cd $EVOLUTION_DIR"
echo "   docker-compose up -d"
echo ""
echo "📖 Pour créer une instance et se connecter à WhatsApp:"
echo "   1. Attendez que les conteneurs démarrent (environ 30 secondes)"
echo "   2. Créez une instance:"
echo "      curl -X POST http://localhost:8080/instance/create \\"
echo "        -H 'apikey: $API_KEY' \\"
echo "        -H 'Content-Type: application/json' \\"
echo "        -d '{\"instanceName\":\"default\",\"token\":\"your_secret_token\",\"qrcode\":true}'"
echo ""
echo "   3. Récupérez le QR code:"
echo "      curl -X GET http://localhost:8080/instance/connect/default \\"
echo "        -H 'apikey: $API_KEY'"
echo ""
echo "   4. Scannez le QR code avec WhatsApp"
echo ""
echo "📚 Documentation complète: https://doc.evolution-api.com/"

