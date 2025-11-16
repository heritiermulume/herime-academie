#!/bin/bash

# Script de diagnostic pour production
# Usage: ./run-production-diagnostic.sh

echo "=========================================="
echo "DIAGNOSTIC ERREUR 500 - PRODUCTION"
echo "=========================================="
echo ""

# 1. Vider tous les caches
echo "1. Vider tous les caches..."
php artisan optimize:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
echo "✓ Caches vidés"
echo ""

# 2. Exécuter le script de diagnostic
echo "2. Exécution du diagnostic..."
php diagnose-500-error.php > diagnostic-result.txt 2>&1
echo "✓ Diagnostic terminé (résultat dans diagnostic-result.txt)"
echo ""

# 3. Exécuter le test de production
echo "3. Test de production..."
php test-production-500.php > test-production-result.txt 2>&1
echo "✓ Test terminé (résultat dans test-production-result.txt)"
echo ""

# 4. Afficher les dernières erreurs
echo "4. Dernières erreurs dans les logs:"
echo "-----------------------------------"
tail -50 storage/logs/laravel.log | grep -E "ERROR|CRITICAL|Exception" | tail -10
echo ""

# 5. Vérifier les permissions
echo "5. Vérification des permissions..."
chmod -R 775 storage bootstrap/cache 2>/dev/null
echo "✓ Permissions vérifiées"
echo ""

echo "=========================================="
echo "FIN DU DIAGNOSTIC"
echo "=========================================="
echo ""
echo "Consultez les fichiers:"
echo "  - diagnostic-result.txt"
echo "  - test-production-result.txt"
echo ""















