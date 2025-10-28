#!/bin/bash

echo "🛑 Arrêt de tous les serveurs PHP..."
pkill -9 -f "php.*artisan serve" 2>/dev/null
pkill -9 -f "php.*-S.*8000" 2>/dev/null

# Attendre que les ports se libèrent
sleep 2

# Vérifier qu'aucun processus ne tourne
if lsof -ti:8000 > /dev/null 2>&1; then
    echo "⚠️  Le port 8000 est toujours occupé. Forçage..."
    lsof -ti:8000 | xargs kill -9 2>/dev/null
    sleep 2
fi

echo "🚀 Démarrage du serveur avec limites augmentées..."
cd "$(dirname "$0")"

# Démarrer le serveur avec les options PHP en ligne de commande
PHP_INI_SCAN_DIR=/dev/null php \
    -d upload_max_filesize=20M \
    -d post_max_size=30M \
    -d memory_limit=512M \
    -d max_execution_time=300 \
    -d max_input_time=300 \
    artisan serve

echo ""
echo "Serveur arrêté."

