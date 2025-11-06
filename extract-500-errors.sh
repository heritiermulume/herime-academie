#!/bin/bash

# Script pour extraire spécifiquement les erreurs 500 avec tous les détails

LOG_FILE="storage/logs/laravel.log"
OUTPUT_FILE="errors-500-$(date +%Y%m%d-%H%M%S).txt"

if [ ! -f "$LOG_FILE" ]; then
    echo "❌ Le fichier $LOG_FILE n'existe pas"
    exit 1
fi

echo "=========================================="
echo "EXTRACTION DES ERREURS 500"
echo "=========================================="
echo ""

# Extraire toutes les erreurs 500 avec contexte complet
echo "Recherche des erreurs 500 dans les logs..."
echo ""

# Chercher les erreurs 500 avec contexte étendu
grep -A 50 -B 10 "500\|HTTP 500\|status of 500" "$LOG_FILE" | tail -n 200 > "$OUTPUT_FILE"

if [ -s "$OUTPUT_FILE" ]; then
    echo "✅ Erreurs 500 trouvées et sauvegardées dans: $OUTPUT_FILE"
    echo ""
    echo "Aperçu des erreurs:"
    echo "--------------------------------------------"
    head -n 100 "$OUTPUT_FILE"
    echo ""
    echo "Nombre total de lignes d'erreur: $(wc -l < "$OUTPUT_FILE")"
else
    echo "⚠️  Aucune erreur 500 trouvée dans les logs récents"
    rm -f "$OUTPUT_FILE"
fi

echo ""
echo "=========================================="
echo "ERREURS PAR TYPE"
echo "=========================================="
echo ""

# Compter les types d'erreurs
echo "Erreurs 'Exception':"
grep -c "Exception" "$LOG_FILE" 2>/dev/null || echo "0"
echo ""

echo "Erreurs 'ERROR':"
grep -c "ERROR" "$LOG_FILE" 2>/dev/null || echo "0"
echo ""

echo "Erreurs 'Fatal':"
grep -c "Fatal" "$LOG_FILE" 2>/dev/null || echo "0"
echo ""

echo "=========================================="
echo "DERNIÈRES 5 ERREURS COMPLÈTES"
echo "=========================================="
echo ""

# Extraire les 5 dernières erreurs complètes
tail -n 2000 "$LOG_FILE" | grep -B 5 "ERROR\|Exception" | tail -n 150

echo ""
echo "=========================================="
echo "Pour voir le fichier complet:"
echo "cat $OUTPUT_FILE"
echo "=========================================="

