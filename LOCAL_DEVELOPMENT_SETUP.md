# Configuration du développement local

## Problème : PostTooLargeException en local

Lorsque vous utilisez `php artisan serve` pour le développement local, le fichier `.htaccess` est ignoré car ce serveur n'utilise pas Apache.

### Erreur typique
```
Illuminate\Http\Exceptions\PostTooLargeException
Les données POST sont trop volumineuses.
```

## Solution rapide : Utiliser le script de démarrage

Au lieu de `php artisan serve`, utilisez :

```bash
./serve-with-limits.sh
```

Ce script lance le serveur Laravel avec les bonnes limites PHP configurées automatiquement.

## Vérification des limites

Pour vérifier que les limites sont correctes :

```bash
php -r "echo 'upload_max_filesize: ' . ini_get('upload_max_filesize') . PHP_EOL;"
php -r "echo 'post_max_size: ' . ini_get('post_max_size') . PHP_EOL;"
php -r "echo 'memory_limit: ' . ini_get('memory_limit') . PHP_EOL;"
```

## Limites configurées

| Configuration | Valeur par défaut | Valeur nécessaire |
|--------------|-------------------|-------------------|
| `upload_max_filesize` | 2M | **20M** |
| `post_max_size` | 8M | **30M** |
| `memory_limit` | 128M | **512M** |

## Solutions alternatives

### Option 1 : Commande manuelle
```bash
php -d upload_max_filesize=20M -d post_max_size=30M -d memory_limit=512M artisan serve
```

### Option 2 : Modifier le php.ini global (nécessite sudo)

1. Trouver le fichier php.ini :
```bash
php --ini
```

2. Éditer le fichier (exemple pour PHP 8.4 via Homebrew) :
```bash
sudo nano /usr/local/etc/php/8.4/php.ini
```

3. Modifier ces lignes :
```ini
upload_max_filesize = 20M
post_max_size = 30M
memory_limit = 512M
```

4. Sauvegarder (Ctrl+O, Entrée, Ctrl+X)

5. Redémarrer PHP-FPM si utilisé :
```bash
brew services restart php@8.4
```

### Option 3 : Créer un fichier de configuration additionnel (nécessite sudo)

1. Créer un fichier dans conf.d :
```bash
sudo nano /usr/local/etc/php/8.4/conf.d/herime-academie.ini
```

2. Ajouter :
```ini
upload_max_filesize = 20M
post_max_size = 30M
memory_limit = 512M
max_execution_time = 300
max_input_time = 300
max_input_vars = 3000
```

3. Vérifier que c'est chargé :
```bash
php --ini
```

## Différences Local vs Production

| Aspect | Local (Mac) | Production (O2Switch) |
|--------|-------------|----------------------|
| Serveur | PHP Built-in | Apache |
| Configuration | `serve-with-limits.sh` ou `php.ini` | `public/.htaccess` |
| Effet | Immédiat après redémarrage | Immédiat |
| Sudo requis | Optionnel | Non |

## Recommandation

Pour le développement quotidien, utilisez simplement :

```bash
./serve-with-limits.sh
```

C'est la solution la plus simple et ne nécessite aucune modification système.

## Test après configuration

1. Démarrez le serveur avec `./serve-with-limits.sh`
2. Allez dans l'administration : http://127.0.0.1:8000/admin/banners
3. Créez une bannière avec 2 images (desktop + mobile)
4. ✅ L'upload devrait fonctionner sans erreur !

## Support

Si vous rencontrez toujours des problèmes :

1. Vérifiez que le serveur a été démarré avec `./serve-with-limits.sh`
2. Vérifiez les logs : `tail -f storage/logs/laravel.log`
3. Vérifiez les limites actives durant l'exécution

