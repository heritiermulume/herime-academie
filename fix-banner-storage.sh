#!/bin/bash

echo "======================================"
echo "Script de correction du stockage des bannières"
echo "======================================"
echo ""

# 1. Créer le lien symbolique storage
echo "1. Création du lien symbolique storage..."
php artisan storage:link
echo ""

# 2. Créer le dossier banners s'il n'existe pas
echo "2. Création du dossier banners..."
mkdir -p storage/app/public/banners
echo ""

# 3. Définir les bonnes permissions
echo "3. Configuration des permissions..."
chmod -R 775 storage
chmod -R 775 bootstrap/cache
chmod -R 775 public/storage
echo ""

# 4. Vérifier les permissions
echo "4. Vérification des permissions..."
ls -la storage/app/public/ | grep banners
ls -la public/ | grep storage
echo ""

# 5. Nettoyer les caches
echo "5. Nettoyage des caches..."
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
echo ""

echo "======================================"
echo "Configuration terminée !"
echo "======================================"
echo ""
echo "Vérifications à faire manuellement :"
echo "- Le dossier storage/app/public/banners existe"
echo "- Le lien symbolique public/storage pointe vers storage/app/public"
echo "- Les permissions sont 775 sur storage et public/storage"
echo ""

