#!/bin/bash

# Script pour vider le cache des routes et config sur le serveur de production
# À exécuter après un git pull

echo "=== Nettoyage du cache Laravel ==="

# Vider le cache des routes
php artisan route:clear
echo "✓ Cache des routes vidé"

# Vider le cache de configuration
php artisan config:clear
echo "✓ Cache de configuration vidé"

# Vider le cache des vues
php artisan view:clear
echo "✓ Cache des vues vidé"

# Vider le cache de l'application
php artisan cache:clear
echo "✓ Cache de l'application vidé"

# Recréer le cache de configuration (optionnel, pour la performance)
# php artisan config:cache
# echo "✓ Cache de configuration recréé"

# Lister les routes pour vérifier que profile.redirect existe
echo ""
echo "=== Vérification de la route profile.redirect ==="
php artisan route:list | grep "profile.redirect" && echo "✓ Route profile.redirect trouvée" || echo "✗ Route profile.redirect NON trouvée"

echo ""
echo "=== Fin du nettoyage ==="

