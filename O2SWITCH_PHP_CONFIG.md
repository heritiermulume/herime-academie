# Configuration PHP recommandée pour O2Switch

Ce document décrit les configurations PHP nécessaires pour que l'upload de fichiers fonctionne correctement sur O2Switch.

## Problème courant

L'erreur `{ "message": "Server Error" }` lors de l'upload de fichiers en production est généralement causée par :

1. **Limites PHP insuffisantes** (upload_max_filesize, post_max_size)
2. **Permissions de fichiers/dossiers incorrectes**
3. **Timeouts trop courts** (max_execution_time, max_input_time)
4. **Limites mémoire insuffisantes** (memory_limit)

## Configuration PHP recommandée

### Via le fichier `.htaccess` (si mod_php est activé)

Ajoutez ces lignes dans le fichier `.htaccess` à la racine de votre projet :

```apache
# Configuration PHP pour uploads de gros fichiers
php_value upload_max_filesize 1024M
php_value post_max_size 1024M
php_value max_execution_time 3600
php_value max_input_time 3600
php_value memory_limit 512M

# Augmenter la taille des chunks
php_value max_file_uploads 50
```

### Via le fichier `php.ini` (si accessible)

Si vous avez accès au fichier `php.ini` via le panneau O2Switch :

```ini
; Configuration pour uploads de gros fichiers
upload_max_filesize = 1024M
post_max_size = 1024M
max_execution_time = 3600
max_input_time = 3600
memory_limit = 512M
max_file_uploads = 50

; Configuration du dossier temporaire
upload_tmp_dir = /tmp
```

### Via le panneau O2Switch

1. Connectez-vous à votre espace client O2Switch
2. Allez dans **"Configuration PHP"** ou **"Paramètres PHP"**
3. Modifiez les valeurs suivantes :
   - `upload_max_filesize` : **1024M** (ou au moins 100M)
   - `post_max_size` : **1024M** (doit être >= upload_max_filesize)
   - `max_execution_time` : **3600** (1 heure)
   - `max_input_time` : **3600** (1 heure)
   - `memory_limit` : **512M** (ou au moins 256M)

## Vérification des permissions

### Dossiers à vérifier

Assurez-vous que les dossiers suivants ont les bonnes permissions (755 pour les dossiers, 644 pour les fichiers) :

```bash
# Dossiers de stockage Laravel
storage/app/                    # 755
storage/app/tmp/                # 755
storage/app/tmp/uploads/        # 755
storage/app/courses/            # 755
storage/app/courses/thumbnails/ # 755
storage/app/courses/previews/   # 755
storage/app/courses/lessons/    # 755
storage/logs/                   # 755
bootstrap/cache/                # 755
```

### Commandes pour définir les permissions (via SSH)

Si vous avez accès SSH :

```bash
# Aller dans le répertoire du projet
cd /home/votre-compte/votre-site

# Définir les permissions pour storage
chmod -R 755 storage
chmod -R 755 bootstrap/cache

# Créer les dossiers nécessaires s'ils n'existent pas
mkdir -p storage/app/tmp/uploads
mkdir -p storage/app/courses/thumbnails
mkdir -p storage/app/courses/previews
mkdir -p storage/app/courses/lessons

# Définir le propriétaire (remplacez www-data par l'utilisateur du serveur web)
chown -R www-data:www-data storage
chown -R www-data:www-data bootstrap/cache
```

## Vérification de la configuration

### Script de vérification PHP

Créez un fichier `check-upload-config.php` à la racine de votre projet pour vérifier la configuration :

```php
<?php
echo "<h2>Vérification de la configuration PHP pour les uploads</h2>";

echo "<h3>Limites PHP</h3>";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "<br>";
echo "post_max_size: " . ini_get('post_max_size') . "<br>";
echo "max_execution_time: " . ini_get('max_execution_time') . " secondes<br>";
echo "max_input_time: " . ini_get('max_input_time') . " secondes<br>";
echo "memory_limit: " . ini_get('memory_limit') . "<br>";
echo "max_file_uploads: " . ini_get('max_file_uploads') . "<br>";

echo "<h3>Permissions</h3>";
$storagePath = __DIR__ . '/storage/app';
echo "storage/app existe: " . (is_dir($storagePath) ? 'Oui' : 'Non') . "<br>";
echo "storage/app est accessible en écriture: " . (is_writable($storagePath) ? 'Oui' : 'Non') . "<br>";

$tmpPath = __DIR__ . '/storage/app/tmp';
echo "storage/app/tmp existe: " . (is_dir($tmpPath) ? 'Oui' : 'Non') . "<br>";
if (is_dir($tmpPath)) {
    echo "storage/app/tmp est accessible en écriture: " . (is_writable($tmpPath) ? 'Oui' : 'Non') . "<br>";
}

echo "<h3>Recommandations</h3>";
$uploadMax = ini_get('upload_max_filesize');
$postMax = ini_get('post_max_size');

if (intval($uploadMax) < 100) {
    echo "⚠️ upload_max_filesize est trop faible (recommandé: au moins 100M)<br>";
}

if (intval($postMax) < intval($uploadMax)) {
    echo "⚠️ post_max_size doit être >= upload_max_filesize<br>";
}

if (ini_get('max_execution_time') < 300) {
    echo "⚠️ max_execution_time est trop faible pour les gros fichiers (recommandé: au moins 300)<br>";
}
```

Accédez à `https://votre-site.com/check-upload-config.php` pour voir la configuration actuelle.

**⚠️ Important : Supprimez ce fichier après vérification pour des raisons de sécurité.**

## Configuration Nginx (si applicable)

Si votre site utilise Nginx, vous devrez peut-être aussi configurer :

```nginx
client_max_body_size 1024M;
client_body_timeout 3600s;
client_body_buffer_size 128k;
```

## Vérification des logs

Après avoir appliqué ces configurations, vérifiez les logs Laravel pour voir les erreurs détaillées :

```bash
tail -f storage/logs/laravel.log
```

Les erreurs d'upload seront maintenant loggées avec plus de détails grâce aux améliorations apportées au `ChunkUploadController`.

## Support O2Switch

Si vous ne pouvez pas modifier ces paramètres vous-même, contactez le support O2Switch en leur demandant de :

1. Augmenter `upload_max_filesize` à au moins 100M (idéalement 1024M)
2. Augmenter `post_max_size` à au moins 100M (idéalement 1024M)
3. Augmenter `max_execution_time` à au moins 300 secondes (idéalement 3600)
4. Augmenter `memory_limit` à au moins 256M (idéalement 512M)

## Test après configuration

1. Connectez-vous à l'administration
2. Essayez d'uploader un fichier (image, vidéo, document)
3. Vérifiez les logs dans `storage/logs/laravel.log` si une erreur persiste
4. Les messages d'erreur seront maintenant plus explicites grâce aux améliorations du code

## Notes importantes

- `post_max_size` doit toujours être **supérieur ou égal** à `upload_max_filesize`
- Les valeurs sont en **mégaoctets (M)** ou **gigaoctets (G)**
- Après modification de la configuration PHP, **redémarrez PHP-FPM** si possible
- Sur certains hébergements, il faut attendre quelques minutes pour que les changements prennent effet

