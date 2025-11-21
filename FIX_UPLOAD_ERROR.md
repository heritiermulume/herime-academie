# Solution : Erreur "Class FileReceiver not found"

## Problème identifié

L'erreur dans les logs est claire :
```
Class "Pion\Laravel\ChunkUpload\Receiver\FileReceiver" not found
```

Cela signifie que le package `pion/laravel-chunk-upload` n'est pas installé ou que l'autoloader n'est pas à jour en production.

## Solution

### Étape 1 : Se connecter en SSH sur O2Switch

Connectez-vous à votre serveur O2Switch via SSH.

### Étape 2 : Aller dans le répertoire du projet

```bash
cd /home/muhe3594/herime-academie
```

### Étape 3 : Installer les dépendances Composer

```bash
composer install --no-dev --optimize-autoloader
```

**Explication des options :**
- `--no-dev` : N'installe pas les dépendances de développement (recommandé en production)
- `--optimize-autoloader` : Optimise l'autoloader pour de meilleures performances

### Étape 4 : Régénérer l'autoloader (si nécessaire)

```bash
composer dump-autoload --optimize
```

### Étape 5 : Vérifier que le package est installé

```bash
composer show pion/laravel-chunk-upload
```

Vous devriez voir quelque chose comme :
```
name     : pion/laravel-chunk-upload
descrip. : Service for chunked upload with several js providers
keywords : chunk, chunked, file, laravel, resumable, upload
versions : * v1.5.6
```

### Étape 6 : Vérifier que la classe existe

```bash
php -r "require 'vendor/autoload.php'; var_dump(class_exists('Pion\Laravel\ChunkUpload\Receiver\FileReceiver'));"
```

Cela devrait afficher `bool(true)`.

### Étape 7 : Vider le cache Laravel (optionnel mais recommandé)

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### Étape 8 : Tester l'upload

Essayez maintenant d'uploader un fichier. L'erreur devrait être résolue.

## Si le problème persiste

### Vérifier les permissions du dossier vendor

```bash
ls -la vendor/pion/
```

Si le dossier n'existe pas ou n'est pas accessible, réinstallez :

```bash
rm -rf vendor/
composer install --no-dev --optimize-autoloader
```

### Vérifier la version de Composer

```bash
composer --version
```

Assurez-vous d'avoir Composer 2.x (recommandé).

### Vérifier les logs après correction

```bash
tail -f storage/logs/laravel.log
```

Puis testez un upload. Vous ne devriez plus voir l'erreur "Class not found".

## Prévention pour les futurs déploiements

Pour éviter ce problème lors des prochains déploiements, assurez-vous de :

1. **Toujours exécuter `composer install` après avoir fait un `git pull`** :
   ```bash
   git pull
   composer install --no-dev --optimize-autoloader
   ```

2. **Vérifier que le fichier `composer.lock` est commité** (il devrait l'être)

3. **Créer un script de déploiement** qui automatise ces étapes :
   ```bash
   #!/bin/bash
   git pull
   composer install --no-dev --optimize-autoloader
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

## Résumé de la commande à exécuter

```bash
cd /home/muhe3594/herime-academie
composer install --no-dev --optimize-autoloader
composer dump-autoload --optimize
php artisan config:clear
php artisan cache:clear
```

Après ces commandes, l'upload devrait fonctionner ! 🎉

