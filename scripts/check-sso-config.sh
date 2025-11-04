#!/bin/bash

# Script de vérification de la configuration SSO
# Usage: ./scripts/check-sso-config.sh

echo "🔐 Vérification de la configuration SSO"
echo "========================================"
echo ""

# Vérifier si .env existe
if [ ! -f .env ]; then
    echo "❌ Fichier .env non trouvé !"
    exit 1
fi

echo "✅ Fichier .env trouvé"
echo ""

# Vérifier les variables SSO
echo "📋 Vérification des variables SSO:"
echo ""

SSO_ENABLED=$(grep "^SSO_ENABLED=" .env | cut -d '=' -f2)
SSO_BASE_URL=$(grep "^SSO_BASE_URL=" .env | cut -d '=' -f2)
SSO_SECRET=$(grep "^SSO_SECRET=" .env | cut -d '=' -f2)
SSO_TIMEOUT=$(grep "^SSO_TIMEOUT=" .env | cut -d '=' -f2)

# SSO_ENABLED
if [ -z "$SSO_ENABLED" ]; then
    echo "❌ SSO_ENABLED: Non configuré"
else
    echo "✅ SSO_ENABLED: $SSO_ENABLED"
fi

# SSO_BASE_URL
if [ -z "$SSO_BASE_URL" ]; then
    echo "❌ SSO_BASE_URL: Non configuré"
else
    echo "✅ SSO_BASE_URL: $SSO_BASE_URL"
fi

# SSO_SECRET
if [ -z "$SSO_SECRET" ]; then
    echo "❌ SSO_SECRET: Non configuré"
else
    SECRET_LENGTH=${#SSO_SECRET}
    if [ "$SECRET_LENGTH" -eq 64 ]; then
        echo "✅ SSO_SECRET: Configuré ($SECRET_LENGTH caractères)"
    else
        echo "⚠️  SSO_SECRET: Configuré mais longueur incorrecte ($SECRET_LENGTH caractères, attendu: 64)"
    fi
fi

# SSO_TIMEOUT
if [ -z "$SSO_TIMEOUT" ]; then
    echo "⚠️  SSO_TIMEOUT: Non configuré (utilisera la valeur par défaut: 10)"
else
    echo "✅ SSO_TIMEOUT: $SSO_TIMEOUT secondes"
fi

echo ""
echo "========================================"
echo ""

# Vérifier la clé secrète attendue
EXPECTED_SECRET="1d69dac265aab9b5633e96af6f2e4f27f082824f1512b2f7a047bf8f4365e3db"
if [ "$SSO_SECRET" = "$EXPECTED_SECRET" ]; then
    echo "✅ Clé secrète correspond à celle attendue"
else
    echo "⚠️  Clé secrète différente de celle attendue"
    echo "   Attendu: $EXPECTED_SECRET"
    echo "   Actuel: $SSO_SECRET"
fi

echo ""
echo "🧪 Pour tester la configuration, exécutez:"
echo "   php artisan sso:test"
echo ""

