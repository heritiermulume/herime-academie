# Guide d'hébergement sur O2Switch - Herime Académie

## 1. Prérequis

Avant de déployer sur O2Switch, vous aurez besoin de :
- Un compte O2Switch (https://www.o2switch.fr)
- L'accès FTP/SFTP ou SSH à votre serveur
- L'accès à phpMyAdmin pour la base de données
- Votre domaine pointant vers O2Switch

## 2. Configuration du serveur O2Switch

### Configuration PHP requise
- PHP 8.1 ou supérieur
- Composer installé
- Node.js et NPM pour les assets frontend

## 3. Upload des fichiers

### Méthode 1 : Via FTP/SFTP
```bash
# Utilisez FileZilla, WinSCP ou Cyberduck
# Uploader tous les fichiers dans le dossier public_html/
```

### Méthode 2 : Via Git (recommandé)
```bash
# SSH dans votre serveur O2Switch
ssh votre-compte@votre-serveur.o2switch.net

# Cloner le repository
cd /home/votre-compte/www
git clone https://github.com/heritiermulume/herime-academie.git

# Configurer les permissions
chmod -R 755 herime-academie
chmod -R 775 herime-academie/storage
chmod -R 775 herime-academie/bootstrap/cache
```

## 4. Configuration de la structure des fichiers

O2Switch utilise généralement un dossier `www` comme racine. Pour Laravel, vous devez :

### Option 1 : Point racine dans public/
```bash
# Dans votre interface O2Switch
# Configurer le répertoire racine de votre domaine vers :
/home/votre-compte/herime-academie/public
```

### Option 2 : Via fichier .htaccess
Créez un fichier `.htaccess` à la racine :
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

## 5. Installation des dépendances

```bash
# SSH dans votre serveur
ssh votre-compte@votre-serveur.o2switch.net
cd herime-academie

# Installer les dépendances PHP
composer install --no-dev --optimize-autoloader

# Installer les dépendances Node.js
npm install
npm run build
```

## 6. Configuration de la base de données

### Via phpMyAdmin
1. Connectez-vous à phpMyAdmin
2. Créez une nouvelle base de données : `herime_academie`
3. Créez un utilisateur SQL avec tous les privilèges
4. Notez les informations de connexion

### Configuration dans .env
```bash
# Copier le fichier .env.example
cp .env.example .env

# Modifier le fichier .env avec vos informations O2Switch
nano .env
```

Contenu du `.env` pour O2Switch :
```env
APP_NAME="Herime Académie"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://votre-domaine.com

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

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
SESSION_LIFETIME=120

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.o2switch.net
MAIL_PORT=587
MAIL_USERNAME=votre_email@votre-domaine.com
MAIL_PASSWORD=votre_password_email
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="contact@herime.com"
MAIL_FROM_NAME="${APP_NAME}"
```

## 7. Génération de la clé d'application

```bash
php artisan key:generate
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 8. Configuration de la base de données

```bash
# Créer les tables
php artisan migrate --force

# (Optionnel) Charger les données de base
php artisan db:seed
```

## 9. Création des liens symboliques

```bash
# Créer le lien pour le storage
php artisan storage:link
```

## 10. Configuration des permissions

```bash
# Donner les bonnes permissions
chmod -R 755 storage bootstrap/cache
chown -R votre-utilisateur:www-data storage bootstrap/cache
```

## 11. Configuration .htaccess pour public/

Créez le fichier `public/.htaccess` si nécessaire :
```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

## 12. Configuration des variables d'environnement

Variables spécifiques à configurer dans O2Switch :

### Base de données O2Switch
- **Hôte** : `localhost` ou l'adresse fournie par O2Switch
- **Port** : `3306` (par défaut)
- **Nom de la base** : `herime_academie` (ou celui de votre choix)
- **Utilisateur** : Créé dans phpMyAdmin
- **Mot de passe** : Celui défini lors de la création de l'utilisateur

### Email O2Switch
Utilisez le serveur SMTP d'O2Switch :
```
MAIL_HOST=smtp.o2switch.net
MAIL_PORT=587
MAIL_USERNAME=votre-email@votre-domaine
MAIL_PASSWORD=votre-password-email
```


## 14. Checklist de déploiement

- [ ] Fichiers uploadés sur le serveur
- [ ] Base de données créée dans phpMyAdmin
- [ ] Fichier `.env` configuré avec les bonnes informations
- [ ] Clé d'application générée (`APP_KEY`)
- [ ] Permissions de dossier configurées (755 pour dossiers, 644 pour fichiers)
- [ ] Permissions de storage et cache (775)
- [ ] Migration de base de données exécutée
- [ ] Lien symbolique de storage créé
- [ ] Tests de connexion à la base de données
- [ ] Tests de paiement
- [ ] Tests d'envoi d'email

## 15. Optimisation de performance

```bash
# Optimiser l'autoloader
composer install --optimize-autoloader --no-dev

# Cache des configurations
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimisation générale
php artisan optimize
```

## 16. Commandes utiles O2Switch

```bash
# Voir les logs
tail -f storage/logs/laravel.log

# Nettoyer le cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Réoptimiser
php artisan optimize
```

## 17. Sécurité

- ✅ Fichier `.env` jamais accessible publiquement
- ✅ APP_DEBUG=false en production
- ✅ Mots de passe forts pour la base de données
- ✅ Mise à jour régulière de Composer et NPM
- ✅ Sauvegardes régulières de la base de données

## 18. Support O2Switch

- Support : https://www.o2switch.fr/support/
- Ticket système : Interface client O2Switch
- Documentation : https://www.o2switch.fr/services/hosting/documentation/

---

## Nouvelle URL de production

Une fois déployé, votre site sera accessible sur :
**https://votre-domaine.com**


