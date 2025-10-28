# Correction de l'erreur PostTooLargeException

## L'erreur
```
Illuminate\Http\Exceptions\PostTooLargeException
Les données POST sont trop volumineuses.
```

## Cause
Quand vous uploadez 2 images de bannières (desktop + mobile), le total des données peut dépasser les limites PHP configurées par défaut.

## Solution rapide pour O2Switch

### ⚠️ IMPORTANT : Le fichier `.htaccess` prend le dessus !

Sur O2Switch (et la plupart des hébergements Apache), le fichier `public/.htaccess` a la priorité sur `.user.ini`. 
Il faut donc modifier le `.htaccess` en premier lieu.

### 1. Modifier le fichier `.htaccess`

```bash
cd /homepages/XX/dXXXXXXX/htdocs/herime-academie/
git pull origin main
```

Le fichier `public/.htaccess` devrait maintenant contenir :

```apache
# Augmenter les limites PHP pour les uploads (bannières avec 2 images)
php_value upload_max_filesize 20M
php_value post_max_size 30M
php_value max_execution_time 300
php_value max_input_time 300
php_value memory_limit 512M
```

### 2. Vérifier que les modifications sont actives (IMMÉDIAT)

⚡ Contrairement à `.user.ini`, les modifications du `.htaccess` sont **immédiates** !

Créez une URL de test :
```
https://votre-site.com/check-php-limits.php?key=herime2024
```

Cette page vous montrera les limites actuelles.

### 3. Alternative : Modifier `.user.ini` (si .htaccess ne fonctionne pas)

Si les directives `php_value` dans `.htaccess` causent une erreur 500, utilisez `.user.ini` :

```bash
nano .user.ini
```

Ajoutez :
```ini
upload_max_filesize = 20M
post_max_size = 30M
memory_limit = 512M
max_execution_time = 300
max_input_time = 300
max_input_vars = 3000
```

⏰ Puis attendez 5-10 minutes pour que `.user.ini` soit pris en compte.

### 4. Tester l'upload de bannières

Retournez dans l'admin et créez une nouvelle bannière avec 2 images.

## Explication des valeurs

| Configuration | Valeur | Pourquoi ? |
|--------------|--------|-----------|
| `upload_max_filesize` | 20M | Taille maximum par fichier image (desktop ou mobile) |
| `post_max_size` | 30M | Taille totale du formulaire (2 images + données texte) |
| `memory_limit` | 512M | Mémoire disponible pour PHP pour traiter les images |

⚠️ **Important** : `post_max_size` doit TOUJOURS être supérieur à `upload_max_filesize`

## Si le problème persiste

### Vérifier la configuration actuelle via SSH

```bash
php -r "echo 'upload_max_filesize: ' . ini_get('upload_max_filesize') . PHP_EOL;"
php -r "echo 'post_max_size: ' . ini_get('post_max_size') . PHP_EOL;"
php -r "echo 'memory_limit: ' . ini_get('memory_limit') . PHP_EOL;"
```

### Contacter le support O2Switch

Si après 10 minutes les valeurs ne sont toujours pas à jour, contactez le support O2Switch avec :

1. Le chemin exact de votre `.user.ini`
2. Le contenu du fichier
3. Cette erreur : `PostTooLargeException`

## Alternative : Compresser les images avant upload

Si vous ne pouvez pas modifier la configuration PHP, optimisez vos images :

### Outils en ligne gratuits
- [TinyPNG](https://tinypng.com/) - Compression PNG/JPG
- [Squoosh](https://squoosh.app/) - Compression avancée Google
- [ImageOptim](https://imageoptim.com/) - Application Mac

### Taille recommandée pour les bannières
- **Image desktop** : Max 1920x1080px, qualité 85%, ~500-800 KB
- **Image mobile** : Max 768x768px, qualité 80%, ~200-400 KB

Après compression, les 2 images ne devraient pas dépasser 2-3 MB au total.

## Vérification finale

✅ Checklist :
- [ ] Fichier `.user.ini` créé avec les bonnes valeurs
- [ ] Attendre 5-10 minutes
- [ ] Vérifier avec `phpinfo.php` ou commande SSH
- [ ] Tester la création d'une bannière
- [ ] Supprimer `phpinfo.php` si créé

## En développement local

Si vous testez en local, modifiez le fichier `php.ini` de votre serveur local :

```bash
# XAMPP
/Applications/XAMPP/xamppfiles/etc/php.ini

# MAMP
/Applications/MAMP/bin/php/php8.x.x/conf/php.ini

# Laravel Valet (Mac)
~/.config/valet/php.ini
```

Puis redémarrez votre serveur local.

