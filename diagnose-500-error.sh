#!/bin/bash

# Script de diagnostic pour l'erreur 500
# À exécuter sur le serveur de production

echo "=== Diagnostic de l'erreur 500 ==="
echo ""

echo "1. Dernières erreurs dans les logs Laravel :"
echo "--------------------------------------------"
tail -n 100 storage/logs/laravel.log | grep -A 10 "ERROR\|Exception" | tail -30
echo ""

echo "2. Vérification de la configuration SSO :"
echo "--------------------------------------------"
php artisan tinker --execute="echo 'SSO Enabled: ' . (config('services.sso.enabled') ? 'Yes' : 'No') . PHP_EOL; echo 'SSO Base URL: ' . config('services.sso.base_url') . PHP_EOL;"
echo ""

echo "3. Vérification des routes avec middleware sso.validate :"
echo "--------------------------------------------"
grep -r "sso.validate" routes/ || echo "Aucune route trouvée avec sso.validate"
echo ""

echo "4. Vérification de la session :"
echo "--------------------------------------------"
php artisan tinker --execute="echo 'Session Driver: ' . config('session.driver') . PHP_EOL;"
echo ""

echo "5. Test de connexion à l'API SSO (si configurée) :"
echo "--------------------------------------------"
SSO_URL=$(php artisan tinker --execute="echo config('services.sso.base_url');" 2>/dev/null | tail -1)
if [ ! -z "$SSO_URL" ]; then
    echo "Test de connexion à: $SSO_URL/api/sso/check-token"
    curl -X POST "$SSO_URL/api/sso/check-token" \
        -H "Content-Type: application/json" \
        -H "Accept: application/json" \
        -d '{"token":"test"}' \
        --max-time 5 \
        -w "\nHTTP Status: %{http_code}\n" 2>&1 | head -5
else
    echo "SSO URL non configurée"
fi
echo ""

echo "=== Fin du diagnostic ==="

