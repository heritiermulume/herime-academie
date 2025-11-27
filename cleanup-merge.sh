#!/bin/bash
# Script pour nettoyer un merge bloqué sur le serveur

cd ~/herime-academie

echo "🧹 Nettoyage du merge bloqué..."

# 1. Supprimer tous les fichiers de merge et swap
echo "1. Suppression des fichiers de merge..."
rm -f .git/.MERGE_MSG.swp
rm -f .git/MERGE_HEAD
rm -f .git/MERGE_MSG
rm -f .git/MERGE_MODE

# 2. Abandonner le merge si possible
echo "2. Abandon du merge..."
git merge --abort 2>/dev/null || echo "   (Merge déjà abandonné ou inexistant)"

# 3. Réinitialiser proprement
echo "3. Réinitialisation du dépôt..."
git reset --hard HEAD

# 4. Nettoyer les fichiers non suivis
echo "4. Nettoyage des fichiers non suivis..."
git clean -fd

# 5. Vérifier l'état
echo ""
echo "✅ Nettoyage terminé !"
echo ""
echo "État actuel du dépôt :"
git status

echo ""
echo "📥 Vous pouvez maintenant faire :"
echo "   git pull origin main"


