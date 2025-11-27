# 🚀 Commandes de Production - Nettoyage et Optimisation

## 📋 Commandes Essentielles de Production

### 1. Nettoyage des Caches

```bash
# Vider tous les caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
php artisan event:clear

# Ou en une seule commande (Laravel 11+)
php artisan optimize:clear
```

### 2. Optimisation de Laravel

```bash
# Créer les caches optimisés pour la production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Optimisation complète (Laravel 11+)
php artisan optimize
```

### 3. Optimisation de Composer

```bash
# Optimiser l'autoloader Composer
composer install --no-dev --optimize-autoloader

# Ou si déjà installé
composer dump-autoload -o
```

### 4. Compilation des Assets

```bash
# Installer les dépendances NPM
npm install

# Compiler les assets pour la production
npm run build

# Ou pour la production avec minification
npm run build -- --mode production
```

### 5. Base de Données

```bash
# Vérifier le statut des migrations
php artisan migrate:status

# Exécuter les migrations (avec --force en production)
php artisan migrate --force

# Vérifier l'intégrité de la base de données
php artisan db:show
```

### 6. Permissions des Fichiers

```bash
# Permissions pour Laravel
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Ou selon votre configuration serveur
chown -R $USER:www-data storage bootstrap/cache
```

### 7. Nettoyage des Logs (Optionnel)

```bash
# Vider les logs (attention : perte de données)
truncate -s 0 storage/logs/laravel.log

# Ou supprimer les anciens logs
find storage/logs -name "*.log" -mtime +30 -delete
```

## 🔄 Script Complet de Déploiement

### Script Automatique (déjà disponible : deploy-production.sh)

```bash
# Exécuter le script de déploiement
chmod +x deploy-production.sh
./deploy-production.sh
```

### Commandes Manuelles (étape par étape)

```bash
# 1. Pull les dernières modifications
git pull origin main

# 2. Installer les dépendances
composer install --no-dev --optimize-autoloader
npm install
npm run build

# 3. Exécuter les migrations
php artisan migrate --force

# 4. Nettoyer les caches
php artisan optimize:clear

# 5. Optimiser Laravel
php artisan optimize

# 6. Vérifier les permissions
chmod -R 775 storage bootstrap/cache
```

## ⚡ Commandes d'Optimisation Avancées

### Optimisation de la Performance

```bash
# Optimiser les routes (si beaucoup de routes)
php artisan route:cache

# Optimiser les vues (si beaucoup de vues)
php artisan view:cache

# Optimiser les événements
php artisan event:cache

# Optimisation complète (Laravel 11+)
php artisan optimize
```

### Nettoyage des Données

```bash
# Nettoyer les anciennes sessions
php artisan session:gc

# Nettoyer les anciens jobs en échec
php artisan queue:flush

# Nettoyer les anciens fichiers temporaires
php artisan cache:prune-stale-tags
```

### Vérification de l'Application

```bash
# Vérifier la configuration
php artisan config:show

# Lister toutes les routes
php artisan route:list

# Vérifier les services
php artisan about

# Vérifier l'environnement
php artisan env
```

## 🔍 Commandes de Diagnostic

### Logs et Erreurs

```bash
# Voir les logs en temps réel
tail -f storage/logs/laravel.log

# Voir les dernières erreurs
tail -n 100 storage/logs/laravel.log | grep ERROR

# Vérifier les logs d'erreur PHP
tail -f /var/log/php_errors.log
```

### Performance

```bash
# Vérifier les requêtes lentes (si configuré)
php artisan db:monitor

# Vérifier l'utilisation du cache
php artisan cache:table
```

### Sécurité

```bash
# Vérifier les permissions
ls -la storage bootstrap/cache

# Vérifier la configuration de sécurité
php artisan config:show | grep -i security
```

## 📦 Commandes de Maintenance

### Mode Maintenance

```bash
# Activer le mode maintenance
php artisan down

# Avec message personnalisé
php artisan down --message="Maintenance en cours" --retry=60

# Désactiver le mode maintenance
php artisan up
```

### Queue Workers

```bash
# Démarrer le worker de queue
php artisan queue:work --tries=3

# Redémarrer le worker (après déploiement)
php artisan queue:restart

# Voir les jobs en attente
php artisan queue:monitor
```

### Scheduler (Cron)

```bash
# Tester le scheduler
php artisan schedule:test

# Lister les tâches planifiées
php artisan schedule:list

# Exécuter le scheduler manuellement
php artisan schedule:run
```

## 🗄️ Commandes de Base de Données

### Sauvegarde

```bash
# Sauvegarde manuelle (si configuré)
php artisan db:backup

# Ou via mysqldump
mysqldump -u utilisateur -p nom_base > backup_$(date +%Y%m%d).sql
```

### Optimisation

```bash
# Optimiser les tables MySQL
php artisan db:optimize

# Analyser les tables
php artisan db:analyze
```

## 🧹 Nettoyage Complet (Avant Déploiement)

```bash
#!/bin/bash
# Script de nettoyage complet

echo "🧹 Nettoyage complet de l'application..."

# 1. Nettoyer les caches
php artisan optimize:clear

# 2. Nettoyer Composer
composer dump-autoload -o

# 3. Nettoyer NPM
npm run build

# 4. Nettoyer les anciens logs (optionnel)
find storage/logs -name "*.log" -mtime +7 -delete

# 5. Optimiser
php artisan optimize

echo "✅ Nettoyage terminé!"
```

## 📝 Checklist de Production

Avant chaque déploiement, vérifier :

- [ ] `APP_ENV=production` dans `.env`
- [ ] `APP_DEBUG=false` dans `.env`
- [ ] `APP_URL` correctement configuré
- [ ] Sauvegarde de la base de données effectuée
- [ ] Migrations testées en staging
- [ ] Assets compilés (`npm run build`)
- [ ] Caches optimisés (`php artisan optimize`)
- [ ] Permissions correctes (`chmod 775 storage bootstrap/cache`)
- [ ] Logs vérifiés (`tail -f storage/logs/laravel.log`)

## 🚨 Commandes d'Urgence

### En cas d'erreur 500

```bash
# 1. Voir les logs
tail -f storage/logs/laravel.log

# 2. Vider les caches
php artisan optimize:clear

# 3. Recréer les caches
php artisan optimize

# 4. Vérifier les permissions
chmod -R 775 storage bootstrap/cache
```

### En cas de problème de routes

```bash
# Vider le cache des routes
php artisan route:clear
php artisan route:cache

# Vérifier les routes
php artisan route:list
```

### En cas de problème de configuration

```bash
# Vider le cache de configuration
php artisan config:clear
php artisan config:cache

# Vérifier la configuration
php artisan config:show
```

## 📚 Commandes Utiles par Catégorie

### Cache
- `php artisan cache:clear` - Vider le cache
- `php artisan cache:table` - Créer la table de cache
- `php artisan config:cache` - Cache de configuration
- `php artisan route:cache` - Cache des routes
- `php artisan view:cache` - Cache des vues

### Base de Données
- `php artisan migrate` - Exécuter les migrations
- `php artisan migrate:status` - Statut des migrations
- `php artisan migrate:rollback` - Annuler la dernière migration
- `php artisan db:seed` - Exécuter les seeders

### Queue
- `php artisan queue:work` - Démarrer le worker
- `php artisan queue:restart` - Redémarrer le worker
- `php artisan queue:failed` - Voir les jobs en échec
- `php artisan queue:flush` - Vider les jobs en échec

### Maintenance
- `php artisan down` - Mode maintenance
- `php artisan up` - Sortir du mode maintenance
- `php artisan optimize:clear` - Nettoyer tous les caches
- `php artisan optimize` - Optimiser l'application

---

**Note** : Ces commandes doivent être exécutées sur le serveur de production avec les permissions appropriées.



