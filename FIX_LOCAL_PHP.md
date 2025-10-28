# ❌ PROBLÈME : Configuration PHP locale insuffisante

## Le problème

Les options `-d` en ligne de commande **ne fonctionnent pas** pour modifier `post_max_size` et `upload_max_filesize` sur votre installation PHP.

Limites actuelles :
- ❌ `upload_max_filesize` = **2M** (requis: 20M)
- ❌ `post_max_size` = **8M** (requis: 30M)
- ❌ `memory_limit` = **128M** (requis: 512M)

## ✅ Solution automatique (RECOMMANDÉE)

### Exécutez ce script (demande le mot de passe admin) :

```bash
./fix-php-ini.sh
```

Le script va :
1. 🔒 Créer une sauvegarde de votre `php.ini`
2. ✏️ Modifier les 3 valeurs nécessaires
3. ✅ Afficher les nouvelles limites

**Puis redémarrez le serveur :**
```bash
# Arrêtez le serveur actuel (Ctrl+C dans le terminal où il tourne)
php artisan serve
```

**Testez :**
```
http://127.0.0.1:8000/test-limits.php
```

Vous devriez voir **✅ OK** en vert pour toutes les limites.

---

## 📝 Solution manuelle (si le script échoue)

### Étape 1 : Ouvrir le fichier php.ini

```bash
sudo nano /usr/local/etc/php/8.4/php.ini
```

### Étape 2 : Chercher et modifier

Utilisez `Ctrl+W` pour chercher dans nano.

**Cherchez :** `upload_max_filesize`  
**Remplacez la ligne par :**
```ini
upload_max_filesize = 20M
```

**Cherchez :** `post_max_size`  
**Remplacez la ligne par :**
```ini
post_max_size = 30M
```

**Cherchez :** `memory_limit`  
**Remplacez la ligne par :**
```ini
memory_limit = 512M
```

### Étape 3 : Sauvegarder et quitter

1. `Ctrl+O` (puis `Entrée`) pour **sauvegarder**
2. `Ctrl+X` pour **quitter**

### Étape 4 : Vérifier

```bash
php -r "echo 'upload_max_filesize: ' . ini_get('upload_max_filesize') . PHP_EOL;"
php -r "echo 'post_max_size: ' . ini_get('post_max_size') . PHP_EOL;"
php -r "echo 'memory_limit: ' . ini_get('memory_limit') . PHP_EOL;"
```

Vous devriez voir :
```
upload_max_filesize: 20M
post_max_size: 30M
memory_limit: 512M
```

### Étape 5 : Redémarrer le serveur

```bash
php artisan serve
```

### Étape 6 : Tester

Allez sur : http://127.0.0.1:8000/test-limits.php

Vous devriez voir **✅ Configuration correcte !** en vert.

---

## 🧪 Test final

Une fois la configuration correcte :

1. ✅ Allez dans l'administration : http://127.0.0.1:8000/admin/banners
2. ✅ Modifiez une bannière avec 2 grandes images (desktop + mobile)
3. ✅ Cliquez sur "Mettre à jour"
4. ✅ **Plus d'erreur PostTooLargeException !**

---

## 🗑️ Nettoyage après test

Une fois que tout fonctionne, supprimez le fichier de test :

```bash
rm public/test-limits.php
```

---

## 📚 Pourquoi les options `-d` ne fonctionnent pas ?

Sur certaines installations PHP (notamment via Homebrew sur Mac), les directives `upload_max_filesize` et `post_max_size` sont définies en mode `PHP_INI_PERDIR`, ce qui signifie qu'elles **ne peuvent pas être modifiées** via :
- ❌ Options en ligne de commande `-d`
- ❌ `ini_set()` dans le code PHP
- ❌ `.user.ini` (seulement pour Apache/Nginx avec CGI)

La **seule méthode** qui fonctionne est de modifier le fichier `php.ini` principal.

---

## ✅ Récapitulatif

| Environnement | Méthode | Fichier |
|--------------|---------|---------|
| **Local (Mac)** | Modifier `php.ini` | `/usr/local/etc/php/8.4/php.ini` |
| **Production (O2Switch)** | Modifier `.htaccess` | `public/.htaccess` |

C'est une **configuration unique** à faire sur votre Mac. Une fois fait, ça reste permanent !

