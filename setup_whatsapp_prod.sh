#!/usr/bin/env bash

# Script de setup production pour Herime Académie + Evolution API (WhatsApp)
# A exécuter depuis la racine du projet Laravel.

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$ROOT_DIR"

echo "🚀 Setup production Herime Académie + WhatsApp (Evolution API)"
echo "Racine projet: $ROOT_DIR"
echo

########################################
# 1. Vérifications de base
########################################

require_cmd() {
  if ! command -v "$1" >/dev/null 2>&1; then
    echo "❌ Commande '$1' introuvable. Installe-la puis relance ce script."
    exit 1
  fi
}

echo "🔍 Vérification des prérequis..."
require_cmd php
require_cmd composer
require_cmd git

# Docker est recommandé pour Evolution API, mais pas obligatoire
if command -v docker >/dev/null 2>&1 && command -v docker-compose >/dev/null 2>&1; then
  HAS_DOCKER=1
  echo "✅ Docker + docker-compose détectés."
else
  HAS_DOCKER=0
  echo "⚠️  Docker ou docker-compose non détecté. Evolution API devra être lancée autrement (npm/pm2)."
fi

echo

########################################
# 2. Dépendances PHP (Laravel)
########################################

echo "📦 Installation des dépendances PHP (composer install --no-dev)..."
composer install --no-dev --optimize-autoloader

echo "⚙️ Optimisation Laravel (migrations + caches)..."
php artisan migrate --force || echo "⚠️ Migrations échouées ou déjà appliquées, poursuite du script."
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

echo

########################################
# 3. Dépendances front (optionnel mais recommandé)
########################################

if [ -f "package.json" ]; then
  if command -v npm >/dev/null 2>&1; then
    echo "📦 Installation des dépendances Node du frontend (npm install)..."
    npm install --production=false || echo '⚠️ npm install a échoué, vérifie manuellement si nécessaire.'

    # Si tu build le front en prod (Vite, etc.), décommente :
    # echo "🏗  Build front (npm run build)..."
    # npm run build || echo '⚠️ Build front échoué, vérifie manuellement.'
  else
    echo "⚠️ npm non trouvé, skip de l'installation des dépendances front."
  fi
fi

echo

########################################
# 4. Evolution API (WhatsApp)
########################################

EVOLUTION_DIR="evolution-api"

if [ ! -d "$EVOLUTION_DIR" ]; then
  echo "❌ Le dossier '$EVOLUTION_DIR' n'existe pas. Assure-toi que le code Evolution API est bien présent."
  echo "   (Soit tu commites le dossier 'evolution-api', soit tu utilises le script ./install-evolution-api.sh manuellement.)"
  exit 1
fi

echo "🔧 Configuration Evolution API (dossier: $EVOLUTION_DIR)..."

# Si pas de .env dans evolution-api, on en crée un à partir de .env.example
if [ ! -f "$EVOLUTION_DIR/.env" ]; then
  echo "📄 Aucun .env trouvé pour Evolution API, copie de .env.example..."
  if [ -f "$EVOLUTION_DIR/.env.example" ]; then
    cp "$EVOLUTION_DIR/.env.example" "$EVOLUTION_DIR/.env"
  else
    echo "⚠️ .env.example manquant dans evolution-api. Tu devras configurer manuellement evolution-api/.env."
  fi
fi

# Récupération de la clé API Evolution API (AUTHENTICATION_API_KEY)
API_KEY=""
if [ -f "$EVOLUTION_DIR/.env" ]; then
  API_KEY=$(grep -E '^AUTHENTICATION_API_KEY=' "$EVOLUTION_DIR/.env" | head -n1 | cut -d= -f2- || true)
fi

if [ -z "$API_KEY" ]; then
  echo "⚠️ Impossible de lire AUTHENTICATION_API_KEY dans evolution-api/.env."
  echo "   Assure-toi de définir AUTHENTICATION_API_KEY dans evolution-api/.env et de la reporter dans .env Laravel (WHATSAPP_API_KEY)."
else
  echo "✅ Clé Evolution API détectée."

  ########################################
  # 5. Mise à jour du .env Laravel
  ########################################

  if [ ! -f ".env" ]; then
    echo "❌ Fichier .env Laravel introuvable à la racine. Copie .env.example -> .env puis relance."
    exit 1
  fi

  echo "📝 Mise à jour du .env Laravel pour WhatsApp..."

  # WHATSAPP_BASE_URL (par défaut, Evolution API tourne en local sur 8080)
  if ! grep -q '^WHATSAPP_BASE_URL=' .env; then
    echo "WHATSAPP_BASE_URL=http://localhost:8080" >> .env
    echo "   ➕ WHATSAPP_BASE_URL ajouté (http://localhost:8080)."
  fi

  # WHATSAPP_INSTANCE_NAME (par défaut: default)
  if ! grep -q '^WHATSAPP_INSTANCE_NAME=' .env; then
    echo "WHATSAPP_INSTANCE_NAME=default" >> .env
    echo "   ➕ WHATSAPP_INSTANCE_NAME ajouté (default)."
  fi

  # WHATSAPP_API_KEY (alignée sur AUTHENTICATION_API_KEY d'Evolution API)
  if ! grep -q '^WHATSAPP_API_KEY=' .env; then
    echo "WHATSAPP_API_KEY=$API_KEY" >> .env
    echo "   ➕ WHATSAPP_API_KEY ajouté (valeur prise de evolution-api/.env)."
  else
    echo "   ℹ️ WHATSAPP_API_KEY déjà présent dans .env, je ne le touche pas."
  fi
fi

echo

########################################
# 6. Démarrage Evolution API
########################################

if [ "$HAS_DOCKER" -eq 1 ]; then
  echo "🐳 Démarrage d'Evolution API via docker-compose..."
  cd "$EVOLUTION_DIR"
  # docker-compose v1 ou v2
  if command -v docker-compose >/dev/null 2>&1; then
    docker-compose up -d
  else
    docker compose up -d
  fi
  cd "$ROOT_DIR"
  echo "✅ Evolution API démarrée (Docker)."
else
  echo "⚠️ Docker absent. Tu dois démarrer Evolution API manuellement, par ex. :"
  echo "   cd evolution-api && npm install && npm run build && npm run start:prod (idéalement avec pm2)."
fi

echo

########################################
# 7. Test de la connexion WhatsApp
########################################

echo "🧪 Test de connexion WhatsApp depuis Laravel..."
php artisan whatsapp:test || {
  echo "⚠️ Le test WhatsApp a échoué. Vérifie que :"
  echo "   - Evolution API tourne bien (port 8080 ou celui configuré)"
  echo "   - Le .env Laravel contient WHATSAPP_BASE_URL, WHATSAPP_INSTANCE_NAME, WHATSAPP_API_KEY corrects"
  echo "   - Tu as créé et connecté une instance via Evolution API (voir WHATSAPP_SETUP.md)"
}

echo
echo "✅ Setup terminé. Vérifie maintenant l'envoi de messages depuis l'interface admin (/admin/announcements)."
















