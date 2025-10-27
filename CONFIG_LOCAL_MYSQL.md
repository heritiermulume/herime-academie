# Configuration MySQL pour le développement local

## Étape 1 : Vérifier que MySQL est démarré

```bash
brew services start mysql
# ou
sudo /usr/local/mysql/support-files/mysql.server start
```

## Étape 2 : Vérifier la connexion

```bash
mysql -u root -h 127.0.0.1 -e "SHOW DATABASES;"
```

## Étape 3 : Créer la base de données

```bash
mysql -u root -h 127.0.0.1 -e "CREATE DATABASE IF NOT EXISTS herime_academie CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

## Étape 4 : Configurer le fichier .env

Dans votre fichier `.env`, utilisez ces valeurs :

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=herime_academie
DB_USERNAME=root
DB_PASSWORD=
```

**Note :** Si vous avez un mot de passe root, ajoutez-le dans `DB_PASSWORD`.

## Étape 5 : Exécuter les migrations et seeders

```bash
cd /Users/heritiermulume/Autres/Herime/Projets/Web/herime-academie
php artisan migrate:fresh --force
php artisan db:seed --force
php artisan config:clear
php artisan cache:clear
php artisan serve
```

## Résoudre les problèmes

### Si MySQL ne démarre pas :

```bash
# Arrêter MySQL
brew services stop mysql

# Redémarrer
brew services start mysql

# Vérifier les logs
tail -f /usr/local/var/mysql/*.err
```

### Si vous ne pouvez pas vous connecter :

```bash
# Réinitialiser le mot de passe root
mysql_safe_mode --skip-grant-tables
```

### Si le socket n'existe pas :

```bash
# Trouver le chemin du socket
mysql_config --socket

# Ou créer un lien symbolique
sudo ln -s /var/run/mysqld/mysqld.sock /tmp/mysql.sock
```

### Alternative : Utiliser SQLite pour le développement local

Si MySQL pose problème, utilisez SQLite :

Dans `.env` :
```env
DB_CONNECTION=sqlite
# Commenter les lignes MySQL
```

