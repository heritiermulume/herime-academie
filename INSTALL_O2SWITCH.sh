#!/bin/bash

##############################################################################
# Script d'installation pour O2Switch - Herime Académie
# À exécuter sur votre serveur O2Switch via SSH
##############################################################################

echo "═══════════════════════════════════════════════════════════════"
echo "  INSTALLATION HERIME ACADÉMIE SUR O2SWITCH"
echo "═══════════════════════════════════════════════════════════════"
echo ""

# Vérifier la version PHP
echo "📌 Vérification de la version PHP..."
php -v
echo ""

# Vérifier si Composer est installé
if ! command -v composer &> /dev/null; then
    echo "⚠️  Composer n'est pas installé globalement. Utilisation de composer.phar local..."
    COMPOSER="php composer.phar"
else
    echo "✅ Composer est installé"
    COMPOSER="composer"
fi

# Étape 1 : Supprimer le vendor existant s'il y a
echo ""
echo "📦 Nettoyage de l'environnement..."
rm -rf vendor/
rm -rf composer.lock

# Étape 2 : Forcer la compatibilité PHP 8.1
echo ""
echo "🔧 Configuration de la compatibilité PHP 8.1..."

# Créer un composer.local.json compatible
cat > composer.local.json << 'EOF'
{
    "config": {
        "platform": {
            "php": "8.1.33"
        }
    }
}
EOF

# Étape 3 : Installer avec la plateforme PHP 8.1
echo ""
echo "📥 Installation des dépendances (cela peut prendre plusieurs minutes)..."

php -d memory_limit=512M /usr/bin/composer install --no-dev --optimize-autoloader --ignore-platform-reqs

# OU si composer.phar local :
# php -d memory_limit=512M composer.phar install --no-dev --optimize-autoloader --ignore-platform-reqs

# Étape 4 : Créer le .env si absent
echo ""
echo "⚙️  Configuration du fichier .env..."

if [ ! -f .env ]; then
    echo "📝 Copie de .env.example vers .env..."
    cp .env.example .env
    echo "⚠️  IMPORTANT: Modifiez le fichier .env avec vos informations !"
    echo "   nano .env"
fi

# Étape 5 : Générer la clé d'application
echo ""
echo "🔑 Génération de la clé d'application..."
php artisan key:generate

# Étape 6 : Créer les liens symboliques
echo ""
echo "🔗 Création du lien de stockage..."
php artisan storage:link

# Étape 7 : Configurer les permissions
echo ""
echo "📝 Configuration des permissions..."
chmod -R 755 storage bootstrap/cache
chmod -R 644 .env

# Étape 8 : Cache des configurations
echo ""
echo "💾 Mise en cache des configurations..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Étape 9 : Résumé
echo ""
echo "═══════════════════════════════════════════════════════════════"
echo "✅ INSTALLATION TERMINÉE !"
echo "═══════════════════════════════════════════════════════════════"
echo ""
echo "📋 Prochaines étapes :"
echo ""
echo "1️⃣  Éditez le fichier .env :"
echo "   nano .env"
echo ""
echo "2️⃣  Configurez votre base de données :"
echo "   DB_DATABASE=votre_base"
echo "   DB_USERNAME=votre_username"
echo "   DB_PASSWORD=votre_password"
echo ""
echo "3️⃣  Créez les tables :"
echo "   php artisan migrate --force"
echo ""
echo "4️⃣  (Optionnel) Chargez les données de base :"
echo "   php artisan db:seed"
echo ""
echo "═══════════════════════════════════════════════════════════════"

