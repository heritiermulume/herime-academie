#!/bin/bash

# Script de diagnostic détaillé pour l'erreur 500

echo "=== Diagnostic détaillé de l'erreur 500 ==="
echo ""

# 1. Vérifier les dernières erreurs dans les logs
echo "1. Dernières erreurs dans les logs Laravel :"
echo "--------------------------------------------"
tail -n 50 storage/logs/laravel.log | grep -A 10 "ERROR\|Exception\|Fatal" | tail -n 30
echo ""

# 2. Vérifier les permissions
echo "2. Vérification des permissions :"
echo "--------------------------------------------"
ls -la storage/logs/ | head -5
ls -la bootstrap/cache/ | head -5
echo ""

# 3. Vérifier la configuration SSO
echo "3. Vérification de la configuration SSO :"
echo "--------------------------------------------"
php artisan tinker --execute="echo 'SSO Enabled: ' . (config('services.sso.enabled') ? 'Yes' : 'No') . PHP_EOL; echo 'SSO Base URL: ' . config('services.sso.base_url') . PHP_EOL;"
echo ""

# 4. Vérifier les routes avec middleware sso.validate
echo "4. Vérification des routes avec middleware sso.validate :"
echo "--------------------------------------------"
grep -n "sso.validate" routes/web.php | head -10
echo ""

# 5. Tester la validation du token SSO
echo "5. Test de la validation du token SSO :"
echo "--------------------------------------------"
php artisan tinker --execute="
try {
    \$ssoService = app(\App\Services\SSOService::class);
    \$testToken = 'test_token';
    \$result = \$ssoService->checkToken(\$testToken);
    echo 'Test checkToken: ' . (\$result ? 'true' : 'false') . PHP_EOL;
} catch (\Exception \$e) {
    echo 'Erreur lors du test: ' . \$e->getMessage() . PHP_EOL;
}
"
echo ""

# 6. Vérifier la session
echo "6. Vérification de la session :"
echo "--------------------------------------------"
php artisan tinker --execute="echo 'Session Driver: ' . config('session.driver') . PHP_EOL;"
echo ""

# 7. Vérifier les routes qui pourraient causer des problèmes
echo "7. Vérification des routes problématiques :"
echo "--------------------------------------------"
php artisan route:list | grep -E "me|logout" | head -10
echo ""

echo "=== Fin du diagnostic ==="

