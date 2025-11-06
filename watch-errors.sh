#!/bin/bash

# Script pour surveiller les erreurs en temps réel

LOG_FILE="storage/logs/laravel.log"

if [ ! -f "$LOG_FILE" ]; then
    echo "❌ Le fichier $LOG_FILE n'existe pas"
    exit 1
fi

echo "=========================================="
echo "SURVEILLANCE DES ERREURS EN TEMPS RÉEL"
echo "Appuyez sur Ctrl+C pour arrêter"
echo "=========================================="
echo ""

# Surveiller les erreurs en temps réel avec coloration
tail -f "$LOG_FILE" | grep --line-buffered -A 20 --color=always "ERROR\|Exception\|Fatal\|500"

