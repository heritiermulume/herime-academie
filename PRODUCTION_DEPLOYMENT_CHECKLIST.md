# Checklist de Déploiement en Production

## 🔍 Diagnostic de l'erreur 500

### 1. Vérifier les logs d'erreur
```bash
# Sur le serveur de production
tail -f storage/logs/laravel.log
```

### 2. Vérifier les migrations
```bash
php artisan migrate:status
php artisan migrate --force
```

### 3. Vérifier les assets Vite
```bash
# Construire les assets pour la production
npm run build

# Vérifier que le dossier public/build existe
ls -la public/build/
```

### 4. Vérifier les permissions
```bash
# Permissions pour Laravel
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 5. Optimiser Laravel
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### 6. Vérifier le fichier .env
```bash
# Vérifier que APP_ENV=production et APP_DEBUG=false
grep APP_ENV .env
grep APP_DEBUG .env
```

## 🔧 Corrections à appliquer en production

### Étape 1 : Pull les dernières modifications
```bash
git pull origin main
```

### Étape 2 : Installer les dépendances
```bash
composer install --no-dev --optimize-autoloader
npm install
npm run build
```

### Étape 3 : Exécuter les migrations
```bash
php artisan migrate --force
```

### Étape 4 : Vider les caches
```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

### Étape 5 : Recréer les caches optimisés
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### Étape 6 : Vérifier les permissions
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

## 🐛 Erreurs courantes et solutions

### Erreur : "Column not found"
**Solution :** Les migrations n'ont pas été exécutées
```bash
php artisan migrate --force
```

### Erreur : "Vite manifest not found"
**Solution :** Les assets n'ont pas été compilés
```bash
npm run build
```

### Erreur : "Class not found"
**Solution :** Autoloader non optimisé
```bash
composer dump-autoload -o
```

### Erreur : "Permission denied"
**Solution :** Permissions incorrectes
```bash
chmod -R 775 storage bootstrap/cache
```

## 📝 Notes importantes

1. **Toujours vider les caches avant de les recréer**
2. **Vérifier les logs après chaque déploiement**
3. **Tester les routes principales après déploiement**
4. **S'assurer que APP_DEBUG=false en production**

