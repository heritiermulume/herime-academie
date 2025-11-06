# Guide de Correction Erreur 500 en Production

## 🚨 Diagnostic Rapide

### Option 1: Script automatique (recommandé)

Sur votre serveur de production, exécutez:

```bash
cd /chemin/vers/herime-academie
chmod +x fix-500-error.sh
./fix-500-error.sh
```

Ce script va automatiquement:
- ✅ Vider tous les caches Laravel
- ✅ Corriger les permissions
- ✅ Vérifier et créer les dossiers nécessaires
- ✅ Vérifier le fichier .env
- ✅ Générer APP_KEY si nécessaire
- ✅ Recréer les caches optimisés

### Option 2: Test via navigateur

1. Copiez `quick-test-500.php` dans le dossier `public/`
2. Accédez à: `http://votre-domaine.com/quick-test-500.php`
3. Le script affichera toutes les erreurs détectées

## 🔧 Corrections Manuelles

### 1. Vider tous les caches

```bash
php artisan optimize:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

### 2. Corriger les permissions

```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

### 3. Vérifier que les dossiers existent

```bash
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
mkdir -p bootstrap/cache
```

### 4. Vérifier le fichier .env

Assurez-vous que:
- Le fichier `.env` existe
- `APP_KEY` est configuré (si non, exécutez: `php artisan key:generate`)
- Les variables de base de données sont correctes
- `APP_DEBUG=false` en production
- `APP_ENV=production`

### 5. Vérifier les logs

```bash
# Voir les dernières erreurs
tail -100 storage/logs/laravel.log | grep -E "ERROR|CRITICAL|Exception"

# Suivre les erreurs en temps réel
tail -f storage/logs/laravel.log
```

## 🔍 Causes Courantes

### 1. Caches corrompus après git pull

**Solution:** Vider tous les caches (voir ci-dessus)

### 2. Permissions incorrectes

**Symptômes:** Erreurs de type "permission denied" dans les logs

**Solution:**
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache  # Selon votre serveur
```

### 3. Fichier .env manquant ou mal configuré

**Symptômes:** Erreurs de connexion DB ou APP_KEY

**Solution:**
```bash
# Si .env n'existe pas
cp .env.example .env
php artisan key:generate

# Vérifier les variables
cat .env | grep -E "APP_KEY|DB_"
```

### 4. Fichiers manquants après déploiement

**Symptômes:** Erreurs "Class not found" ou "File not found"

**Solution:**
```bash
# Réinstaller les dépendances
composer install --no-dev --optimize-autoloader

# Recompiler les assets
npm install
npm run build
```

### 5. Base de données non accessible

**Symptômes:** Erreurs SQL dans les logs

**Solution:**
- Vérifier les credentials dans `.env`
- Tester la connexion: `php artisan db:show`
- Vérifier que la base de données existe
- Vérifier que les migrations sont à jour: `php artisan migrate:status`

### 6. Problèmes avec les caches optimisés

**Symptômes:** Erreurs après avoir fait `config:cache` ou `route:cache`

**Solution:**
```bash
# Supprimer les caches
rm -f bootstrap/cache/config.php
rm -f bootstrap/cache/routes.php
rm -f bootstrap/cache/services.php

# Les recréer
php artisan config:cache
php artisan route:cache
```

### 7. Problèmes de mémoire PHP

**Symptômes:** Erreurs "Memory limit exceeded"

**Solution:**
- Augmenter `memory_limit` dans `php.ini`
- Ou créer un fichier `.htaccess` dans `public/` avec:
```apache
php_value memory_limit 512M
```

### 8. Problèmes avec le serveur web

**Pour Apache:**
- Vérifier que `mod_rewrite` est activé
- Vérifier que le `.htaccess` est lu

**Pour Nginx:**
- Vérifier la configuration du serveur
- Vérifier que `try_files` est bien configuré

## 📋 Checklist de Vérification

- [ ] Caches vidés (`optimize:clear`)
- [ ] Permissions correctes (775 pour storage et bootstrap/cache)
- [ ] Fichier `.env` existe et est configuré
- [ ] `APP_KEY` est généré
- [ ] Base de données accessible
- [ ] Toutes les migrations exécutées
- [ ] Dépendances installées (`composer install`)
- [ ] Assets compilés (`npm run build`)
- [ ] Lien symbolique storage créé (`storage:link`)
- [ ] Logs consultables (vérifier les dernières erreurs)
- [ ] `APP_DEBUG=false` en production
- [ ] `APP_ENV=production`

## 🆘 Si l'erreur persiste

1. **Activer temporairement le mode debug** (⚠️ seulement pour diagnostic):
   ```bash
   # Dans .env
   APP_DEBUG=true
   ```
   Cela affichera l'erreur exacte dans le navigateur.

2. **Consulter les logs du serveur web:**
   - Apache: `/var/log/apache2/error.log` ou `/var/log/httpd/error_log`
   - Nginx: `/var/log/nginx/error.log`

3. **Tester avec le script de diagnostic:**
   ```bash
   php quick-test-500.php
   ```

4. **Vérifier les logs Laravel:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

## 🔐 Sécurité

⚠️ **IMPORTANT:** Après avoir résolu l'erreur:
- Remettez `APP_DEBUG=false` en production
- Supprimez `quick-test-500.php` du dossier `public/`
- Vérifiez que les fichiers sensibles ne sont pas accessibles publiquement

## 📞 Support

Si le problème persiste après avoir suivi ce guide:
1. Consultez les logs: `storage/logs/laravel.log`
2. Notez l'erreur exacte
3. Vérifiez la configuration du serveur

