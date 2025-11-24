# Guide d'exécution des migrations en production

## ⚠️ Important : Précautions avant d'exécuter les migrations

Avant d'exécuter les migrations en production, il est **ESSENTIEL** de :

1. **Faire une sauvegarde complète de la base de données**
2. **Tester les migrations en environnement de staging** si possible
3. **Vérifier que l'application est en mode maintenance** (optionnel mais recommandé)
4. **Avoir un plan de rollback** en cas de problème

## 📋 Étapes pour exécuter les migrations en production

### 1. Connexion SSH au serveur

```bash
# Se connecter au serveur O2Switch via SSH
ssh votre-compte@votre-serveur.o2Switch.net

# Naviguer vers le répertoire de l'application
cd /home/votre-compte/herime-academie
# ou
cd /home/votre-compte/www/herime-academie
```

### 2. Sauvegarde de la base de données

**⚠️ OBLIGATOIRE avant toute migration**

```bash
# Option 1 : Via phpMyAdmin
# - Connectez-vous à phpMyAdmin
# - Sélectionnez votre base de données
# - Cliquez sur "Exporter" → "Exécuter"

# Option 2 : Via ligne de commande (si mysqldump est disponible)
mysqldump -u votre_utilisateur -p nom_de_la_base > backup_$(date +%Y%m%d_%H%M%S).sql

# Option 3 : Via Laravel (si configuré)
php artisan db:backup
```

### 3. Mettre l'application en mode maintenance (recommandé)

```bash
# Activer le mode maintenance
php artisan down

# Ou avec un message personnalisé
php artisan down --message="Mise à jour en cours" --retry=60
```

### 4. Vérifier l'état actuel des migrations

```bash
# Voir quelles migrations ont déjà été exécutées
php artisan migrate:status

# Voir les migrations en attente
php artisan migrate --pretend
```

### 5. Exécuter les migrations

```bash
# Exécuter toutes les migrations en attente
php artisan migrate --force

# Le flag --force est nécessaire en production pour éviter la confirmation interactive
```

### 6. Vérifier que tout s'est bien passé

```bash
# Vérifier l'état final des migrations
php artisan migrate:status

# Vérifier qu'il n'y a pas d'erreurs dans les logs
tail -f storage/logs/laravel.log
```

### 7. Désactiver le mode maintenance

```bash
# Remettre l'application en ligne
php artisan up
```

### 8. Optimiser l'application

```bash
# Vider les caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Recréer les caches optimisés
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimisation générale
php artisan optimize
```

## 🔄 Rollback en cas de problème

Si une migration échoue ou cause des problèmes :

```bash
# Rollback de la dernière migration
php artisan migrate:rollback

# Rollback de plusieurs migrations (ex: 3 dernières)
php artisan migrate:rollback --step=3

# Rollback de toutes les migrations
php artisan migrate:reset

# Puis restaurer la base de données depuis la sauvegarde
mysql -u votre_utilisateur -p nom_de_la_base < backup_YYYYMMDD_HHMMSS.sql
```

## 📝 Commandes utiles

### Voir l'état des migrations

```bash
# Liste complète des migrations et leur statut
php artisan migrate:status
```

### Exécuter une migration spécifique

```bash
# Exécuter une migration spécifique (non recommandé en production)
php artisan migrate --path=/database/migrations/nom_du_fichier.php --force
```

### Voir le SQL qui sera exécuté (sans l'exécuter)

```bash
# Mode "dry-run" - montre ce qui sera fait sans l'exécuter
php artisan migrate --pretend
```

## ⚠️ Migrations supprimées dans le dernier commit

Les migrations suivantes ont été supprimées du code (MokoPay, WhatsApp, MaxiCash) :

- `2025_10_15_004218_create_moko_transactions_table.php`
- `2025_10_16_000001_add_whatsapp_fields_to_orders_table.php`
- `2025_10_27_010110_add_foreign_keys_to_moko_transactions_table.php`

**Si ces migrations ont déjà été exécutées en production**, vous devez :

1. **Ne PAS les supprimer de la base de données** (elles sont déjà appliquées)
2. **Les ignorer** - Laravel ne les exécutera plus car les fichiers n'existent plus
3. **Si vous voulez les supprimer**, créer une migration manuelle pour supprimer les tables/colonnes

## 🚨 Problèmes courants et solutions

### Erreur : "Migration table not found"

```bash
# Créer la table de suivi des migrations
php artisan migrate:install
```

### Erreur : "Class not found"

```bash
# Vider le cache et réessayer
php artisan clear-compiled
php artisan config:clear
composer dump-autoload
php artisan migrate --force
```

### Erreur : "Foreign key constraint fails"

```bash
# Vérifier l'ordre des migrations
# Certaines migrations peuvent dépendre d'autres
# Exécutez-les dans l'ordre chronologique
```

### Erreur : "Table already exists"

```bash
# La migration a peut-être été partiellement exécutée
# Vérifiez l'état avec :
php artisan migrate:status

# Si nécessaire, marquez la migration comme exécutée manuellement
# (à faire avec précaution)
```

## 📊 Checklist de déploiement

- [ ] Sauvegarde de la base de données effectuée
- [ ] Mode maintenance activé (optionnel)
- [ ] État des migrations vérifié (`migrate:status`)
- [ ] Migrations testées en mode `--pretend`
- [ ] Migrations exécutées avec `--force`
- [ ] Vérification post-migration réussie
- [ ] Mode maintenance désactivé
- [ ] Caches optimisés
- [ ] Tests fonctionnels effectués
- [ ] Logs vérifiés pour erreurs

## 🔗 Ressources

- [Documentation Laravel - Migrations](https://laravel.com/docs/migrations)
- [Guide de déploiement O2Switch](DEPLOY_O2SWITCH.md)

