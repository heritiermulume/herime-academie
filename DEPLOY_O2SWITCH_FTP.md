# Déploiement O2Switch - Méthode FTP/ZIP (SIMPLE)

## ✅ Méthode recommandée pour débuter

Cette méthode est la plus simple et ne nécessite pas Git sur le serveur O2Switch.

## 📥 Étape 1 : Télécharger le projet

1. **Aller sur GitHub** : https://github.com/heritiermulume/herime-academie
2. **Cliquer sur le bouton vert "Code"**
3. **Sélectionner "Download ZIP"**
4. **Extraire le ZIP** sur votre ordinateur local

## 📤 Étape 2 : Uploader via FTP

### Utiliser FileZilla (recommandé)

1. **Télécharger FileZilla** : https://filezilla-project.org/
2. **Se connecter au serveur O2Switch** :
   - Host : `ftp.o2switch.net` (ou l'adresse fournie par O2Switch)
   - Username : votre identifiant O2Switch
   - Password : votre mot de passe O2Switch
   - Port : 21

3. **Uploader les fichiers** :
   - Dans FileZilla, aller dans le dossier `www` ou `htdocs` ou `public_html`
   - Glisser-déposer tous les fichiers du projet dans ce dossier
   - **ATTENTION** : La racine doit pointer vers le dossier `public/` de Laravel

### Structure sur O2Switch

```
/home/votre-compte/www/
├── .env
├── .htaccess (déjà inclus)
├── app/
├── bootstrap/
├── config/
├── database/
├── public/  ← RACINE WEB
├── resources/
├── routes/
└── vendor/
```

## ⚙️ Étape 3 : Configuration

### Modifier le répertoire racine (via O2Switch)

1. **Connectez-vous à votre panneau O2Switch**
2. **Allez dans la gestion de votre domaine**
3. **Changez le "Répertoire racine" vers** : `/home/votre-compte/www/public`

### Créer le fichier .env

```bash
# Se connecter en SSH au serveur
ssh votre-compte@votre-serveur.o2switch.net
cd www

# Copier le fichier .env.example
cp .env.example .env
```

### Modifier .env sur le serveur

Éditez le fichier `.env` avec nano ou vi :
```bash
nano .env
```

Contenu du `.env` pour O2Switch :
```env
APP_NAME="Herime Académie"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://academie.herime.com

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=votre_base_de_donnees
DB_USERNAME=votre_username_sql
DB_PASSWORD=votre_password_sql

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file

# MaxiCash Production
MAXICASH_MERCHANT_ID=votre_merchant_id
MAXICASH_MERCHANT_PASSWORD=votre_password
MAXICASH_SANDBOX=false
MAXICASH_API_URL=https://api.maxicashapp.com/Merchant/api.asmx
MAXICASH_GATEWAY_URL=https://api.maxicashapp.com/PayEntryPost

# Mail O2Switch
MAIL_MAILER=smtp
MAIL_HOST=smtp.o2switch.net
MAIL_PORT=587
MAIL_USERNAME=votre-email@herime.com
MAIL_PASSWORD=votre-password-email
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="contact@herime.com"
MAIL_FROM_NAME="${APP_NAME}"
```

## 🔧 Étape 4 : Installation

### Installer les dépendances

```bash
# Se connecter en SSH
ssh votre-compte@votre-serveur.o2switch.net
cd www

# Installer Composer (si pas déjà installé)
curl -sS https://getcomposer.org/installer | php

# Installer les dépendances
php composer.phar install --no-dev --optimize-autoloader

# OU si Composer est installé globalement
composer install --no-dev --optimize-autoloader
```

### Générer la clé d'application

```bash
php artisan key:generate
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Créer les tables de base de données

```bash
# Créer la base de données dans phpMyAdmin O2Switch d'abord
php artisan migrate --force
php artisan db:seed  # (optionnel)
```

### Créer le lien symbolique

```bash
php artisan storage:link
```

### Configurer les permissions

```bash
chmod -R 755 storage bootstrap/cache
chmod -R 644 .env
```

## 📋 Étape 5 : Configuration O2Switch

### Dans le panneau O2Switch

1. **Gestion du domaine** > **Répertoire racine** > `/home/votre-compte/www/public`

### phpMyAdmin pour la base de données

1. **Créer la base de données** : `academie_herime` (ou autre nom)
2. **Créer un utilisateur SQL** avec tous les privilèges
3. **Noter les identifiants** pour le `.env`

## ✅ Vérification

1. **Tester l'accès** : https://academie.herime.com
2. **Vérifier les logs** : `storage/logs/laravel.log`
3. **Tester le paiement MaxiCash** (avec sandbox d'abord)

## 🔄 Mise à jour du site

Pour mettre à jour le site plus tard :

1. **Télécharger le nouveau ZIP** depuis GitHub
2. **Faire une sauvegarde** de votre `.env` et `storage/`
3. **Remplacer les fichiers** sauf `.env` et `storage/`
4. **Se reconnecter en SSH** pour exécuter :

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

## 🆘 Dépannage

### Erreur 500
```bash
# Vérifier les logs
tail -f storage/logs/laravel.log

# Vérifier les permissions
chmod -R 755 storage bootstrap/cache
```

### Erreur de base de données
- Vérifier les identifiants dans `.env`
- Vérifier que la base existe dans phpMyAdmin
- Tester la connexion avec les mêmes identifiants

### Assets non chargés
- Vérifier que le lien symbolique existe : `ls -la public/storage`
- Si absent : `php artisan storage:link`

## 📞 Support

- **O2Switch Support** : https://www.o2switch.fr/support/
- **Documentation** : https://www.o2switch.fr/services/hosting/documentation/

