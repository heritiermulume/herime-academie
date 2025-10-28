#!/bin/bash

echo "======================================"
echo "Script de correction du stockage des bannières"
echo "======================================"
echo ""

# 1. Vérifier la configuration PHP
echo "1. Vérification de la configuration PHP..."
echo "   upload_max_filesize: $(php -r "echo ini_get('upload_max_filesize');")"
echo "   post_max_size: $(php -r "echo ini_get('post_max_size');")"
echo "   memory_limit: $(php -r "echo ini_get('memory_limit');")"
echo ""
echo "   ⚠️  IMPORTANT: Ces valeurs doivent être:"
echo "   - upload_max_filesize >= 20M"
echo "   - post_max_size >= 30M"
echo "   - memory_limit >= 512M"
echo ""

# 2. Créer le lien symbolique storage
echo "2. Création du lien symbolique storage..."
php artisan storage:link
echo ""

# 3. Créer le dossier banners s'il n'existe pas
echo "3. Création du dossier banners..."
mkdir -p storage/app/public/banners
echo ""

# 4. Définir les bonnes permissions
echo "4. Configuration des permissions..."
chmod -R 775 storage
chmod -R 775 bootstrap/cache
chmod -R 775 public/storage
echo ""

# 5. Vérifier les permissions
echo "5. Vérification des permissions..."
ls -la storage/app/public/ | grep banners
ls -la public/ | grep storage
echo ""

# 6. Nettoyer les caches
echo "6. Nettoyage des caches..."
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
echo "- Les valeurs PHP sont suffisantes (voir ci-dessus)"
echo ""
echo "Si les valeurs PHP sont insuffisantes, modifiez le php.ini"
echo "et redémarrez le serveur web."
echo ""

