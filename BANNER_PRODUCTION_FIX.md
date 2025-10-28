# Correction du stockage des images de bannières en production

## Problème
Les images des bannières ne s'enregistrent pas lors de la création ou modification en production.

## Causes possibles
1. Le lien symbolique `public/storage` n'existe pas ou est cassé
2. Les permissions sur le dossier `storage/app/public` ne sont pas correctes
3. Le dossier `storage/app/public/banners` n'existe pas
4. Problème de configuration du stockage

## Solution

### Étape 1 : Exécuter le script de correction

Sur le serveur de production, exécutez le script suivant :

```bash
chmod +x fix-banner-storage.sh
./fix-banner-storage.sh
```

### Étape 2 : Vérifications manuelles

#### 2.1 Vérifier le lien symbolique
```bash
ls -la public/ | grep storage
```
Vous devriez voir : `storage -> /chemin/vers/storage/app/public`

#### 2.2 Vérifier les permissions
```bash
ls -la storage/app/public/
```
Les permissions doivent être `drwxrwxr-x` (775)

#### 2.3 Vérifier le dossier banners
```bash
ls -la storage/app/public/banners/
```
Le dossier doit exister avec les permissions 775

### Étape 3 : Test de création d'image

1. Connectez-vous à l'administration
2. Allez dans "Gestion des bannières"
3. Créez une nouvelle bannière avec une image
4. Vérifiez que l'image s'affiche correctement

### Étape 4 : Vérifier les logs en cas d'erreur

```bash
tail -f storage/logs/laravel.log
```

## Modifications techniques apportées

### 1. Stockage des chemins relatifs
Au lieu de stocker l'URL complète (`https://site.com/storage/banners/image.jpg`), 
nous stockons maintenant le chemin relatif (`storage/banners/image.jpg`).

### 2. Génération de l'URL à l'affichage
Les vues utilisent maintenant `asset()` pour générer l'URL complète à partir du chemin relatif :
```php
{{ asset($banner->image) }}
```

### 3. Gestion des images existantes
Le code gère à la fois :
- Les anciennes images avec URL complète (commençant par `http`)
- Les nouvelles images avec chemin relatif

### 4. Amélioration de la suppression
Utilisation de `Storage::disk('public')->exists()` et `Storage::disk('public')->delete()` 
au lieu de vérifier manuellement l'existence des fichiers.

## Commandes utiles

### Recréer le lien symbolique
```bash
php artisan storage:link
```

### Vérifier la configuration du stockage
```bash
php artisan tinker
>>> config('filesystems.disks.public')
```

### Lister les bannières et leurs images
```bash
php artisan tinker
>>> App\Models\Banner::select('id', 'title', 'image', 'mobile_image')->get()
```

### Corriger les permissions (O2Switch/Linux)
```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
chown -R www-data:www-data storage
chown -R www-data:www-data bootstrap/cache
```

## En cas de problème persistant

### Vérifier l'upload max size
Éditez `php.ini` et vérifiez :
```ini
upload_max_filesize = 10M
post_max_size = 12M
memory_limit = 256M
```

### Vérifier les droits du serveur web
```bash
ps aux | grep -E 'apache|nginx'
```
Le propriétaire doit avoir accès en écriture au dossier storage.

### Vérifier l'espace disque
```bash
df -h
```

### Tester l'upload manuellement
Créez un fichier test dans le dossier :
```bash
touch storage/app/public/banners/test.txt
ls -la storage/app/public/banners/
```

Si le fichier ne peut pas être créé, c'est un problème de permissions.

## Support

Si le problème persiste après avoir suivi toutes ces étapes :
1. Vérifiez les logs Laravel : `storage/logs/laravel.log`
2. Vérifiez les logs du serveur web (Apache/Nginx)
3. Contactez le support de l'hébergeur si nécessaire

