#!/bin/bash

# Script de déploiement pour la production
# Usage: ./deploy-production.sh

set -e  # Arrêter en cas d'erreur

echo "🚀 Déploiement en production..."

# 1. Pull les dernières modifications
echo "📥 Pull des dernières modifications..."
git pull origin main

# 2. Installer les dépendances Composer
echo "📦 Installation des dépendances Composer..."
composer install --no-dev --optimize-autoloader

# 3. Installer les dépendances NPM
echo "📦 Installation des dépendances NPM..."
npm install

# 4. Compiler les assets pour la production
echo "🎨 Compilation des assets..."
npm run build

# 5. Exécuter les migrations
echo "🗄️  Exécution des migrations..."
php artisan migrate --force

# 6. Vider les caches
echo "🧹 Nettoyage des caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# 7. Optimiser Laravel
echo "⚡ Optimisation de Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# 8. Vérifier les permissions
echo "🔐 Vérification des permissions..."
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || echo "⚠️  Impossible de changer le propriétaire (peut nécessiter sudo)"

echo "✅ Déploiement terminé avec succès!"
echo ""
echo "📋 Prochaines étapes:"
echo "   1. Vérifier les logs: tail -f storage/logs/laravel.log"
echo "   2. Tester le site: curl -I https://votre-domaine.com"
echo "   3. Vérifier les routes: php artisan route:list"

