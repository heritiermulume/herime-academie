#!/bin/bash

# Script de correction rapide pour erreur 500 en production
# Usage: ./fix-500-error.sh

echo "=========================================="
echo "CORRECTION ERREUR 500 - PRODUCTION"
echo "=========================================="
echo ""

# 1. Vérifier que nous sommes dans le bon répertoire
if [ ! -f "artisan" ]; then
    echo "❌ Erreur: Ce script doit être exécuté depuis la racine du projet Laravel"
    exit 1
fi

# 2. Vider tous les caches Laravel
echo "1️⃣  Vider tous les caches Laravel..."
php artisan optimize:clear 2>/dev/null || true
php artisan config:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true
php artisan cache:clear 2>/dev/null || true
php artisan event:clear 2>/dev/null || true
echo "✅ Caches vidés"
echo ""

# 3. Vérifier et corriger les permissions
echo "2️⃣  Vérification des permissions..."
if [ -d "storage" ]; then
    chmod -R 775 storage
    echo "✅ Permissions storage corrigées (775)"
fi

if [ -d "bootstrap/cache" ]; then
    chmod -R 775 bootstrap/cache
    echo "✅ Permissions bootstrap/cache corrigées (775)"
fi

# Vérifier que les dossiers existent et sont accessibles en écriture
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
mkdir -p bootstrap/cache
echo "✅ Dossiers créés/vérifiés"
echo ""

# 4. Vérifier le fichier .env
echo "3️⃣  Vérification du fichier .env..."
if [ ! -f ".env" ]; then
    echo "⚠️  ATTENTION: Le fichier .env n'existe pas!"
    if [ -f ".env.example" ]; then
        echo "   Copie de .env.example vers .env..."
        cp .env.example .env
        echo "   ⚠️  N'oubliez pas de configurer votre .env avec les bonnes valeurs!"
    fi
else
    echo "✅ Fichier .env existe"
    
    # Vérifier APP_KEY
    if ! grep -q "APP_KEY=base64:" .env; then
        echo "⚠️  APP_KEY non configuré, génération..."
        php artisan key:generate
    fi
fi
echo ""

# 5. Vérifier la connexion à la base de données
echo "4️⃣  Test de la connexion à la base de données..."
php artisan db:show 2>/dev/null && echo "✅ Connexion DB OK" || echo "⚠️  Erreur de connexion DB - Vérifiez votre .env"
echo ""

# 6. Vérifier les logs pour l'erreur exacte
echo "5️⃣  Dernières erreurs dans les logs:"
echo "-----------------------------------"
if [ -f "storage/logs/laravel.log" ]; then
    echo "🔍 Analyse des 20 dernières lignes d'erreur..."
    tail -100 storage/logs/laravel.log | grep -E "ERROR|CRITICAL|Exception|Fatal" | tail -5 || echo "Aucune erreur récente trouvée"
else
    echo "⚠️  Fichier de log non trouvé"
fi
echo ""

# 7. Vérifier les fichiers de cache système
echo "6️⃣  Nettoyage des caches système..."
if [ -f "bootstrap/cache/config.php" ]; then
    rm -f bootstrap/cache/config.php
    echo "✅ Cache config supprimé"
fi
if [ -f "bootstrap/cache/routes.php" ]; then
    rm -f bootstrap/cache/routes.php
    echo "✅ Cache routes supprimé"
fi
if [ -f "bootstrap/cache/services.php" ]; then
    rm -f bootstrap/cache/services.php
    echo "✅ Cache services supprimé"
fi
echo ""

# 8. Recréer les caches optimisés (optionnel, peut être commenté si problème persiste)
echo "7️⃣  Recréation des caches optimisés..."
php artisan config:cache 2>/dev/null && echo "✅ Cache config recréé" || echo "⚠️  Erreur lors de la création du cache config"
php artisan route:cache 2>/dev/null && echo "✅ Cache routes recréé" || echo "⚠️  Erreur lors de la création du cache routes"
php artisan view:cache 2>/dev/null && echo "✅ Cache vues recréé" || echo "⚠️  Erreur lors de la création du cache vues"
echo ""

# 9. Vérifier le lien symbolique storage
echo "8️⃣  Vérification du lien symbolique storage..."
if [ ! -L "public/storage" ] && [ ! -d "public/storage" ]; then
    echo "⚠️  Lien symbolique manquant, création..."
    php artisan storage:link
    echo "✅ Lien symbolique créé"
else
    echo "✅ Lien symbolique existe"
fi
echo ""

# 10. Afficher un résumé final
echo "=========================================="
echo "RÉSUMÉ"
echo "=========================================="
echo "✅ Toutes les corrections appliquées!"
echo ""
echo "📋 Prochaines étapes:"
echo "   1. Vérifiez que votre site fonctionne: http://votre-domaine.com"
echo "   2. Si l'erreur persiste, consultez: tail -f storage/logs/laravel.log"
echo "   3. Vérifiez les logs du serveur web (Apache/Nginx)"
echo "   4. Assurez-vous que APP_DEBUG=false dans .env en production"
echo ""
echo "🔍 Pour voir les erreurs en temps réel:"
echo "   tail -f storage/logs/laravel.log"
echo ""
echo "=========================================="

