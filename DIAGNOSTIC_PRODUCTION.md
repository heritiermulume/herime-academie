# Scripts de Diagnostic pour Erreur 500 en Production

## Fichiers créés

1. **diagnose-500-error.php** - Script de diagnostic complet
2. **test-production-500.php** - Script de test ciblé pour identifier l'erreur exacte
3. **run-production-diagnostic.sh** - Script bash pour exécuter tous les diagnostics

## Utilisation en production

### Option 1: Script bash (recommandé)

```bash
./run-production-diagnostic.sh
```

Ce script va:
- Vider tous les caches Laravel
- Exécuter le diagnostic complet
- Exécuter les tests de production
- Vérifier les permissions
- Générer des fichiers de résultats

### Option 2: Scripts individuels

#### Diagnostic complet
```bash
php diagnose-500-error.php > diagnostic-result.txt 2>&1
```

#### Test ciblé
```bash
php test-production-500.php > test-production-result.txt 2>&1
```

## Résultats

Les résultats seront sauvegardés dans:
- `diagnostic-result.txt` - Résultat du diagnostic complet
- `test-production-result.txt` - Résultat des tests ciblés

## Corrections apportées

1. **SSOController** - Correction du rôle "super_user" qui n'existe pas dans l'enum
   - Le rôle "super_user" est maintenant automatiquement mappé vers "admin"
   - Validation stricte des rôles avant création/mise à jour

2. **Scripts de diagnostic** - Scripts pour identifier rapidement les erreurs 500

## Instructions pour la production

1. Transférez les fichiers suivants sur le serveur:
   - `diagnose-500-error.php`
   - `test-production-500.php`
   - `run-production-diagnostic.sh`

2. Rendez le script bash exécutable:
   ```bash
   chmod +x run-production-diagnostic.sh
   ```

3. Exécutez le diagnostic:
   ```bash
   ./run-production-diagnostic.sh
   ```

4. Consultez les fichiers de résultats pour identifier l'erreur exacte

5. Si l'erreur persiste, vérifiez:
   - Les logs en temps réel: `tail -f storage/logs/laravel.log`
   - Les logs du serveur web (Apache/Nginx)
   - Que toutes les migrations sont exécutées: `php artisan migrate:status`
   - Les permissions: `chmod -R 775 storage bootstrap/cache`

## Notes importantes

- Les scripts n'ont pas besoin de permissions spéciales
- Ils peuvent être exécutés depuis n'importe quel répertoire du projet
- Les résultats sont sauvegardés dans des fichiers texte pour analyse ultérieure
- Les scripts sont sûrs à exécuter en production (lecture seule, pas de modifications)







