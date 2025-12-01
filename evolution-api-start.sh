#!/bin/bash

# Script pour démarrer Evolution API

cd "$(dirname "$0")/evolution-api"

echo "🚀 Démarrage d'Evolution API..."

# Vérifier si Prisma est généré
if [ ! -d "node_modules/.prisma" ]; then
    echo "📦 Génération du client Prisma..."
    npx prisma generate --schema=./prisma/mysql-schema.prisma
fi

# Vérifier si les tables existent
echo "🔍 Vérification de la base de données..."
npx prisma db push --schema=./prisma/mysql-schema.prisma --accept-data-loss --skip-generate > /dev/null 2>&1

# Démarrer l'API
echo "✅ Démarrage du serveur..."
npm start

