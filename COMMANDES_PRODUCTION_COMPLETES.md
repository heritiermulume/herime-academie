# Commandes de Production - Laravel

Guide complet des commandes pour exécuter les migrations, nettoyer les caches et optimiser l'application en production.

## 📋 Table des Matières

1. [Migrations](#migrations)
2. [Nettoyage des Caches (Clear)](#nettoyage-des-caches-clear)
3. [Optimisation](#optimisation)
4. [Commandes Complètes](#commandes-complètes)
5. [Scripts Automatiques](#scripts-automatiques)

---

## 🗄️ Migrations

### Exécuter les migrations

```bash
# Exécuter toutes les migrations en attente
php artisan migrate --force

# Exécuter les migrations avec affichage des requêtes SQL
php artisan migrate --force --pretend

# Exécuter une migration spécifique
php artisan migrate --path=/database/migrations/2025_11_14_215638_add_show_students_count_to_courses_table.php --force

# Rollback la dernière migration
php artisan migrate:rollback --step=1

# Rollback toutes les migrations
php artisan migrate:reset --force

# Voir le statut des migrations
php artisan migrate:status
```

### Migration avec seed

```bash
# Exécuter les migrations et les seeders
php artisan migrate --force --seed

# Exécuter uniquement les seeders
php artisan db:seed --force
```

---

## 🧹 Nettoyage des Caches (Clear)

### Vider tous les caches (Recommandé)

```bash
# Vider tous les caches en une seule commande
php artisan optimize:clear
```

Cette commande exécute automatiquement :
- `config:clear`
- `route:clear`
- `view:clear`
- `cache:clear`
- `event:clear`

### Vider les caches individuellement

```bash
# Vider le cache de configuration
php artisan config:clear

# Vider le cache des routes
php artisan route:clear

# Vider le cache des vues
php artisan view:clear

# Vider le cache de l'application
php artisan cache:clear

# Vider le cache des événements
php artisan event:clear

# Vider le cache de l'autoload Composer
composer dump-autoload
```

### Nettoyage avancé

```bash
# Nettoyer les fichiers compilés
php artisan clear-compiled

# Supprimer les fichiers de cache compilés
rm -rf bootstrap/cache/*.php

# Nettoyer les sessions expirées
php artisan session:gc

# Vider le cache de l'OPcache (si activé)
php artisan opcache:clear
```

---

## ⚡ Optimisation

### Optimiser l'application (Recommandé)

```bash
# Optimiser l'application complète
php artisan optimize
```

Cette commande exécute automatiquement :
- `config:cache`
- `route:cache`
- `view:cache`
- `event:cache`

### Optimiser individuellement

```bash
# Cache de configuration
php artisan config:cache

# Cache des routes
php artisan route:cache

# Cache des vues
php artisan view:cache

# Cache des événements
php artisan event:cache
```

### Optimiser Composer

```bash
# Optimiser l'autoloader Composer (production)
composer dump-autoload --optimize --classmap-authoritative --no-dev

# Optimiser l'autoloader (développement)
composer dump-autoload --optimize
```

### Compiler les assets frontend

```bash
# Compiler les assets pour la production
npm run build

# Ou si vous utilisez Vite
npm run build
```

---

## 🔄 Commandes Complètes

### Séquence complète de déploiement

```bash
# 1. Pull les dernières modifications
git pull origin main

# 2. Installer les dépendances Composer
composer install --no-dev --optimize-autoloader

# 3. Installer les dépendances NPM
npm install

# 4. Compiler les assets
npm run build

# 5. Exécuter les migrations
php artisan migrate --force

# 6. Vider tous les caches
php artisan optimize:clear

# 7. Optimiser l'application
php artisan optimize

# 8. Vérifier les permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Séquence d'optimisation seule

```bash
# 1. Vider tous les caches
php artisan optimize:clear

# 2. Nettoyer les fichiers compilés
php artisan clear-compiled
rm -rf bootstrap/cache/*.php

# 3. Optimiser Composer
composer dump-autoload --optimize --classmap-authoritative --no-dev

# 4. Optimiser Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 5. Compiler les assets
npm run build

# 6. Nettoyer les sessions
php artisan session:gc
```

---

## 🤖 Scripts Automatiques

### Script d'optimisation

```bash
# Exécuter le script d'optimisation
chmod +x optimize-production.sh
./optimize-production.sh
```

### Script de déploiement complet

```bash
# Exécuter le script de déploiement
chmod +x deploy-production.sh
./deploy-production.sh
```

### Script de diagnostic

```bash
# Exécuter le diagnostic de production
chmod +x run-production-diagnostic.sh
./run-production-diagnostic.sh
```

---

## 📝 Commandes Rapides (Résumé)

### Migration uniquement

```bash
php artisan migrate --force
```

### Clear uniquement

```bash
php artisan optimize:clear
```

### Optimiser uniquement

```bash
php artisan optimize
```

### Tout faire (Migration + Clear + Optimise)

```bash
php artisan migrate --force && \
php artisan optimize:clear && \
php artisan optimize && \
npm run build
```

---

## ⚠️ Notes Importantes

### En Production

1. **Toujours utiliser `--force`** avec les migrations pour éviter les confirmations interactives
2. **Ne jamais exécuter `php artisan optimize:clear`** en production pendant les heures de pointe
3. **Toujours exécuter `php artisan optimize`** après le clear en production
4. **Vérifier les permissions** après chaque déploiement
5. **Sauvegarder la base de données** avant d'exécuter les migrations

### Ordre Recommandé

1. **Backup de la base de données**
2. **Pull du code**
3. **Installation des dépendances**
4. **Compilation des assets**
5. **Exécution des migrations**
6. **Nettoyage des caches**
7. **Optimisation**
8. **Vérification des permissions**

### Permissions

```bash
# Permissions pour storage et cache
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Ou avec sudo si nécessaire
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache
```

---

## 🔍 Vérification

### Vérifier l'état des migrations

```bash
php artisan migrate:status
```

### Vérifier les routes cachées

```bash
php artisan route:list
```

### Vérifier la configuration

```bash
php artisan config:show
```

### Vérifier les logs

```bash
tail -f storage/logs/laravel.log
```

---

## 🆘 Dépannage

### Si les migrations échouent

```bash
# Voir le statut des migrations
php artisan migrate:status

# Rollback la dernière migration
php artisan migrate:rollback --step=1

# Réessayer
php artisan migrate --force
```

### Si les caches ne se vident pas

```bash
# Vider manuellement tous les caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
php artisan event:clear

# Supprimer les fichiers de cache manuellement
rm -rf bootstrap/cache/*.php
rm -rf storage/framework/cache/*
rm -rf storage/framework/views/*
```

### Si l'optimisation échoue

```bash
# Vider d'abord tous les caches
php artisan optimize:clear

# Réessayer l'optimisation
php artisan optimize

# Vérifier les permissions
ls -la bootstrap/cache
ls -la storage/framework
```

---

## 📚 Ressources

- [Documentation Laravel - Migrations](https://laravel.com/docs/migrations)
- [Documentation Laravel - Cache](https://laravel.com/docs/cache)
- [Documentation Laravel - Optimization](https://laravel.com/docs/deployment#optimization)

---

**Dernière mise à jour :** 2025-01-14



