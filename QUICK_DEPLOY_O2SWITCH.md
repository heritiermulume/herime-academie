# ⚡ Guide de déploiement rapide sur o2switch

## 🎯 Changements apportés

✅ **Les images de bannières sont maintenant stockées en base de données (base64)**
- Plus besoin de synchroniser les fichiers d'images
- Simplification du déploiement
- Backup inclus dans les exports SQL

## 📋 Commandes à exécuter sur o2switch

### 1️⃣ Connexion et pull

```bash
# Se connecter via SSH ou Terminal cPanel
ssh votre-user@votre-domaine.com

# Aller dans le dossier du projet
cd ~/www/herime-academie  # Ajustez selon votre configuration

# Pull des modifications
git pull origin main
```

### 2️⃣ Installation des dépendances

```bash
composer install --no-dev --optimize-autoloader
```

### 3️⃣ Nettoyage du cache

```bash
php artisan optimize:clear
```

### 4️⃣ Exécution de la migration

```bash
php artisan migrate --force
```

### 5️⃣ Conversion des bannières existantes

```bash
# Cette commande convertit automatiquement vos bannières existantes
php artisan banners:convert-to-base64
```

### 6️⃣ Re-cache pour production

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 7️⃣ Réinitialisation OPcache (Important!)

```bash
# Créer le fichier reset-opcache.php
cat > public/reset-opcache.php << 'EOF'
<?php
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✅ OPcache réinitialisé";
} else {
    echo "❌ OPcache non disponible";
}
?>
EOF

# Ensuite, visitez dans le navigateur:
# https://votre-domaine.com/reset-opcache.php

# Puis supprimez le fichier
rm public/reset-opcache.php
```

### 8️⃣ Vérification

```bash
# Vérifier que les bannières sont bien en base64
php artisan tinker
>>> $banner = \App\Models\Banner::first();
>>> echo "Taille: " . strlen($banner->image);  # Devrait être > 1000
>>> echo "\nDébut: " . substr($banner->image, 0, 25);  # Devrait afficher "data:image/jpeg;base64..."
>>> exit
```

## ✅ Checklist de vérification

Après déploiement, vérifiez:

- [ ] Le site s'affiche sans erreur 500
- [ ] Les bannières sont visibles sur la page d'accueil
- [ ] Le carousel fonctionne (défilement automatique)
- [ ] Les bannières sont visibles sur mobile
- [ ] L'admin des bannières fonctionne: `/admin/banners`
- [ ] Vous pouvez créer une nouvelle bannière avec image
- [ ] Les images s'affichent correctement dans l'admin

## 🐛 Problèmes courants

### Les bannières ne s'affichent pas

```bash
# Vérifier si la conversion a fonctionné
php artisan tinker
>>> \App\Models\Banner::first()->image
>>> exit

# Si c'est toujours un chemin de fichier, reconvertir
php artisan banners:convert-to-base64
php artisan optimize:clear
```

### Erreur "Data too long for column"

```bash
# La migration n'a pas été appliquée
php artisan migrate:status
php artisan migrate --force
```

### Les modifications ne s'affichent pas

```bash
# Cache non nettoyé ou OPcache
php artisan optimize:clear

# Puis réinitialiser OPcache via le fichier reset-opcache.php
```

### Erreur 500

```bash
# Vérifier les logs
tail -n 50 storage/logs/laravel.log

# Vérifier les permissions
chmod -R 755 storage bootstrap/cache
chmod -R 777 storage/logs
```

## 📞 Support

Si les problèmes persistent après avoir suivi ce guide:

1. Consultez `O2SWITCH_DEPLOYMENT_BANNERS.md` pour plus de détails
2. Consultez `BANNER_DATABASE_STORAGE.md` pour comprendre les changements techniques
3. Vérifiez les logs: `storage/logs/laravel.log`
4. Contactez le support o2switch si problème serveur

## 🔄 Commande complète (copy-paste)

Vous pouvez copier-coller tout ça d'un coup:

```bash
cd ~/www/herime-academie && \
git pull origin main && \
composer install --no-dev --optimize-autoloader && \
php artisan optimize:clear && \
php artisan migrate --force && \
php artisan banners:convert-to-base64 && \
php artisan config:cache && \
php artisan route:cache && \
php artisan view:cache && \
echo "✅ Déploiement terminé! N'oubliez pas de réinitialiser OPcache."
```

Après cette commande, il ne reste plus qu'à:
1. Visiter `https://votre-domaine.com/reset-opcache.php` (voir étape 7)
2. Supprimer le fichier `reset-opcache.php`
3. Tester le site

## 📊 Résumé technique

**Avant**: 
- Images stockées dans `public/images/hero/`
- Problèmes de synchronisation entre local et production
- Nécessite de transférer les fichiers séparément

**Après**:
- Images en base64 dans la table `banners` (colonne `longText`)
- Tout est dans la base de données
- Un seul `git pull` + migration suffit

---

**Date**: 28 octobre 2025
**Commit**: Stockage des images de bannières en base de données (base64)

