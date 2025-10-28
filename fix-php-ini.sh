#!/bin/bash

echo "======================================"
echo "🔧 Configuration PHP pour Herime Academie"
echo "======================================"
echo ""

PHP_INI="/usr/local/etc/php/8.4/php.ini"

echo "📍 Fichier PHP : $PHP_INI"
echo ""

echo "📊 Limites actuelles :"
echo "   upload_max_filesize: $(php -r "echo ini_get('upload_max_filesize');")"
echo "   post_max_size: $(php -r "echo ini_get('post_max_size');")"
echo "   memory_limit: $(php -r "echo ini_get('memory_limit');")"
echo ""

echo "🔄 Modification du fichier php.ini..."
echo "   (Mot de passe administrateur requis)"
echo ""

# Backup du fichier original
sudo cp "$PHP_INI" "$PHP_INI.backup.$(date +%Y%m%d_%H%M%S)"

# Modifier les valeurs
sudo sed -i '' 's/^upload_max_filesize = .*/upload_max_filesize = 20M/' "$PHP_INI"
sudo sed -i '' 's/^post_max_size = .*/post_max_size = 30M/' "$PHP_INI"
sudo sed -i '' 's/^memory_limit = .*/memory_limit = 512M/' "$PHP_INI"

if [ $? -eq 0 ]; then
    echo "✅ Fichier php.ini modifié avec succès !"
    echo ""
    echo "📊 Nouvelles limites :"
    echo "   upload_max_filesize: $(php -r "echo ini_get('upload_max_filesize');")"
    echo "   post_max_size: $(php -r "echo ini_get('post_max_size');")"
    echo "   memory_limit: $(php -r "echo ini_get('memory_limit');")"
    echo ""
    echo "======================================"
    echo "✅ Configuration terminée !"
    echo "======================================"
    echo ""
    echo "📝 Prochaines étapes :"
    echo "   1. Arrêtez le serveur si en cours (Ctrl+C)"
    echo "   2. Redémarrez : php artisan serve"
    echo "   3. Testez : http://127.0.0.1:8000/test-limits.php"
    echo ""
else
    echo "❌ Erreur lors de la modification"
    echo ""
    echo "Solution manuelle :"
    echo "   1. Ouvrez : sudo nano $PHP_INI"
    echo "   2. Modifiez :"
    echo "      upload_max_filesize = 20M"
    echo "      post_max_size = 30M"
    echo "      memory_limit = 512M"
    echo "   3. Sauvegardez : Ctrl+O, Entrée, Ctrl+X"
    exit 1
fi

