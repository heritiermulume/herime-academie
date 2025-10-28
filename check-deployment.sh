#!/bin/bash
# Script de vérification après déploiement sur o2switch

echo "🔍 Vérification du déploiement..."
echo ""

# 1. Vérifier les migrations
echo "📊 État des migrations :"
php artisan migrate:status
echo ""

# 2. Vérifier les bannières
echo "🎨 Bannières en base de données :"
php artisan tinker --execute="echo 'Nombre de bannières: ' . \App\Models\Banner::count(); echo PHP_EOL; \App\Models\Banner::all(['id', 'title', 'is_active'])->each(fn(\$b) => print_r(\$b->toArray()));"
echo ""

# 3. Vérifier les fichiers images
echo "🖼️  Images de bannières :"
ls -lh public/images/hero/ 2>/dev/null || echo "Dossier images/hero non trouvé"
echo ""

# 4. Vérifier les permissions
echo "🔒 Permissions :"
ls -ld storage/
ls -ld bootstrap/cache/
ls -ld public/images/hero/ 2>/dev/null || echo "Dossier images/hero non trouvé"
echo ""

# 5. Vérifier le cache
echo "💾 État du cache :"
php artisan cache:clear
php artisan config:clear
php artisan view:clear
echo "Cache nettoyé"
echo ""

# 6. Vérifier les routes
echo "🛣️  Routes bannières :"
php artisan route:list | grep banner
echo ""

echo "✅ Vérification terminée"

