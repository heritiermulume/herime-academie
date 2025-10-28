# 🚀 Guide de déploiement des bannières sur o2switch

## 🎯 Problème constaté

Après `git pull` sur o2switch, certaines mises à jour ne s'affichent pas correctement. Voici les raisons possibles et solutions.

## 🔍 Diagnostic des problèmes courants

### 1. **Le cache Laravel n'a pas été nettoyé**

**Symptôme**: Les anciennes versions des vues/configs sont toujours utilisées

**Solution**:
```bash
cd ~/www/herime-academie  # Ajustez le chemin selon votre config

# Nettoyer TOUT le cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# OU en une commande
php artisan optimize:clear
```

### 2. **Les migrations n'ont pas été exécutées**

**Symptôme**: Erreurs SQL ou colonnes manquantes

**Solution**:
```bash
# Vérifier l'état des migrations
php artisan migrate:status

# Exécuter les migrations manquantes
php artisan migrate --force
```

### 3. **Composer n'a pas été mis à jour**

**Symptôme**: Classes non trouvées ou méthodes manquantes

**Solution**:
```bash
# Mettre à jour les dépendances
composer install --no-dev --optimize-autoloader

# Si problème de version PHP
composer install --no-dev --optimize-autoloader --ignore-platform-reqs
```

### 4. **Les fichiers .env ne sont pas synchronisés**

**Symptôme**: Variables d'environnement manquantes

**Solution**:
```bash
# Vérifier les différences
cat .env.example
cat .env

# Copier les nouvelles variables si nécessaires
nano .env
```

### 5. **Permissions de fichiers incorrectes**

**Symptôme**: Erreurs de lecture/écriture

**Solution**:
```bash
# Sur o2switch, généralement pas besoin de www-data
chmod -R 755 storage bootstrap/cache
chmod -R 777 storage/logs

# Si vous utilisez des sessions en fichiers
chmod -R 777 storage/framework/sessions
```

### 6. **OPcache PHP activé (o2switch)**

**Symptôme**: Les changements de code ne sont pas pris en compte

**Solution**:
```bash
# Via cPanel > "Sélecteur PHP" > Réinitialiser OPcache
# OU créer un script reset-opcache.php à la racine:
```

**Fichier**: `public/reset-opcache.php`
```php
<?php
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✅ OPcache réinitialisé avec succès";
} else {
    echo "❌ OPcache n'est pas activé";
}
?>
```

Visitez: `https://votre-domaine.com/reset-opcache.php`

**⚠️ Important**: Supprimez ce fichier après utilisation pour des raisons de sécurité!

## 📋 Checklist complète de déploiement sur o2switch

Suivez cette checklist étape par étape:

### ✅ Préparation locale

```bash
# Sur votre machine locale
git add .
git commit -m "Stockage des images de bannières en base de données"
git push origin main
```

### ✅ Connexion au serveur

```bash
# Option 1: SSH (si activé sur o2switch)
ssh votre-user@votre-domaine.com

# Option 2: Terminal via cPanel
# Aller dans cPanel > Terminal
```

### ✅ Navigation et pull

```bash
# Aller dans le dossier du projet
cd ~/www  # ou ~/public_html selon votre config
cd herime-academie

# Vérifier la branche actuelle
git branch

# Pull les modifications
git pull origin main
```

Si erreur de permissions Git:
```bash
git config --global --add safe.directory ~/www/herime-academie
git pull origin main
```

### ✅ Installation des dépendances

```bash
# Composer
composer install --no-dev --optimize-autoloader

# NPM (si nécessaire)
npm install
npm run build
```

### ✅ Base de données et migrations

```bash
# Backup de la base (IMPORTANT!)
mysqldump -u db_user -p db_name > backup_$(date +%Y%m%d_%H%M%S).sql

# Exécuter les migrations
php artisan migrate --force

# Vérifier le statut
php artisan migrate:status
```

### ✅ Conversion des bannières existantes (si nécessaire)

```bash
# Option 1: Convertir les bannières existantes (RECOMMANDÉ)
php artisan banners:convert-to-base64

# Option 2: Supprimer et re-créer
php artisan tinker
>>> \App\Models\Banner::truncate();
>>> exit

php artisan db:seed --class=BannerSeeder --force
```

### ✅ Nettoyage du cache

```bash
# Nettoyer tout le cache
php artisan optimize:clear

# Re-cacher pour la production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### ✅ Vérification

```bash
# Vérifier les bannières
php artisan tinker
>>> \App\Models\Banner::count()
>>> $b = \App\Models\Banner::first();
>>> echo strlen($b->image); // Devrait être > 1000
>>> echo substr($b->image, 0, 30); // "data:image/jpeg;base64..."
>>> exit
```

### ✅ Réinitialiser OPcache

```bash
# Créer le fichier reset-opcache.php
cat > public/reset-opcache.php << 'EOF'
<?php
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✅ OPcache réinitialisé";
} else {
    echo "❌ OPcache non disponible";
}
?>
EOF

# Visiter https://votre-domaine.com/reset-opcache.php dans le navigateur
# Puis supprimer le fichier
rm public/reset-opcache.php
```

### ✅ Test final

1. Ouvrir `https://votre-domaine.com` dans un navigateur
2. Vérifier que les bannières s'affichent
3. Tester le carousel (défilement automatique)
4. Tester sur mobile (responsive)
5. Tester l'admin: `/admin/banners`
6. Uploader une nouvelle bannière pour tester

## 🐛 Problèmes spécifiques et solutions

### Problème: "Class 'App\Models\Banner' not found"

**Cause**: Autoload pas à jour

**Solution**:
```bash
composer dump-autoload
php artisan clear-compiled
php artisan optimize:clear
```

### Problème: "SQLSTATE[42S22]: Column not found: 'image'"

**Cause**: Migration pas exécutée

**Solution**:
```bash
php artisan migrate:status
php artisan migrate --force
```

### Problème: Les images ne s'affichent pas (broken image)

**Cause 1**: Les images sont encore des chemins de fichiers

**Solution**:
```bash
php artisan tinker
>>> $banner = \App\Models\Banner::first();
>>> echo substr($banner->image, 0, 50);
>>> // Si ça affiche "images/hero/..." au lieu de "data:image/...", convertissez
>>> exit

php artisan banners:convert-to-base64
```

**Cause 2**: Les vues utilisent encore `asset()`

**Solution**:
```bash
# Vérifier que les vues ont été mises à jour
grep -r "asset(\$banner->image)" resources/views/
# Si des résultats apparaissent, les fichiers n'ont pas été mis à jour

# Re-pull et vérifier
git pull origin main --force
php artisan view:clear
```

### Problème: Erreur "Allowed memory size exhausted"

**Cause**: Images trop grandes pour la mémoire PHP

**Solution 1**: Via php.ini (cPanel > Sélecteur PHP > Options)
```ini
memory_limit = 256M
```

**Solution 2**: Via .htaccess dans public/
```apache
php_value memory_limit 256M
php_value upload_max_filesize 10M
php_value post_max_size 12M
```

### Problème: "Data too long for column 'image'"

**Cause**: Migration pas appliquée, colonne encore en VARCHAR

**Solution**:
```bash
# Vérifier la structure de la table
php artisan tinker
>>> DB::select("SHOW COLUMNS FROM banners LIKE 'image'");
>>> // Type doit être "longtext"
>>> exit

# Si pas longtext, forcer la migration
php artisan migrate:refresh --path=database/migrations/2025_10_28_095739_update_banners_table_store_images_in_database.php --force
```

### Problème: Le site affiche une erreur 500

**Cause**: Multiples possibilités

**Solution**:
```bash
# 1. Activer le mode debug temporairement
nano .env
# APP_DEBUG=true

# 2. Vérifier les logs
tail -f storage/logs/laravel.log

# 3. Vérifier les permissions
chmod -R 755 storage bootstrap/cache

# 4. Re-désactiver le debug après diagnostic
# APP_DEBUG=false
php artisan config:clear
```

## 📱 Vérification mobile

Pour vérifier que tout fonctionne sur mobile:

1. Ouvrir le site sur mobile ou utiliser le mode responsive du navigateur
2. Vérifier l'aspect ratio 16:9 de la bannière
3. Vérifier que le texte est lisible
4. Vérifier que les boutons sont cliquables
5. Tester le swipe pour changer de bannière

## 🔒 Sécurité

### Après déploiement:

```bash
# 1. Désactiver le mode debug
# Dans .env
APP_DEBUG=false
APP_ENV=production

# 2. Re-cacher la config
php artisan config:cache

# 3. Supprimer les scripts temporaires
rm -f public/reset-opcache.php
rm -f convert-banners-to-base64.php
```

## 📞 Support o2switch

Si problème persistant:

1. **Documentation o2switch**: https://www.o2switch.fr/kb/
2. **Support o2switch**: Via cPanel > "Ouvrir un ticket"
3. **Forum o2switch**: https://forum.o2switch.fr/

### Informations à fournir au support:

- Version PHP utilisée (cPanel > Sélecteur PHP)
- Logs d'erreur (`storage/logs/laravel.log`)
- Message d'erreur exact
- Étapes pour reproduire le problème

## ✅ Validation finale

Une fois tout déployé, vérifiez:

- [ ] Le site s'affiche correctement
- [ ] Les bannières défilent automatiquement
- [ ] Les images sont visibles sur desktop
- [ ] Les images sont visibles sur mobile
- [ ] L'admin des bannières fonctionne
- [ ] On peut créer une nouvelle bannière
- [ ] On peut modifier une bannière existante
- [ ] On peut supprimer une bannière
- [ ] Le mode debug est désactivé
- [ ] Les caches sont optimisés

## 🎉 Félicitations!

Si tous les checks sont verts, votre déploiement est réussi! 🚀

