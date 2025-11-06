#!/bin/bash

# Script de diagnostic précis pour l'erreur 500
# Affiche les erreurs avec précision depuis les logs Laravel

echo "=========================================="
echo "DIAGNOSTIC PRÉCIS DE L'ERREUR 500"
echo "=========================================="
echo ""

# Couleurs pour la sortie
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# 1. Vérifier les dernières erreurs dans les logs
echo -e "${YELLOW}1. DERNIÈRES ERREURS DANS LES LOGS LARAVEL${NC}"
echo "--------------------------------------------"
if [ -f "storage/logs/laravel.log" ]; then
    echo "Dernières 100 lignes contenant 'ERROR' ou 'Exception':"
    echo ""
    tail -n 200 storage/logs/laravel.log | grep -A 20 -B 5 "ERROR\|Exception\|Fatal\|500" | tail -n 100
    echo ""
    echo "Dernière erreur complète:"
    echo ""
    tail -n 500 storage/logs/laravel.log | grep -A 50 "ERROR" | tail -n 60
else
    echo -e "${RED}Le fichier storage/logs/laravel.log n'existe pas${NC}"
fi
echo ""
echo ""

# 2. Vérifier les erreurs récentes (dernières 10 minutes)
echo -e "${YELLOW}2. ERREURS DES 10 DERNIÈRES MINUTES${NC}"
echo "--------------------------------------------"
if [ -f "storage/logs/laravel.log" ]; then
    # Chercher les erreurs des 10 dernières minutes
    tail -n 1000 storage/logs/laravel.log | grep -A 30 "$(date -d '10 minutes ago' '+%Y-%m-%d %H:%M')" | grep -A 30 "ERROR\|Exception" | head -n 50
else
    echo -e "${RED}Le fichier storage/logs/laravel.log n'existe pas${NC}"
fi
echo ""
echo ""

# 3. Vérifier les permissions des fichiers
echo -e "${YELLOW}3. VÉRIFICATION DES PERMISSIONS${NC}"
echo "--------------------------------------------"
echo "Permissions storage/logs/:"
ls -la storage/logs/ | head -5
echo ""
echo "Permissions bootstrap/cache/:"
ls -la bootstrap/cache/ | head -5
echo ""
echo "Permissions storage/framework/:"
ls -la storage/framework/ | head -5
echo ""

# 4. Vérifier la configuration SSO
echo -e "${YELLOW}4. CONFIGURATION SSO${NC}"
echo "--------------------------------------------"
php artisan tinker --execute="
try {
    echo 'SSO Enabled: ' . (config('services.sso.enabled') ? 'Yes' : 'No') . PHP_EOL;
    echo 'SSO Base URL: ' . (config('services.sso.base_url') ?: 'Not set') . PHP_EOL;
    echo 'SSO Secret: ' . (config('services.sso.secret') ? 'Set' : 'Not set') . PHP_EOL;
} catch (\Exception \$e) {
    echo 'Error: ' . \$e->getMessage() . PHP_EOL;
}
" 2>&1
echo ""

# 5. Tester les routes problématiques
echo -e "${YELLOW}5. TEST DES ROUTES PROBLÉMATIQUES${NC}"
echo "--------------------------------------------"
echo "Test de la route /me:"
php artisan tinker --execute="
try {
    \$request = \Illuminate\Http\Request::create('/me', 'GET');
    \$response = app()->handle(\$request);
    echo 'Status: ' . \$response->getStatusCode() . PHP_EOL;
    echo 'Content: ' . \$response->getContent() . PHP_EOL;
} catch (\Exception \$e) {
    echo 'Error: ' . \$e->getMessage() . PHP_EOL;
    echo 'File: ' . \$e->getFile() . ':' . \$e->getLine() . PHP_EOL;
}
" 2>&1
echo ""

# 6. Vérifier les routes avec middleware sso.validate
echo -e "${YELLOW}6. ROUTES AVEC MIDDLEWARE SSO.VALIDATE${NC}"
echo "--------------------------------------------"
grep -n "sso.validate" routes/web.php | head -10
echo ""

# 7. Vérifier les erreurs PHP
echo -e "${YELLOW}7. ERREURS PHP RÉCENTES${NC}"
echo "--------------------------------------------"
if [ -f "/var/log/php_errors.log" ]; then
    tail -n 50 /var/log/php_errors.log | grep -A 10 "ERROR\|Fatal\|Warning" | tail -n 30
elif [ -f "/var/log/php-fpm/error.log" ]; then
    tail -n 50 /var/log/php-fpm/error.log | grep -A 10 "ERROR\|Fatal\|Warning" | tail -n 30
else
    echo "Fichier de log PHP non trouvé dans les emplacements standards"
fi
echo ""

# 8. Vérifier la session
echo -e "${YELLOW}8. CONFIGURATION DE LA SESSION${NC}"
echo "--------------------------------------------"
php artisan tinker --execute="
echo 'Session Driver: ' . config('session.driver') . PHP_EOL;
echo 'Session Lifetime: ' . config('session.lifetime') . PHP_EOL;
echo 'Session Path: ' . config('session.files') . PHP_EOL;
" 2>&1
echo ""

# 9. Vérifier les erreurs de validation du token SSO
echo -e "${YELLOW}9. TEST DE VALIDATION DU TOKEN SSO${NC}"
echo "--------------------------------------------"
php artisan tinker --execute="
try {
    \$ssoService = app(\App\Services\SSOService::class);
    \$testToken = 'test_token_invalid';
    \$result = \$ssoService->checkToken(\$testToken);
    echo 'Test checkToken avec token invalide: ' . (\$result ? 'true' : 'false') . PHP_EOL;
} catch (\Throwable \$e) {
    echo 'Erreur lors du test: ' . \$e->getMessage() . PHP_EOL;
    echo 'Fichier: ' . \$e->getFile() . ':' . \$e->getLine() . PHP_EOL;
    echo 'Trace: ' . \$e->getTraceAsString() . PHP_EOL;
}
" 2>&1
echo ""

# 10. Vérifier les dernières requêtes qui ont causé des erreurs
echo -e "${YELLOW}10. DERNIÈRES REQUÊTES AVEC ERREURS${NC}"
echo "--------------------------------------------"
if [ -f "storage/logs/laravel.log" ]; then
    echo "Dernières requêtes qui ont causé des erreurs:"
    tail -n 1000 storage/logs/laravel.log | grep -B 5 "ERROR\|Exception" | grep -E "GET|POST|PUT|DELETE|PATCH" | tail -n 10
fi
echo ""

# 11. Vérifier les erreurs de base de données
echo -e "${YELLOW}11. ERREURS DE BASE DE DONNÉES${NC}"
echo "--------------------------------------------"
if [ -f "storage/logs/laravel.log" ]; then
    tail -n 500 storage/logs/laravel.log | grep -A 10 "SQLSTATE\|QueryException\|Database" | tail -n 30
fi
echo ""

# 12. Vérifier les erreurs de mémoire
echo -e "${YELLOW}12. ERREURS DE MÉMOIRE${NC}"
echo "--------------------------------------------"
if [ -f "storage/logs/laravel.log" ]; then
    tail -n 500 storage/logs/laravel.log | grep -A 5 "memory\|Memory\|Allowed memory" | tail -n 20
fi
echo ""

# 13. Afficher le résumé des erreurs
echo -e "${YELLOW}13. RÉSUMÉ DES ERREURS${NC}"
echo "--------------------------------------------"
if [ -f "storage/logs/laravel.log" ]; then
    echo "Nombre d'erreurs dans les dernières 1000 lignes:"
    tail -n 1000 storage/logs/laravel.log | grep -c "ERROR\|Exception\|Fatal"
    echo ""
    echo "Types d'erreurs les plus fréquents:"
    tail -n 1000 storage/logs/laravel.log | grep "ERROR\|Exception" | sed 's/.*\[\(.*\)\].*/\1/' | sort | uniq -c | sort -rn | head -10
fi
echo ""

# 14. Vérifier les routes qui pourraient causer des problèmes
echo -e "${YELLOW}14. VÉRIFICATION DES ROUTES PROBLÉMATIQUES${NC}"
echo "--------------------------------------------"
php artisan route:list | grep -E "me|logout" | head -10
echo ""

# 15. Test de la route /me avec curl (si disponible)
echo -e "${YELLOW}15. TEST DE LA ROUTE /me AVEC CURL${NC}"
echo "--------------------------------------------"
if command -v curl &> /dev/null; then
    echo "Test de GET /me (sans authentification):"
    curl -s -o /dev/null -w "Status: %{http_code}\n" http://localhost/me 2>&1 || echo "Impossible de tester (serveur non accessible)"
else
    echo "curl n'est pas disponible"
fi
echo ""

echo "=========================================="
echo "FIN DU DIAGNOSTIC"
echo "=========================================="
echo ""
echo -e "${GREEN}Pour voir les erreurs en temps réel, utilisez:${NC}"
echo "tail -f storage/logs/laravel.log | grep -A 20 ERROR"
echo ""

