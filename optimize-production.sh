#!/bin/bash

# Script de nettoyage et optimisation pour la production Laravel
# Usage: ./optimize-production.sh

set -e  # Arrêter en cas d'erreur

echo "🧹 Nettoyage et optimisation de l'application Laravel..."
echo ""

# 1. Nettoyer tous les caches
echo "1️⃣  Nettoyage des caches..."
php artisan optimize:clear

# 2. Nettoyer les fichiers compilés
echo "2️⃣  Nettoyage des fichiers compilés..."
php artisan clear-compiled
rm -rf bootstrap/cache/*.php 2>/dev/null || true

# 3. Optimiser Composer
echo "3️⃣  Optimisation de Composer..."
if [ -f "composer.json" ]; then
    composer dump-autoload --optimize --classmap-authoritative --no-dev 2>/dev/null || \
    composer dump-autoload --optimize --classmap-authoritative
else
    echo "   ⚠️  composer.json non trouvé, ignoré"
fi

# 4. Optimiser les caches Laravel
echo "4️⃣  Optimisation des caches Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 5. Compiler les assets frontend (si nécessaire)
echo "5️⃣  Compilation des assets frontend..."
if [ -f "package.json" ]; then
    if command -v npm &> /dev/null; then
        npm run build
    else
        echo "   ⚠️  npm non trouvé, ignoré"
    fi
else
    echo "   ℹ️  package.json non trouvé, ignoré"
fi

# 6. Nettoyer les sessions expirées
echo "6️⃣  Nettoyage des sessions expirées..."
php artisan session:gc 2>/dev/null || echo "   ⚠️  Commande session:gc non disponible"

# 7. Afficher les permissions (suggestion)
echo "7️⃣  Vérification des permissions..."
if [ -d "storage" ]; then
    chmod -R 775 storage 2>/dev/null || echo "   ⚠️  Impossible de modifier les permissions de storage"
fi
if [ -d "bootstrap/cache" ]; then
    chmod -R 775 bootstrap/cache 2>/dev/null || echo "   ⚠️  Impossible de modifier les permissions de bootstrap/cache"
fi

echo ""
echo "✅ Optimisation terminée !"
echo ""
echo "📊 Résumé :"
echo "   - Caches nettoyés et optimisés"
echo "   - Composer optimisé"
echo "   - Assets frontend compilés"
echo "   - Sessions expirées nettoyées"
echo ""
echo "💡 Pour plus d'informations, consultez OPTIMIZE_PRODUCTION.md"

