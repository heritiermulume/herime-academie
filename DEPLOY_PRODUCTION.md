# Instructions de déploiement en production

## Migrations à exécuter

Deux nouvelles migrations doivent être exécutées sur le serveur de production :

### 1. Migration `2025_11_06_000001_add_super_user_role_to_users_table.php`
**Action :** Ajoute le rôle `super_user` à l'enum `role` de la table `users`

### 2. Migration `2025_11_06_063642_create_instructor_applications_table.php`
**Action :** Crée la table `instructor_applications` pour gérer les candidatures formateur

## Commandes à exécuter sur le serveur

```bash
# 1. Se connecter au serveur
ssh votre-utilisateur@votre-serveur

# 2. Aller dans le répertoire du projet
cd /home/muhe3594/herime-academie

# 3. Récupérer les dernières modifications
git pull origin main

# 4. Exécuter les migrations
php artisan migrate --force

# 5. Vérifier que les migrations ont été exécutées
php artisan migrate:status | grep -E "(instructor_applications|super_user)"
```

## Vérification

Après l'exécution des migrations, vérifiez que :

1. **La table `instructor_applications` existe :**
   ```bash
   php artisan tinker
   >>> Schema::hasTable('instructor_applications')
   => true
   ```

2. **Le rôle `super_user` est disponible :**
   ```bash
   php artisan tinker
   >>> DB::select("SHOW COLUMNS FROM users WHERE Field = 'role'")
   # Vérifiez que 'super_user' est dans la liste des valeurs enum
   ```

## Notes importantes

- ⚠️ **Utilisez `--force`** en production pour éviter les confirmations interactives
- ✅ Les migrations sont **non-destructives** (elles ajoutent seulement des fonctionnalités)
- ✅ Aucun seeder n'est nécessaire pour cette fonctionnalité
- ✅ Les données existantes ne seront pas affectées

## En cas de problème

Si une migration échoue :

1. Vérifiez les logs : `tail -f storage/logs/laravel.log`
2. Vérifiez les permissions : `chmod -R 775 storage bootstrap/cache`
3. Vérifiez la connexion à la base de données dans `.env`
4. Contactez le support si nécessaire

