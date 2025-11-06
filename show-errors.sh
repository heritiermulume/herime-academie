#!/bin/bash

# Script simple pour afficher les erreurs récentes avec précision

LOG_FILE="storage/logs/laravel.log"

if [ ! -f "$LOG_FILE" ]; then
    echo "❌ Le fichier $LOG_FILE n'existe pas"
    exit 1
fi

echo "=========================================="
echo "ERREURS RÉCENTES (Dernières 50 lignes)"
echo "=========================================="
echo ""

# Afficher les dernières erreurs avec contexte
tail -n 200 "$LOG_FILE" | grep -A 30 -B 5 "ERROR\|Exception\|Fatal\|500" | tail -n 100

echo ""
echo "=========================================="
echo "DERNIÈRE ERREUR COMPLÈTE"
echo "=========================================="
echo ""

# Extraire la dernière erreur complète
tail -n 1000 "$LOG_FILE" | grep -B 5 "ERROR" | tail -n 50

echo ""
echo "=========================================="
echo "POUR VOIR EN TEMPS RÉEL:"
echo "tail -f $LOG_FILE | grep -A 20 ERROR"
echo "=========================================="

