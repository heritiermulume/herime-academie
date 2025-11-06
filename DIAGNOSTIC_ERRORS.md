# Guide de Diagnostic des Erreurs 500

Ce guide explique comment utiliser les scripts de diagnostic pour identifier et résoudre les erreurs 500.

## Scripts Disponibles

### 1. `diagnose-500-precise.sh` - Diagnostic Complet
Script de diagnostic complet qui vérifie tous les aspects de l'application.

**Utilisation:**
```bash
./diagnose-500-precise.sh
```

**Ce qu'il fait:**
- Affiche les dernières erreurs dans les logs Laravel
- Vérifie les permissions des fichiers
- Teste la configuration SSO
- Teste les routes problématiques
- Vérifie les erreurs PHP
- Teste la validation du token SSO
- Affiche un résumé des erreurs

### 2. `show-errors.sh` - Afficher les Erreurs Récentes
Script simple pour afficher rapidement les erreurs récentes.

**Utilisation:**
```bash
./show-errors.sh
```

**Ce qu'il fait:**
- Affiche les 50 dernières lignes d'erreur
- Affiche la dernière erreur complète
- Fournit la commande pour surveiller en temps réel

### 3. `watch-errors.sh` - Surveiller les Erreurs en Temps Réel
Script pour surveiller les erreurs au fur et à mesure qu'elles se produisent.

**Utilisation:**
```bash
./watch-errors.sh
```

**Ce qu'il fait:**
- Surveille le fichier de log en temps réel
- Affiche les erreurs avec coloration
- S'arrête avec Ctrl+C

### 4. `extract-500-errors.sh` - Extraire les Erreurs 500
Script pour extraire spécifiquement les erreurs 500 dans un fichier.

**Utilisation:**
```bash
./extract-500-errors.sh
```

**Ce qu'il fait:**
- Extrait toutes les erreurs 500 avec contexte
- Sauvegarde dans un fichier avec horodatage
- Affiche un résumé des types d'erreurs

## Utilisation Recommandée

### Étape 1: Diagnostic Rapide
```bash
./show-errors.sh
```

### Étape 2: Diagnostic Complet
```bash
./diagnose-500-precise.sh > diagnostic-output.txt 2>&1
```

### Étape 3: Extraire les Erreurs 500
```bash
./extract-500-errors.sh
```

### Étape 4: Surveiller en Temps Réel
Dans un terminal séparé, reproduisez l'erreur pendant que vous surveillez:
```bash
./watch-errors.sh
```

## Commandes Utiles

### Voir les dernières erreurs
```bash
tail -n 100 storage/logs/laravel.log | grep -A 20 ERROR
```

### Surveiller les erreurs en temps réel
```bash
tail -f storage/logs/laravel.log | grep -A 20 ERROR
```

### Vider les logs (après diagnostic)
```bash
> storage/logs/laravel.log
```

### Vérifier les permissions
```bash
ls -la storage/logs/
ls -la bootstrap/cache/
ls -la storage/framework/
```

### Vérifier la configuration
```bash
php artisan config:show services.sso
```

### Tester une route spécifique
```bash
php artisan route:list | grep -E "me|logout"
```

## Résolution des Erreurs Communes

### Erreur: "Vite manifest not found"
```bash
npm run build
```

### Erreur: "Route not found"
```bash
php artisan route:clear
php artisan route:cache
```

### Erreur: "Class not found"
```bash
composer dump-autoload
php artisan config:clear
php artisan cache:clear
```

### Erreur: "Permission denied"
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Erreur: "Session not started"
```bash
php artisan config:clear
php artisan cache:clear
```

## Informations à Fournir lors d'un Rapport d'Erreur

Lorsque vous signalez une erreur 500, fournissez:

1. **Sortie du diagnostic complet:**
   ```bash
   ./diagnose-500-precise.sh > diagnostic.txt 2>&1
   ```

2. **Fichier d'erreurs extraites:**
   ```bash
   ./extract-500-errors.sh
   # Ensuite, partagez le fichier errors-500-*.txt généré
   ```

3. **Dernières lignes du log:**
   ```bash
   tail -n 200 storage/logs/laravel.log
   ```

4. **Configuration SSO:**
   ```bash
   php artisan tinker --execute="echo config('services.sso.enabled') ? 'Enabled' : 'Disabled';"
   ```

5. **Routes problématiques:**
   ```bash
   php artisan route:list | grep -E "me|logout"
   ```

## Support

Si les erreurs persistent après avoir suivi ce guide, partagez:
- La sortie complète de `diagnose-500-precise.sh`
- Le fichier généré par `extract-500-errors.sh`
- Les étapes pour reproduire l'erreur

