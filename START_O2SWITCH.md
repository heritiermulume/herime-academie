# 🚀 Démarrage du site sur O2Switch

## ✅ Étapes de finalisation

### 1️⃣ Configuration du fichier .env

```bash
# Se connecter en SSH
ssh votre-compte@votre-serveur.o2switch.net
cd ~/herime-academie

# Éditer le fichier .env
nano .env
```

**Configurez ces lignes importantes :**

```env
APP_NAME="Herime Académie"
APP_ENV=production
APP_KEY=  # Sera généré automatiquement
APP_DEBUG=false
APP_URL=https://academie.herime.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=herime_academie  # REMPLACEZ PAR VOTRE BASE
DB_USERNAME=votre_username    # REMPLACEZ
DB_PASSWORD=votre_password   # REMPLACEZ

# Mail O2Switch
MAIL_MAILER=smtp
MAIL_HOST=smtp.o2switch.net
MAIL_PORT=587
MAIL_USERNAME=votre-email@herime.com
MAIL_PASSWORD=votre-password-email
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="contact@herime.com"
MAIL_FROM_NAME="${APP_NAME}"
```

**Sauvegarder avec :** `Ctrl + O`, puis `Entrée`, puis `Ctrl + X`

### 2️⃣ Créer la base de données dans phpMyAdmin

1. **Connectez-vous à phpMyAdmin O2Switch**
2. **Cliquez sur "Nouvelle base de données"**
3. **Nom :** `herime_academie`
4. **Encodage :** `utf8mb4_unicode_ci`
5. **Cliquez sur "Créer"**

### 3️⃣ Créer un utilisateur SQL (si nécessaire)

1. **Onglet "Utilisateurs" dans phpMyAdmin**
2. **Ajouter un nouvel utilisateur**
3. **Nom d'utilisateur :** choisi par vous
4. **Mot de passe :** fort et sécurisé
5. **Attribuer tous les privilèges à la base `herime_academie`**

### 4️⃣ Créer les tables dans la base de données

```bash
# Toujours dans votre dossier herime-academie
php artisan migrate --force

# Vous devriez voir les tables créées dans phpMyAdmin
```

### 5️⃣ (Optionnel) Charger les données de base

```bash
php artisan db:seed
```

Cette commande charge :
- Des catégories d'exemple
- Des cours de démonstration
- Des utilisateurs de test

### 6️⃣ Configurer le dossier racine dans O2Switch

**Important :** Le domaine doit pointer vers le dossier `public/`

**Dans votre interface O2Switch :**

1. **Gestion du domaine** > **academie.herime.com**
2. **Répertoire racine** → Modifier
3. **Chemin :** `/home/votre-compte/herime-academie/public`
4. **Enregistrer**

### 7️⃣ Configurer les permissions

```bash
# Dans le dossier herime-academie
chmod -R 755 storage bootstrap/cache
chmod -R 644 .env
chmod -R 755 public
```

### 8️⃣ Tester le site

1. **Ouvrez votre navigateur**
2. **Allez sur :** https://academie.herime.com
3. **Le site devrait s'afficher ! 🎉**

## 🔍 Vérification et dépannage

### Si vous voyez une erreur 500

```bash
# Vérifier les logs
tail -f storage/logs/laravel.log

# Vérifier les permissions
ls -la storage/
ls -la bootstrap/cache/

# Nettoyer le cache
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Re-générer le cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Si le CSS/JS ne se charge pas

```bash
# Vérifier le lien symbolique
ls -la public/storage

# Si absent
php artisan storage:link

# Vérifier les permissions de public/
chmod -R 755 public/
```

### Si la base de données ne se connecte pas

1. **Vérifiez vos identifiants dans `.env`**
2. **Testez la connexion dans phpMyAdmin**
3. **Vérifiez que la base existe**
4. **Vérifiez que l'utilisateur a tous les privilèges**

## ✅ Checklist finale

- [ ] Fichier `.env` configuré avec les bonnes informations
- [ ] Base de données créée dans phpMyAdmin
- [ ] Tables créées (`php artisan migrate`)
- [ ] Dossier racine pointant vers `/public`
- [ ] Permissions configurées (755 pour dossiers, 644 pour fichiers)
- [ ] Lien de stockage créé (`php artisan storage:link`)
- [ ] Cache configuré (`config:cache`, `route:cache`)
- [ ] Site accessible sur https://academie.herime.com

## 🎊 Votre site est maintenant en ligne !

**Prochaines étapes :**

1. **Créer un compte administrateur** pour gérer le site
2. **Ajouter vos premiers cours** depuis l'interface admin
3. **Tester les paiements** avec les méthodes de paiement configurées

---

**Besoin d'aide ?**
- Logs : `storage/logs/laravel.log`
- Support O2Switch : https://www.o2switch.fr/support/

