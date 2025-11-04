#!/bin/bash

# Script de vérification SSO en production
# Usage: ./SSO_PRODUCTION_VERIFY.sh

echo "🔐 Vérification SSO en Production"
echo "=================================="
echo ""

# Couleurs
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# 1. Vérifier la configuration
echo "1️⃣ Vérification de la configuration..."
echo ""
php artisan config:clear
php artisan sso:test
echo ""

# 2. Vérifier que le cache est activé
echo "2️⃣ Vérification du cache de configuration..."
if php artisan config:show services.sso.enabled > /dev/null 2>&1; then
    echo -e "${GREEN}✅ Cache de configuration actif${NC}"
else
    echo -e "${YELLOW}⚠️  Cache de configuration non actif${NC}"
    echo "   Exécutez: php artisan config:cache"
fi
echo ""

# 3. Vérifier les routes
echo "3️⃣ Vérification des routes SSO..."
php artisan route:list | grep -i sso
echo ""

# 4. Vérifier les dernières entrées dans les logs
echo "4️⃣ Dernières entrées SSO dans les logs:"
if [ -f storage/logs/laravel.log ]; then
    echo ""
    echo "Dernières 10 entrées SSO:"
    grep -i "SSO" storage/logs/laravel.log | tail -10
    echo ""
    
    # Compter les erreurs
    ERROR_COUNT=$(grep -i "SSO.*error\|SSO.*failed\|SSO.*exception" storage/logs/laravel.log | wc -l | tr -d ' ')
    if [ "$ERROR_COUNT" -gt 0 ]; then
        echo -e "${RED}⚠️  $ERROR_COUNT erreur(s) SSO trouvée(s) dans les logs${NC}"
        echo "   Consultez les logs pour plus de détails"
    else
        echo -e "${GREEN}✅ Aucune erreur SSO dans les logs récents${NC}"
    fi
else
    echo -e "${YELLOW}⚠️  Fichier de log non trouvé${NC}"
fi
echo ""

# 5. Test de l'endpoint API
echo "5️⃣ Test de connexion à l'API SSO..."
echo ""
read -p "Voulez-vous tester l'endpoint API? (o/n) " -n 1 -r
echo ""
if [[ $REPLY =~ ^[OoYy]$ ]]; then
    echo "Test de l'endpoint /api/validate-token..."
    curl -X POST https://compte.herime.com/api/validate-token \
      -H "Authorization: Bearer 1d69dac265aab9b5633e96af6f2e4f27f082824f1512b2f7a047bf8f4365e3db" \
      -H "Accept: application/json" \
      -H "Content-Type: application/json" \
      -d '{"token": "test_token"}' \
      -w "\n\nStatus HTTP: %{http_code}\n" \
      -s
    echo ""
fi

# 6. Résumé
echo ""
echo "=================================="
echo "📋 Checklist de Vérification:"
echo ""
echo "Sur le serveur de production, vérifiez:"
echo "  [ ] php artisan sso:test réussit"
echo "  [ ] Redirection vers compte.herime.com fonctionne"
echo "  [ ] Connexion SSO fonctionne"
echo "  [ ] Déconnexion SSO fonctionne"
echo "  [ ] Utilisateurs créés/mis à jour automatiquement"
echo "  [ ] Aucune erreur dans les logs"
echo ""
echo "=================================="
echo "✅ Vérification terminée !"
echo ""

