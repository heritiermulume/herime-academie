#!/bin/bash

# Script pour résoudre le conflit de merge avec storage/framework/cache/data/.gitignore

echo "🔍 Résolution du conflit de merge..."

# Vérifier l'état actuel
echo "📊 État Git actuel:"
git status

# Supprimer le fichier local qui cause le conflit
# (Ce fichier a été supprimé dans le commit distant)
if [ -f "storage/framework/cache/data/.gitignore" ]; then
    echo "🗑️  Suppression du fichier local storage/framework/cache/data/.gitignore"
    rm -f storage/framework/cache/data/.gitignore
fi

# Supprimer le fichier de l'index Git si nécessaire
git rm --cached storage/framework/cache/data/.gitignore 2>/dev/null || echo "Fichier déjà retiré de l'index"

# Réessayer le pull
echo "⬇️  Exécution du pull..."
git pull origin main

echo "✅ Conflit résolu !"

