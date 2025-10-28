#!/bin/bash

echo "======================================"
echo "Configuration PHP en local pour Herime Academie"
echo "======================================"
echo ""

# Chemin du fichier de configuration PHP
PHP_INI_DIR="/usr/local/etc/php/8.4/conf.d"
CONFIG_FILE="$PHP_INI_DIR/herime-academie.ini"

echo "1. Vérification des limites actuelles..."
echo "   upload_max_filesize: $(php -r "echo ini_get('upload_max_filesize');")"
echo "   post_max_size: $(php -r "echo ini_get('post_max_size');")"
echo "   memory_limit: $(php -r "echo ini_get('memory_limit');")"
echo ""

echo "2. Création du fichier de configuration personnalisé..."
echo "   Fichier: $CONFIG_FILE"
echo ""

# Créer le fichier de configuration
sudo tee "$CONFIG_FILE" > /dev/null <<EOF
; Configuration PHP pour Herime Academie (développement local)
; Ce fichier sera automatiquement chargé par PHP

; Limites d'upload pour les bannières (2 images)
upload_max_filesize = 20M
post_max_size = 30M
max_file_uploads = 20

; Limites d'exécution
max_execution_time = 300
max_input_time = 300
memory_limit = 512M

; Limites de variables
max_input_vars = 3000
max_input_nesting_level = 64
EOF

if [ $? -eq 0 ]; then
    echo "✅ Fichier de configuration créé avec succès !"
else
    echo "❌ Erreur lors de la création du fichier"
    echo ""
    echo "Si vous n'avez pas les droits sudo, modifiez manuellement :"
    echo "   sudo nano /usr/local/etc/php/8.4/php.ini"
    echo ""
    echo "Et ajoutez ces lignes :"
    echo "   upload_max_filesize = 20M"
    echo "   post_max_size = 30M"
    echo "   memory_limit = 512M"
    exit 1
fi

echo ""
echo "3. Vérification des nouvelles limites..."
echo "   upload_max_filesize: $(php -r "echo ini_get('upload_max_filesize');")"
echo "   post_max_size: $(php -r "echo ini_get('post_max_size');")"
echo "   memory_limit: $(php -r "echo ini_get('memory_limit');")"
echo ""

echo "======================================"
echo "Configuration terminée !"
echo "======================================"
echo ""
echo "⚠️  Si les valeurs n'ont pas changé, redémarrez le serveur Laravel :"
echo "   1. Arrêtez le serveur (Ctrl+C)"
echo "   2. Relancez : php artisan serve"
echo ""

