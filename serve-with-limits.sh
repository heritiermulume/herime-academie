#!/bin/bash

echo "======================================"
echo "🚀 Démarrage du serveur Laravel avec limites PHP augmentées"
echo "======================================"
echo ""

# Définir les limites PHP via des options en ligne de commande
php -d upload_max_filesize=20M \
    -d post_max_size=30M \
    -d memory_limit=512M \
    -d max_execution_time=300 \
    -d max_input_time=300 \
    -d max_input_vars=3000 \
    artisan serve

echo ""
echo "Serveur arrêté."

