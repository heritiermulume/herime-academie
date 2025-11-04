#!/bin/bash

# Script de test manuel du SSO
# Usage: ./scripts/test-sso-manual.sh

echo "🧪 Test Manuel du SSO"
echo "===================="
echo ""

# Couleurs
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# 1. Vérifier la configuration
echo "1️⃣ Vérification de la configuration..."
php artisan sso:test
echo ""

# 2. Vérifier les logs
echo "2️⃣ Dernières entrées SSO dans les logs:"
if [ -f storage/logs/laravel.log ]; then
    echo ""
    grep -i "SSO" storage/logs/laravel.log | tail -5
    echo ""
else
    echo -e "${YELLOW}⚠️  Aucun fichier de log trouvé${NC}"
fi

# 3. Tester l'endpoint API
echo "3️⃣ Test de l'endpoint API SSO..."
echo ""

read -p "Voulez-vous tester l'endpoint API? (o/n) " -n 1 -r
echo ""
if [[ $REPLY =~ ^[OoYy]$ ]]; then
    echo "Envoi d'une requête de test..."
    curl -X POST https://compte.herime.com/api/validate-token \
      -H "Authorization: Bearer 1d69dac265aab9b5633e96af6f2e4f27f082824f1512b2f7a047bf8f4365e3db" \
      -H "Accept: application/json" \
      -H "Content-Type: application/json" \
      -d '{"token": "test_connection_token"}' \
      -w "\n\nStatus HTTP: %{http_code}\n" \
      -s
    echo ""
fi

# 4. Instructions
echo ""
echo "4️⃣ Instructions pour tester manuellement:"
echo ""
echo -e "${GREEN}✅ Test de redirection:${NC}"
echo "   Visitez: https://academie.herime.com/login"
echo "   Vous devriez être redirigé vers compte.herime.com"
echo ""
echo -e "${GREEN}✅ Test de connexion:${NC}"
echo "   1. Connectez-vous sur compte.herime.com"
echo "   2. Vous devriez être redirigé vers academie.herime.com/sso/callback"
echo "   3. Vous devriez être connecté automatiquement"
echo ""
echo -e "${GREEN}✅ Test de déconnexion:${NC}"
echo "   1. Déconnectez-vous sur academie.herime.com"
echo "   2. Vous devriez être redirigé vers compte.herime.com/logout"
echo ""
echo "===================="
echo "✅ Tests terminés !"
echo ""

