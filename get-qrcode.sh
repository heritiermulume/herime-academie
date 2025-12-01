#!/bin/bash

# Script pour obtenir le QR code de l'instance WhatsApp

API_KEY="e20d827cf706399860c46f6b9f11e55ac4cbb77d0cbe5548648937727a4e55d2"
INSTANCE_NAME="default"
BASE_URL="http://localhost:8080"

echo "📱 Récupération du QR code pour l'instance: $INSTANCE_NAME"
echo ""

# Vérifier l'état de l'instance
echo "🔍 Vérification de l'état de l'instance..."
STATE=$(curl -s "$BASE_URL/instance/connectionState/$INSTANCE_NAME" \
  -H "apikey: $API_KEY" | python3 -c "import sys, json; print(json.load(sys.stdin).get('instance', {}).get('state', 'unknown'))" 2>/dev/null)

echo "   État actuel: $STATE"
echo ""

if [ "$STATE" = "open" ]; then
    echo "✅ L'instance est déjà connectée à WhatsApp!"
    exit 0
fi

echo "📲 Tentative de récupération du QR code..."
echo ""

# Essayer différents endpoints
QR_RESPONSE=$(curl -s "$BASE_URL/instance/connect/$INSTANCE_NAME" \
  -H "apikey: $API_KEY" 2>&1)

if echo "$QR_RESPONSE" | grep -q "base64\|qrcode\|data:image"; then
    echo "✅ QR code trouvé!"
    echo "$QR_RESPONSE" | python3 -m json.tool 2>/dev/null | head -20
else
    echo "⚠️  QR code non disponible via API"
    echo ""
    echo "💡 Solutions:"
    echo "   1. Ouvrez dans votre navigateur: $BASE_URL/manager"
    echo "   2. Sélectionnez l'instance '$INSTANCE_NAME'"
    echo "   3. Cliquez sur 'Connect' ou 'QR Code'"
    echo ""
    echo "   Ou visitez directement:"
    echo "   $BASE_URL/instance/connect/$INSTANCE_NAME"
    echo ""
    echo "Réponse de l'API:"
    echo "$QR_RESPONSE" | head -10
fi

