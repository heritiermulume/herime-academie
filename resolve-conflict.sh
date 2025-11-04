#!/bin/bash

# Script pour résoudre le conflit Git sur storage/app/private/.gitignore

echo "🔧 Résolution du conflit Git pour storage/app/private/.gitignore"
echo ""

# Vérifier si on est dans un repository Git
if [ ! -d .git ]; then
    echo "❌ Erreur: Vous n'êtes pas dans un repository Git"
    exit 1
fi

# Afficher le statut actuel
echo "📊 Statut actuel:"
git status storage/app/private/.gitignore
echo ""

# Accepter la version distante
echo "✅ Acceptation de la version distante..."
git checkout --theirs storage/app/private/.gitignore

# Ajouter le fichier résolu
echo "➕ Ajout du fichier résolu..."
git add storage/app/private/.gitignore

# Vérifier le contenu du fichier
echo ""
echo "📄 Contenu du fichier résolu:"
cat storage/app/private/.gitignore
echo ""

# Si on est dans un merge, terminer le commit
if [ -f .git/MERGE_HEAD ]; then
    echo "💾 Finalisation du merge..."
    git commit -m "Résolution du conflit: acceptation de la version distante pour storage/app/private/.gitignore"
else
    echo "✅ Conflit résolu! Le fichier est maintenant prêt."
    echo "💡 Vous pouvez maintenant continuer avec: git pull origin main"
fi

echo ""
echo "✨ Terminé!"

