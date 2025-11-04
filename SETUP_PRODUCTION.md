# 📋 Guide complet : Configuration de la Production

## 🎯 Objectif
Recréer tous les dossiers et configurations du système d'upload optimisé en production.

---

## 📁 Étape 1 : Créer les dossiers de stockage

Exécutez ces commandes sur votre serveur de production :

```bash
# Aller dans le répertoire du projet
cd /chemin/vers/herime-academie

# Créer tous les dossiers nécessaires
mkdir -p storage/app/private/courses/thumbnails
mkdir -p storage/app/private/courses/previews
mkdir -p storage/app/private/courses/lessons
mkdir -p storage/app/private/courses/downloads
mkdir -p storage/app/private/avatars
mkdir -p storage/app/private/banners

# Créer le fichier .gitignore
cat > storage/app/private/.gitignore << 'EOF'
*
!.gitignore
EOF
```

---

## 🔐 Étape 2 : Configurer les permissions

```bash
# Donner les permissions d'écriture au serveur web
chmod -R 775 storage/app/private

# Si vous avez accès root/sudo, définir le propriétaire
sudo chown -R www-data:www-data storage/app/private
# Ou selon votre configuration :
# sudo chown -R apache:apache storage/app/private
# sudo chown -R nginx:nginx storage/app/private
```

---

## ⚙️ Étape 3 : Vérifier la configuration

### 3.1 Vérifier `config/filesystems.php`

Le fichier doit contenir :

```php
'disks' => [
    'local' => [
        'driver' => 'local',
        'root' => storage_path('app/private'),
        'serve' => true,
        'throw' => false,
        'report' => false,
    ],
    // ... autres disques
],
```

### 3.2 Vérifier `routes/web.php`

Doit contenir la route pour le FileController :

```php
use App\Http\Controllers\FileController;

// ...

Route::get('/files/{type}/{path}', [FileController::class, 'serve'])
    ->where('path', '.*')
    ->name('files.serve');
```

### 3.3 Vérifier les fichiers nécessaires

Ces fichiers doivent exister :
- ✅ `app/Services/FileUploadService.php`
- ✅ `app/Http/Controllers/FileController.php`
- ✅ `app/Helpers/FileHelper.php`

---

## 📦 Étape 4 : Mettre à jour depuis GitHub

Si vous n'avez pas encore fait le pull :

```bash
# Résoudre le conflit si nécessaire (voir instructions précédentes)
git checkout --theirs storage/app/private/.gitignore
git add storage/app/private/.gitignore
git commit -m "Résolution conflit .gitignore"

# Faire le pull
git pull origin main
```

---

## 🚀 Étape 5 : Optimiser l'application

```bash
# Vider le cache
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Reconstruire le cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## ✅ Étape 6 : Vérification finale

### Test 1 : Vérifier que les dossiers existent

```bash
ls -la storage/app/private/
```

Devrait afficher :
```
avatars/
banners/
courses/
  downloads/
  lessons/
  previews/
  thumbnails/
.gitignore
```

### Test 2 : Vérifier les permissions

```bash
ls -la storage/app/private/courses/
```

Les permissions doivent être `drwxrwxr-x` (775)

### Test 3 : Tester un upload

1. Connectez-vous en admin
2. Créez ou modifiez un cours
3. Essayez d'uploader une image/thumbnail
4. Vérifiez que le fichier apparaît dans `storage/app/private/courses/thumbnails/`

### Test 4 : Vérifier l'accès aux fichiers

Essayez d'accéder à un fichier uploadé via l'URL :
```
https://votre-domaine.com/files/thumbnails/nom-du-fichier.jpg
```

---

## 🔧 Utilisation du script automatique

Si vous préférez utiliser le script automatique :

```bash
# Rendre le script exécutable
chmod +x setup-production.sh

# Exécuter le script
./setup-production.sh
```

Le script fera automatiquement :
- ✅ Création des dossiers
- ✅ Création du .gitignore
- ✅ Configuration des permissions
- ✅ Vérification des fichiers
- ✅ Optimisation de l'application

---

## ⚠️ Problèmes courants

### Erreur : "Permission denied"
```bash
# Solution : Ajuster les permissions
chmod -R 775 storage/app/private
sudo chown -R www-data:www-data storage/app/private
```

### Erreur : "File not found" lors de l'accès
- Vérifiez que la route `/files/{type}/{path}` existe
- Vérifiez que `FileController` est bien présent
- Videz le cache : `php artisan route:clear && php artisan route:cache`

### Erreur : "Storage disk 'local' not found"
- Vérifiez `config/filesystems.php`
- Exécutez : `php artisan config:clear && php artisan config:cache`

---

## 📞 Support

Si vous rencontrez des problèmes :
1. Vérifiez les logs : `storage/logs/laravel.log`
2. Vérifiez les permissions des dossiers
3. Vérifiez que PHP a les droits d'écriture
4. Vérifiez la configuration du serveur web (Apache/Nginx)

---

## 📝 Résumé des dossiers créés

```
storage/app/private/
├── .gitignore
├── avatars/
├── banners/
└── courses/
    ├── downloads/
    ├── lessons/
    ├── previews/
    └── thumbnails/
```

Tous ces dossiers doivent avoir les permissions `775` et être accessibles en écriture par le serveur web.


