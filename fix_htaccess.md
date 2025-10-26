# Corriger le problème d'affichage des fichiers

## Le problème
Quand vous accédez au site, vous voyez la liste des fichiers au lieu du site.

## Cause
Le répertoire racine n'est pas configuré pour pointer vers `public/`

## Solution 1 : Via l'interface O2Switch (RECOMMANDÉ)

1. Connectez-vous à votre interface O2Switch
2. Allez dans **"Gestion du domaine"** ou **"Réglages du site"**
3. Cherchez **"Répertoire racine"** ou **"Document Root"**
4. Changez-le pour pointer vers le dossier `public` :
   ```
   /home/votre-compte/herime-academie/public
   ```
5. **Enregistrez** les modifications
6. Attendez quelques secondes
7. Rafraîchissez votre navigateur

## Solution 2 : Via .htaccess à la racine

Si vous ne pouvez pas changer le Document Root via l'interface, créez ce fichier `.htaccess` à la racine :

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

Commande :
```bash
cd ~/herime-academie
cat > .htaccess << 'EOF'
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
EOF
```

## Solution 3 : Vérifier le fichier public/index.php

Le fichier `public/index.php` doit exister et être accessible.

Vérification :
```bash
ls -la public/index.php
cat public/index.php | head -20
```

## Vérifier que Apache/PHP fonctionne

Testez si PHP est bien activé :
```bash
# Créer un fichier test.php dans public/
echo "<?php phpinfo(); ?>" > public/test.php

# Accédez à : https://academie.herime.com/test.php
# Vous devriez voir les informations PHP

# SUPPRIMEZ test.php après !
rm public/test.php
```

## Structure de dossiers correcte

```
~/herime-academie/
├── .env              ← Fichier de config (à éditer)
├── .htaccess         ← Redirection vers public/
├── app/
├── bootstrap/
├── config/
├── database/
├── public/          ← RACINE WEB (doit pointer ici)
│   ├── index.php    ← Point d'entrée
│   ├── .htaccess    ← Rewrite rules
│   ├── css/
│   ├── js/
│   └── images/
├── resources/
├── routes/
└── vendor/
```

## Commande complète pour tout vérifier

```bash
cd ~/herime-academie

# Vérifier la structure
ls -la
ls -la public/

# Vérifier le .htaccess
cat public/.htaccess

# Vérifier que index.php existe
cat public/index.php | head -10

# Vérifier les permissions
chmod -R 755 public/
chmod -R 755 storage/
chmod 644 .env
```

## Test après configuration

1. Allez sur : https://academie.herime.com
2. Vous devriez voir le site Laravel
3. Pas une liste de fichiers !

Si ça ne marche toujours pas :
```bash
# Regarder les logs
tail -f storage/logs/laravel.log

# Ou vérifier les logs Apache
tail -f /var/log/apache2/error.log
```

