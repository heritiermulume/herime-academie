# Connecter votre environnement local à la base O2Switch

## 🎯 Objectif

Travailler en local tout en utilisant la base de données du serveur O2Switch.

## 📝 Étape par étape

### 1. Récupérer les informations de connexion

Dans votre **interface O2Switch** :

1. Allez dans **"Bases de données"** ou **"phpMyAdmin"**
2. Notez ces informations :
   - **Nom de la base** : `muhe3594_herime_academie` (ou celui que vous avez créé)
   - **Nom d'utilisateur SQL** : (votre identifiant SQL)
   - **Mot de passe SQL** : (votre mot de passe)
   - **Hôte** : Généralement `localhost` (sur le serveur) OU `persil.o2switch.net` (à distance)

### 2. Configurer O2Switch pour les connexions distantes (SI NÉCESSAIRE)

**Important :** Par défaut, MySQL sur O2Switch accepte seulement les connexions locales.

**Option A : Connexions distantes autorisées (déjà configuré sur certains serveurs)**

Si O2Switch autorise les connexions distantes, utilisez :
- **Hôte** : `persil.o2switch.net` ou l'IP du serveur
- **Port** : `3306`

**Option B : Connexions locales uniquement (recommandé pour O2Switch)**

O2Switch accepte généralement seulement les connexions depuis le serveur lui-même. Dans ce cas :
- Utilisez un **tunnel SSH** pour vous connecter

## 🔧 Configuration du .env local

### Créer un backup

```bash
# Sur votre Mac
cd /Users/heritiermulume/Autres/Herime/Projets/Web/herime-academie
cp .env .env.local.backup
```

### Modifier le .env

```bash
nano .env
```

Changez ces lignes :

```env
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# DATABASE CONFIGURATION - O2Switch
DB_CONNECTION=mysql
DB_HOST=localhost  # Ou l'adresse distante si autorisée
DB_PORT=3306
DB_DATABASE=muhe3594_herime_academie  # Votre base O2Switch
DB_USERNAME=votre_username_sql
DB_PASSWORD=votre_password_sql
```

### Option 1 : Connexion via tunnel SSH (RECOMMANDÉ)

Si O2Switch n'accepte que les connexions locales, utilisez un tunnel SSH :

```bash
# Dans un terminal séparé, créez le tunnel
ssh -L 3306:localhost:3306 votre-compte@persil.o2switch.net

# Puis dans un autre terminal, utilisez :
DB_HOST=127.0.0.1  # Via le tunnel SSH
DB_PORT=3306
```

### Option 2 : Connexion directe (si autorisée par O2Switch)

Si O2Switch autorise les connexions distantes :

```env
DB_HOST=persil.o2switch.net  # Ou l'IP du serveur
DB_PORT=3306
```

## ✅ Tester la connexion

```bash
# Vider le cache
php artisan config:clear

# Tester la connexion
php artisan migrate:status

# Vous devriez voir les tables de votre base O2Switch !
```

## 📊 Commandes utiles

### Voir les tables distantes

```bash
php artisan tinker
>>> DB::table('users')->count()
>>> DB::getDoctrineSchemaManager()->listTableNames()
```

### Synchroniser les schémas

```bash
# Voir quelles migrations ont été exécutées
php artisan migrate:status

# Appliquer de nouvelles migrations
php artisan migrate
```

### Basculement rapide entre local et distant

Créez deux fichiers .env :

**`.env.local`** (base locale) :
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=herime_academie_local
```

**`.env.remote`** (base O2Switch) :
```env
DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=muhe3594_herime_academie
```

Pour basculer :
```bash
# Utiliser la base locale
cp .env.local .env
php artisan config:clear

# Utiliser la base distante
cp .env.remote .env
php artisan config:clear
```

## 🚨 Important

### Sécurité

- **Ne commitez JAMAIS** votre fichier `.env` avec les credentials de production
- **Utilisez des identifiants différents** pour local et production
- **En local, gardez `APP_DEBUG=true`** mais **PAS en production**

### Limitations

- **Pas de modifications de structure** en production depuis local (risque de corrompre les données)
- **Utilisez des requêtes SELECT uniquement** en développement
- **Pour les migrations**, faites-les directement sur le serveur

## 🔄 Workflow recommandé

1. **Développement local** : Utilisez une base locale
2. **Tests** : Importez des données de prod en local pour tester
3. **Déploiement** : Faites les migrations sur le serveur
4. **Debugging en prod** : Utilisez un tunnel SSH pour inspecter (en lecture seule)

## 📋 Checklist

- [ ] Backup du .env local créé
- [ ] Informations de connexion O2Switch notées
- [ ] .env modifié avec les bonnes informations
- [ ] Tunn

el SSH créé (si nécessaire)
- [ ] Connexion testée (`php artisan migrate:status`)
- [ ] Vérification que vous voyez les données distantes

