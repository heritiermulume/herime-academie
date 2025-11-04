#!/bin/bash

# Script pour configurer la production avec tous les dossiers et configurations nécessaires

echo "🚀 Configuration de la production pour Herime Academie"
echo ""

# Couleurs pour les messages
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Vérifier qu'on est dans un projet Laravel
if [ ! -f "artisan" ]; then
    echo -e "${RED}❌ Erreur: Ce script doit être exécuté dans le répertoire racine du projet Laravel${NC}"
    exit 1
fi

echo -e "${YELLOW}📁 Création des dossiers de stockage privé...${NC}"

# Créer tous les dossiers nécessaires
mkdir -p storage/app/private/courses/thumbnails
mkdir -p storage/app/private/courses/previews
mkdir -p storage/app/private/courses/lessons
mkdir -p storage/app/private/courses/downloads
mkdir -p storage/app/private/avatars
mkdir -p storage/app/private/banners

echo -e "${GREEN}✅ Dossiers créés${NC}"

# Créer le fichier .gitignore dans storage/app/private
echo -e "${YELLOW}📝 Création du fichier .gitignore...${NC}"
cat > storage/app/private/.gitignore << 'EOF'
*
!.gitignore
EOF
echo -e "${GREEN}✅ .gitignore créé${NC}"

# Vérifier et ajuster les permissions
echo -e "${YELLOW}🔐 Configuration des permissions...${NC}"
chmod -R 775 storage/app/private
chown -R www-data:www-data storage/app/private 2>/dev/null || echo "⚠️  Impossible de changer le propriétaire (utilisez sudo si nécessaire)"
echo -e "${GREEN}✅ Permissions configurées${NC}"

# Vérifier la configuration filesystems.php
echo -e "${YELLOW}⚙️  Vérification de la configuration...${NC}"
if grep -q "storage_path('app/private')" config/filesystems.php; then
    echo -e "${GREEN}✅ Configuration filesystems.php OK${NC}"
else
    echo -e "${YELLOW}⚠️  Vérifiez que config/filesystems.php contient la configuration pour le disque 'local'${NC}"
fi

# Vérifier que les fichiers nécessaires existent
echo -e "${YELLOW}🔍 Vérification des fichiers nécessaires...${NC}"

FILES=(
    "app/Services/FileUploadService.php"
    "app/Http/Controllers/FileController.php"
    "app/Helpers/FileHelper.php"
)

MISSING=0
for file in "${FILES[@]}"; do
    if [ -f "$file" ]; then
        echo -e "${GREEN}✅ $file${NC}"
    else
        echo -e "${RED}❌ $file manquant${NC}"
        MISSING=1
    fi
done

if [ $MISSING -eq 1 ]; then
    echo -e "${RED}❌ Certains fichiers sont manquants. Assurez-vous d'avoir fait 'git pull origin main'${NC}"
    exit 1
fi

# Vérifier les routes
echo -e "${YELLOW}🔍 Vérification des routes...${NC}"
if grep -q "FileController" routes/web.php; then
    echo -e "${GREEN}✅ Route FileController présente${NC}"
else
    echo -e "${YELLOW}⚠️  La route FileController n'a pas été trouvée dans routes/web.php${NC}"
fi

# Optimiser l'application
echo -e "${YELLOW}⚡ Optimisation de l'application...${NC}"
php artisan config:cache 2>/dev/null || echo "⚠️  config:cache échoué"
php artisan route:cache 2>/dev/null || echo "⚠️  route:cache échoué"
php artisan view:cache 2>/dev/null || echo "⚠️  view:cache échoué"
echo -e "${GREEN}✅ Optimisation terminée${NC}"

echo ""
echo -e "${GREEN}✨ Configuration terminée avec succès !${NC}"
echo ""
echo "📋 Checklist finale :"
echo "  ✅ Dossiers de stockage créés"
echo "  ✅ Permissions configurées"
echo "  ✅ Fichiers de configuration vérifiés"
echo ""
echo "⚠️  N'oubliez pas de :"
echo "  1. Vérifier que les permissions sont correctes (chmod 775)"
echo "  2. Vérifier que le serveur web peut écrire dans storage/"
echo "  3. Tester un upload de fichier"
echo "  4. Vérifier que les fichiers servis via FileController sont accessibles"


