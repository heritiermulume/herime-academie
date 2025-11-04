# Commandes pour recréer la base de données

## 🔄 Recréer toutes les migrations et seeders

### Commande complète (recommandée)
```bash
php artisan migrate:fresh --seed
```

### Étape par étape
```bash
# 1. Supprimer toutes les tables et réexécuter les migrations
php artisan migrate:fresh

# 2. Exécuter tous les seeders
php artisan db:seed
```

## 🔁 Réinitialiser sans tout supprimer
```bash
php artisan migrate:refresh --seed
```

## 📋 Seeders disponibles dans le projet

1. **UserSeeder** - Crée les utilisateurs (admin, formateurs, étudiants, affiliés)
2. **CategorySeeder** - Crée les catégories de cours
3. **CourseSeeder** - Crée les cours avec leurs leçons
4. **BlogCategorySeeder** - Crée les catégories de blog
5. **BlogPostSeeder** - Crée les articles de blog
6. **NewsletterSubscriberSeeder** - Crée des abonnés newsletter
7. **AnnouncementSeeder** - Crée les annonces
8. **PartnerSeeder** - Crée les partenaires
9. **TestimonialSeeder** - Crée les témoignages
10. **NotificationSeeder** - Crée les notifications
11. **BannerSeeder** - Crée les bannières

## ⚠️ Attention

⚠️ **`migrate:fresh` supprime TOUTES les données de la base !**

Assurez-vous d'avoir fait une sauvegarde si vous avez des données importantes.

## 🔍 Vérifier les migrations

```bash
# Voir le statut des migrations
php artisan migrate:status

# Voir les migrations en attente
php artisan migrate
```

## 💾 Sauvegarde avant reset

```bash
# Exporter la base de données (MySQL/MariaDB)
mysqldump -u username -p database_name > backup.sql

# Ou avec Laravel
php artisan db:backup
```


