# Configuration du sous-domaine academie.herime.com

## Vérification DNS

### 1. Vérifier que le sous-domaine existe
Allez sur votre gestionnaire de domaine (là où vous avez configuré herime.com) et vérifiez :
```
Type    Name       Value                  TTL
A       academie   IP_DU_SERVEUR_O2SWITCH  3600
```

### 2. Tester le DNS
```bash
ping academie.herime.com
# Doit retourner l'IP de votre serveur O2Switch
```

## Configuration O2Switch

### 1. Ajouter le sous-domaine dans O2Switch

1. **Connectez-vous à votre interface O2Switch**
2. **Allez dans "Gestion de domaines"** ou **"Mes sites"**
3. **Cliquez sur "Ajouter un domaine"** ou **"Ajouter un sous-domaine"**
4. **Entrez** : `academie.herime.com`
5. **Sélectionnez** : Pointer vers un répertoire existant
6. **Chemin** : `/home/muhe3594/herime-academie/public`
7. **Enregistrez**

### 2. OU configurer le domaine existant

Si `academie.herime.com` existe déjà :

1. **Gestion du domaine** > **academie.herime.com**
2. **Répertoire racine** : Modifier
3. **Chemin** : `/home/muhe3594/herime-academie/public`
4. **Enregistrer**

## Configuration du fichier .env

Sur votre serveur O2Switch :

```bash
cd ~/herime-academie
nano .env
```

Vérifiez/Modifiez cette ligne :
```env
APP_URL=https://academie.herime.com
```

Sauvegardez (Ctrl+O, Entrée, Ctrl+X)

Puis :
```bash
php artisan config:cache
```

## Vérification

### 1. Accéder au site
Allez sur : **https://academie.herime.com**

### 2. Si vous voyez toujours les fichiers

Exécutez ces commandes sur le serveur :

```bash
cd ~/herime-academie

# Vérifier où vous êtes
pwd

# Vérifier que public/index.php existe
ls -la public/index.php

# Vérifier le contenu de public/index.php
head -30 public/index.php

# Si le fichier n'existe pas, il y a un problème
```

### 3. Dernière vérification

```bash
# Créer un fichier test
echo "<?php echo 'PHP fonctionne !'; ?>" > public/test.php

# Accédez à : https://academie.herime.com/test.php
# Vous devriez voir "PHP fonctionne !"
# PUIS SUPPRIMEZ test.php !
rm public/test.php
```

## Résolution de problèmes courants

### Problème : 404 Not Found
**Solution** : Le répertoire racine est mal configuré
```bash
# Dans O2Switch, vérifier que le Document Root est :
/home/muhe3594/herime-academie/public
```

### Problème : Liste des fichiers visible
**Solution** : Le Document Root ne pointe pas vers `public/`
```bash
# Mettre à jour le .htaccess à la racine
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

### Problème : Erreur 500
```bash
# Voir les logs
tail -50 storage/logs/laravel.log

# Vérifier les permissions
chmod -R 755 storage bootstrap/cache
chmod 644 .env

# Nettoyer le cache
php artisan config:clear
php artisan cache:clear
```

## Checklist finale

- [ ] Le sous-domaine `academie.herime.com` existe dans votre DNS
- [ ] Le DNS pointe vers l'IP de votre serveur O2Switch
- [ ] Le domaine est ajouté dans l'interface O2Switch
- [ ] Le répertoire racine est `/home/muhe3594/herime-academie/public`
- [ ] Le fichier `public/index.php` existe
- [ ] Le `.env` a `APP_URL=https://academie.herime.com`
- [ ] Les permissions sont correctes (755/644)
- [ ] Le site est accessible sur https://academie.herime.com

## Commandes complètes à exécuter

```bash
# Sur votre serveur O2Switch
cd ~/herime-academie

# Vérifier la structure
ls -la public/

# Vérifier le .env
grep APP_URL .env

# Mettre à jour le .env si nécessaire
sed -i 's|APP_URL=.*|APP_URL=https://academie.herime.com|g' .env

# Mettre en cache
php artisan config:cache

# Vérifier les permissions
chmod -R 755 public storage bootstrap/cache
chmod 644 .env

# Tester
curl -I https://academie.herime.com
```

