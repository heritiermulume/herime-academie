# Commandes de Nettoyage et Optimisation - Production

## Commandes de Nettoyage

### 1. Nettoyer tous les caches Laravel

```bash
# Nettoyer le cache de configuration
php artisan config:clear

# Nettoyer le cache des routes
php artisan route:clear

# Nettoyer le cache des vues
php artisan view:clear

# Nettoyer le cache de l'application
php artisan cache:clear

# Nettoyer le cache des événements
php artisan event:clear

# Nettoyer tous les caches en une seule commande
php artisan optimize:clear
```

### 2. Nettoyer les fichiers temporaires

```bash
# Nettoyer les fichiers de log (attention : supprime tous les logs)
# Il est recommandé de les archiver avant
find storage/logs -name "*.log" -type f -mtime +30 -delete

# Nettoyer les fichiers de session expirés (automatique, mais peut être forcé)
php artisan session:gc

# Nettoyer les fichiers de cache expirés
php artisan cache:prune-stale-tags
```

### 3. Nettoyer les fichiers compilés

```bash
# Supprimer les fichiers de cache compilés
php artisan clear-compiled

# Nettoyer le cache de bootstrap
rm -rf bootstrap/cache/*.php
```

## Commandes d'Optimisation

### 1. Optimiser les caches Laravel

```bash
# Optimiser la configuration (cache config)
php artisan config:cache

# Optimiser les routes (cache routes)
php artisan route:cache

# Optimiser les vues (cache views)
php artisan view:cache

# Optimiser les événements (cache events)
php artisan event:cache

# Optimiser tout en une seule commande
php artisan optimize
```

### 2. Optimiser Composer

```bash
# Optimiser l'autoloader Composer
composer dump-autoload --optimize --classmap-authoritative

# Ou en mode production (sans dev dependencies)
composer install --no-dev --optimize-autoloader --classmap-authoritative
```

### 3. Optimiser les assets frontend

```bash
# Compiler les assets pour la production
npm run build

# Ou si vous utilisez yarn
yarn build
```

## Script Complet de Nettoyage et Optimisation

Créez un script `optimize-production.sh` :

```bash
#!/bin/bash

echo "🧹 Nettoyage et optimisation de l'application Laravel..."

# 1. Nettoyer tous les caches
echo "1️⃣  Nettoyage des caches..."
php artisan optimize:clear

# 2. Nettoyer les fichiers compilés
echo "2️⃣  Nettoyage des fichiers compilés..."
php artisan clear-compiled
rm -rf bootstrap/cache/*.php

# 3. Optimiser Composer
echo "3️⃣  Optimisation de Composer..."
composer dump-autoload --optimize --classmap-authoritative --no-dev

# 4. Optimiser les caches Laravel
echo "4️⃣  Optimisation des caches Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 5. Compiler les assets frontend (si nécessaire)
echo "5️⃣  Compilation des assets frontend..."
if [ -f "package.json" ]; then
    npm run build
fi

# 6. Nettoyer les sessions expirées
echo "6️⃣  Nettoyage des sessions expirées..."
php artisan session:gc

# 7. Afficher les permissions
echo "7️⃣  Vérification des permissions..."
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

echo "✅ Optimisation terminée !"
```

## Commandes de Maintenance Régulière

### Tâches quotidiennes (à mettre dans un cron)

```bash
# Nettoyer les sessions expirées (à exécuter quotidiennement)
php artisan session:gc

# Nettoyer les logs anciens (à exécuter hebdomadairement)
find storage/logs -name "*.log" -type f -mtime +7 -exec truncate -s 0 {} \;
```

### Exemple de crontab

```bash
# Nettoyer les sessions expirées tous les jours à 2h du matin
0 2 * * * cd /home/muhe3594/herime-academie && php artisan session:gc >> /dev/null 2>&1

# Nettoyer les logs anciens tous les dimanches à 3h du matin
0 3 * * 0 cd /home/muhe3594/herime-academie && find storage/logs -name "*.log" -type f -mtime +30 -delete
```

## Commandes de Vérification

### Vérifier l'état de l'application

```bash
# Vérifier les routes
php artisan route:list

# Vérifier la configuration
php artisan config:show

# Vérifier les permissions
ls -la storage bootstrap/cache

# Vérifier l'espace disque
df -h

# Vérifier la mémoire
free -h
```

## Commandes de Diagnostic

### En cas de problème

```bash
# Vérifier les logs d'erreur
tail -f storage/logs/laravel.log

# Vérifier les permissions
ls -la storage bootstrap/cache

# Vérifier la configuration PHP
php -v
php -m

# Vérifier les variables d'environnement
php artisan tinker
>>> config('app.env')
>>> config('services.sso.enabled')
```

## Notes Importantes

1. **Ne jamais exécuter `php artisan optimize` en développement** - Utilisez `php artisan optimize:clear` à la place
2. **Sauvegarder avant optimisation** - Toujours faire une sauvegarde avant d'exécuter des commandes d'optimisation
3. **Permissions** - Assurez-vous que `storage` et `bootstrap/cache` sont accessibles en écriture
4. **Mode maintenance** - Mettez l'application en mode maintenance avant les optimisations importantes :
   ```bash
   php artisan down
   # ... exécuter les optimisations ...
   php artisan up
   ```

## Ordre Recommandé pour un Déploiement

```bash
# 1. Mettre en maintenance
php artisan down

# 2. Récupérer le code
git pull origin main

# 3. Installer les dépendances
composer install --no-dev --optimize-autoloader

# 4. Nettoyer les caches
php artisan optimize:clear

# 5. Exécuter les migrations
php artisan migrate --force

# 6. Optimiser les caches
php artisan optimize

# 7. Compiler les assets
npm run build

# 8. Réactiver l'application
php artisan up
```

