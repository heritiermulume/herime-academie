# 🔍 Guide de Debug - Erreur 500 en Production

## 🎯 Diagnostic Rapide

### 1. Vérifier les logs d'erreur
```bash
# Sur le serveur de production
tail -n 100 storage/logs/laravel.log | grep -A 20 "ERROR\|Exception\|SQLSTATE"
```

### 2. Identifier la route qui échoue
- Ouvrez la console du navigateur (F12)
- Regardez l'onglet Network
- Identifiez quelle requête retourne le code 500
- Notez l'URL exacte qui échoue

### 3. Vérifier les migrations
```bash
php artisan migrate:status
# Si des migrations sont en attente :
php artisan migrate --force
```

### 4. Vérifier les assets Vite
```bash
# Vérifier que le dossier build existe
ls -la public/build/

# Si absent, compiler les assets
npm run build
```

## 🔧 Solutions Courantes

### Problème 1 : Champs manquants dans la base de données

**Symptôme :** `SQLSTATE[42S22]: Column not found`

**Solution :**
```bash
# Exécuter toutes les migrations
php artisan migrate --force

# Vérifier la structure de la table
php artisan tinker
>>> Schema::getColumnListing('courses');
>>> Schema::getColumnListing('users');
```

### Problème 2 : Assets Vite manquants

**Symptôme :** `Failed to load resource: the server responded with a status of 500`

**Solution :**
```bash
# Compiler les assets
npm install
npm run build

# Vérifier que public/build/manifest.json existe
ls -la public/build/manifest.json
```

### Problème 3 : Caches corrompus

**Symptôme :** Erreurs inexpliquées, comportement étrange

**Solution :**
```bash
# Vider tous les caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Recréer les caches optimisés
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### Problème 4 : Permissions incorrectes

**Symptôme :** Erreurs de permission, fichiers non accessibles

**Solution :**
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Problème 5 : Code non déployé

**Symptôme :** L'erreur persiste malgré les corrections

**Solution :**
```bash
# Vérifier que vous êtes sur la bonne branche
git branch

# Pull les dernières modifications
git pull origin main

# Vérifier que les fichiers modifiés sont présents
ls -la app/Http/Controllers/Auth/SSOController.php
ls -la app/Http/Controllers/AdminController.php
```

## 📋 Checklist de Déploiement

### Avant le déploiement
- [ ] Tous les tests passent en local
- [ ] Les migrations sont testées
- [ ] Les assets sont compilés (`npm run build`)
- [ ] Le code est commité et poussé sur GitHub

### Pendant le déploiement
- [ ] Pull les dernières modifications (`git pull`)
- [ ] Installer les dépendances (`composer install --no-dev`)
- [ ] Compiler les assets (`npm run build`)
- [ ] Exécuter les migrations (`php artisan migrate --force`)
- [ ] Vider les caches (`php artisan config:clear`)
- [ ] Recréer les caches (`php artisan config:cache`)

### Après le déploiement
- [ ] Vérifier les logs (`tail -f storage/logs/laravel.log`)
- [ ] Tester la page d'accueil
- [ ] Tester la connexion SSO
- [ ] Tester la création de cours (admin)
- [ ] Vérifier les routes principales

## 🐛 Erreurs Spécifiques

### Erreur SSO : "super_user" role
**Cause :** Le code essaie d'insérer un rôle invalide

**Vérification :**
```bash
# Vérifier que le code corrigé est déployé
grep -n "normalizeRole" app/Http/Controllers/Auth/SSOController.php
```

### Erreur Courses : "duration" ou "lessons_count"
**Cause :** Tentative d'accès à des champs supprimés

**Vérification :**
```bash
# Vérifier que les références ont été supprimées
grep -n "->duration\|->lessons_count" app/Http/Controllers/AdminController.php
# Ne devrait rien retourner (sauf dans les commentaires)
```

## 📞 Support

Si l'erreur persiste après avoir suivi ces étapes :

1. **Copiez les dernières lignes des logs :**
   ```bash
   tail -n 200 storage/logs/laravel.log > error-log.txt
   ```

2. **Notez l'URL exacte qui échoue** (depuis la console du navigateur)

3. **Vérifiez la version du code déployé :**
   ```bash
   git log -1 --oneline
   ```

4. **Vérifiez les variables d'environnement :**
   ```bash
   grep APP_ENV .env
   grep APP_DEBUG .env
   ```

