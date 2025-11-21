# Guide de débogage pour les erreurs d'upload

## Résultats du diagnostic

D'après le diagnostic sur https://academie.herime.com/diagnose-upload.php, la configuration est **OK** :
- ✅ Limites PHP correctes (512M)
- ✅ Permissions correctes
- ✅ Espace disque suffisant
- ✅ Extensions nécessaires présentes

## Problème identifié

L'erreur `{ "message": "Server Error" }` persiste malgré une configuration correcte. Cela signifie que l'erreur se produit probablement :

1. **Avant que Laravel ne traite la requête** (erreur serveur web)
2. **Dans le code Laravel mais non loggée** (exception non capturée)
3. **Dans le package de chunk upload** (pion/laravel-chunk-upload)

## Étapes de débogage

### 1. Vérifier les logs Laravel en temps réel

Connectez-vous en SSH sur O2Switch et exécutez :

```bash
cd /home/muhe3594/herime-academie
tail -f storage/logs/laravel.log
```

Puis, dans un autre terminal ou navigateur, essayez d'uploader un fichier. Vous devriez voir les erreurs apparaître en temps réel.

### 2. Vérifier les dernières erreurs dans les logs

```bash
cd /home/muhe3594/herime-academie
tail -n 100 storage/logs/laravel.log | grep -i "error\|exception\|upload"
```

### 3. Vérifier les logs Apache/Nginx

Si les logs Laravel ne montrent rien, l'erreur peut se produire au niveau du serveur web :

**Pour Apache :**
```bash
tail -f /var/log/apache2/error.log
# ou
tail -f /var/log/httpd/error_log
```

**Pour Nginx :**
```bash
tail -f /var/log/nginx/error.log
```

### 4. Tester l'endpoint d'upload directement

Créez un fichier de test `test-upload.php` dans `public/` :

```php
<?php
// Test simple d'upload
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['file'])) {
        $file = $_FILES['file'];
        echo json_encode([
            'success' => true,
            'file_name' => $file['name'],
            'file_size' => $file['size'],
            'file_type' => $file['type'],
            'error' => $file['error'],
            'error_message' => $file['error'] === UPLOAD_ERR_OK ? 'OK' : 'Error code: ' . $file['error'],
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'No file uploaded',
            'post_data' => $_POST,
            'files_data' => $_FILES,
        ]);
    }
} else {
    echo json_encode(['error' => 'POST method required']);
}
```

Testez avec :
```bash
curl -X POST -F "file=@/chemin/vers/votre/fichier.jpg" https://academie.herime.com/test-upload.php
```

### 5. Vérifier les limites du serveur web

Le serveur web (Apache/Nginx) peut avoir ses propres limites qui bloquent les requêtes avant PHP :

**Pour Apache**, vérifiez dans `.htaccess` ou la configuration :
```apache
LimitRequestBody 0  # Pas de limite
```

**Pour Nginx**, vérifiez dans la configuration :
```nginx
client_max_body_size 1024M;
```

### 6. Activer le mode debug temporairement

Dans `.env`, changez temporairement :
```env
APP_DEBUG=true
APP_ENV=local
```

⚠️ **Important** : Remettez `APP_DEBUG=false` en production après le débogage.

### 7. Vérifier les erreurs JavaScript dans la console

Ouvrez la console du navigateur (F12) et vérifiez :
- Les erreurs JavaScript
- Les requêtes réseau (onglet Network)
- La réponse exacte du serveur

### 8. Tester avec un fichier plus petit

Essayez d'uploader un fichier très petit (quelques Ko) pour voir si le problème est lié à la taille.

## Solutions possibles

### Solution 1 : Vérifier la configuration Nginx/Apache

Si vous utilisez Nginx comme reverse proxy, vérifiez que `client_max_body_size` est suffisant.

### Solution 2 : Vérifier les logs du package chunk-upload

Le package `pion/laravel-chunk-upload` peut avoir ses propres logs. Vérifiez :
```bash
grep -r "chunk" storage/logs/laravel.log
```

### Solution 3 : Désactiver temporairement le middleware SSO

Le middleware `ValidateSSOOnPageLoad` pourrait causer des problèmes. Testez en le désactivant temporairement.

### Solution 4 : Vérifier les permissions du dossier temporaire PHP

Le dossier temporaire PHP (`upload_tmp_dir`) doit être accessible :
```bash
php -r "echo ini_get('upload_tmp_dir') ?: sys_get_temp_dir();"
```

## Informations à collecter

Si le problème persiste, collectez ces informations :

1. **Dernières lignes des logs Laravel** :
   ```bash
   tail -n 200 storage/logs/laravel.log > upload-errors.log
   ```

2. **Configuration PHP complète** :
   ```bash
   php -i > php-info.txt
   ```

3. **Taille du fichier que vous essayez d'uploader**

4. **Type de fichier** (image, vidéo, document)

5. **Route exacte utilisée** (admin ou instructor)

6. **Réponse complète du serveur** (depuis la console du navigateur)

## Contact support

Si le problème persiste après ces vérifications, contactez le support O2Switch avec :
- Les résultats du diagnostic
- Les logs Laravel
- La configuration PHP
- Les détails de l'erreur

