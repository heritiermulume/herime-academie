#!/bin/bash

# Script pour compiler les assets Vite en production
# Usage: ./build-assets-production.sh

echo "=========================================="
echo "COMPILATION DES ASSETS VITE - PRODUCTION"
echo "=========================================="
echo ""

# Vérifier que nous sommes dans le bon répertoire
if [ ! -f "package.json" ]; then
    echo "❌ Erreur: Ce script doit être exécuté depuis la racine du projet Laravel"
    exit 1
fi

# Vérifier si Node.js est installé
if ! command -v node &> /dev/null; then
    echo "❌ Node.js n'est pas installé!"
    echo ""
    echo "Pour installer Node.js sur O2Switch:"
    echo "1. Contactez le support O2Switch"
    echo "2. Ou installez via nvm (Node Version Manager)"
    echo ""
    exit 1
fi

echo "✅ Node.js trouvé: $(node --version)"

# Vérifier si npm est installé
if ! command -v npm &> /dev/null; then
    echo "❌ npm n'est pas installé!"
    exit 1
fi

echo "✅ npm trouvé: $(npm --version)"
echo ""

# Installer les dépendances si nécessaire
echo "1️⃣  Vérification des dépendances npm..."
if [ ! -d "node_modules" ]; then
    echo "📦 Installation des dépendances npm..."
    npm install --production
    if [ $? -ne 0 ]; then
        echo "❌ Erreur lors de l'installation des dépendances"
        exit 1
    fi
    echo "✅ Dépendances installées"
else
    echo "✅ Dépendances déjà installées"
fi
echo ""

# Compiler les assets
echo "2️⃣  Compilation des assets Vite..."
npm run build

if [ $? -ne 0 ]; then
    echo "❌ Erreur lors de la compilation des assets"
    exit 1
fi

# Vérifier que le manifest.json a été créé
if [ -f "public/build/manifest.json" ]; then
    echo "✅ Assets compilés avec succès!"
    echo "✅ Fichier manifest.json créé: public/build/manifest.json"
    echo ""
    echo "📊 Contenu du manifest:"
    head -20 public/build/manifest.json
else
    echo "❌ Erreur: Le fichier manifest.json n'a pas été créé"
    exit 1
fi

echo ""
echo "=========================================="
echo "COMPILATION TERMINÉE"
echo "=========================================="

