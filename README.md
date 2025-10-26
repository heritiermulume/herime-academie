# Herime Academie - Plateforme d'Apprentissage en Ligne

## 🎯 Description du Projet

Herime Academie est une plateforme d'apprentissage en ligne complète inspirée d'Udemy, conçue pour offrir une expérience d'apprentissage moderne et professionnelle. La plateforme intègre toutes les fonctionnalités essentielles d'un site e-learning, e-commerce et marketing.

## ✨ Fonctionnalités Principales

### 🏠 Page d'Accueil Dynamique
- **Section Hero** avec slider automatique et call-to-action
- **Affichage des cours** par catégories (populaires, best-sellers, recommandés, tendances)
- **Section Annonces** avec boutons d'action personnalisables
- **Témoignages d'étudiants** avec photos et notes
- **Logos des partenaires** officiels
- **Sections marketing** personnalisées
- **Statistiques** en temps réel

### 🎓 Système de Cours Complet
- **Navigation par catégories, filtres et tags**
- **Page de détail** avec aperçu vidéo, description, programme complet
- **Profil du formateur** avec bio et liens sociaux
- **Système d'avis et évaluations**
- **Suivi de progression** pour les étudiants
- **Génération de certificats** PDF
- **Messagerie** entre étudiant et formateur

### 👨‍🏫 Espace Formateur
- **Création et gestion** des cours (vidéos, quiz, PDF)
- **Suivi des ventes** et revenus
- **Analytics détaillées** avec graphiques
- **Gestion des étudiants** et de leur progression
- **Système de coupons** et promotions

### 👨‍💼 Back-Office Admin
- **Gestion complète** des utilisateurs, formateurs, cours
- **Tableaux de bord** avec statistiques avancées
- **Gestion des annonces** et partenaires
- **Système de retraits** pour les formateurs
- **Analytics** globales de la plateforme

### 💸 Système de Paiement
- **Intégration Stripe, PayPal, Mobile Money**
- **Modèle freemium** + ventes individuelles
- **Système d'abonnement** mensuel
- **Partage des revenus** avec les formateurs

### 🤝 Système d'Affiliation
- **Espace info-preneurs** avec codes promo
- **Commissions** sur les ventes
- **Tableaux de bord** dédiés
- **Suivi des performances**

## 🛠️ Technologies Utilisées

### Backend
- **Laravel 12** (dernière version stable)
- **MySQL** pour la base de données
- **Laravel Breeze** pour l'authentification
- **Laravel Cashier** pour les paiements
- **Spatie Laravel Permission** pour les rôles

### Frontend
- **HTML5, CSS3, Bootstrap 5**
- **JavaScript Vanilla**
- **Font Awesome** pour les icônes
- **AOS** pour les animations
- **Design responsive** et moderne

### Intégrations
- **Stripe** pour les paiements
- **PayPal** pour les paiements
- **Mobile Money** (Orange Money, M-Pesa)
- **Intervention Image** pour le traitement d'images
- **Pusher** pour les notifications temps réel

## 🎨 Identité Visuelle

- **Couleur principale** : Bleu foncé (#003366)
- **Couleur secondaire** : Orange clair (#ffcc33)
- **Polices** : Inter, Poppins
- **Style** : Professionnel, épuré, dynamique avec animations modernes

## 📊 Structure de la Base de Données

### Tables Principales
- `users` - Utilisateurs (étudiants, formateurs, admins, affiliés)
- `categories` - Catégories de cours
- `courses` - Cours avec métadonnées complètes
- `course_sections` - Sections des cours
- `course_lessons` - Leçons individuelles
- `enrollments` - Inscriptions des étudiants
- `orders` - Commandes et factures
- `payments` - Paiements
- `coupons` - Codes de réduction
- `affiliates` - Système d'affiliation
- `certificates` - Certificats de fin de cours
- `reviews` - Avis et évaluations
- `messages` - Messagerie interne
- `announcements` - Annonces de la plateforme
- `partners` - Partenaires officiels
- `testimonials` - Témoignages d'étudiants

## 🚀 Installation

1. **Cloner le projet**
```bash
git clone https://github.com/heritiermulume/herime-academie.git
cd herime-academie
```

2. **Installer les dépendances**
```bash
composer install
npm install
```

3. **Configuration de l'environnement**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Configuration de la base de données**
```bash
# Configurer les variables DB_* dans .env
php artisan migrate
php artisan db:seed
```

5. **Lien de stockage**
```bash
php artisan storage:link
```

6. **Démarrer le serveur**
```bash
php artisan serve
```

## 🌐 Déploiement sur O2Switch

Pour héberger ce site sur O2Switch, consultez le guide complet :

📄 **[Guide d'hébergement O2Switch](DEPLOY_O2SWITCH.md)**

Le guide inclut :
- Configuration du serveur
- Upload des fichiers
- Configuration de la base de données
- Variables d'environnement
- Optimisation de performance
- Checklist de déploiement

## 👥 Rôles Utilisateurs

### 👨‍🎓 Étudiant
- Parcourir et acheter des cours
- Suivre les cours avec progression
- Obtenir des certificats
- Communiquer avec les formateurs
- Gérer son profil

### 👨‍🏫 Formateur
- Créer et gérer ses cours
- Suivre ses étudiants
- Analyser ses performances
- Gérer ses revenus
- Communiquer avec les étudiants

### 👨‍💼 Admin
- Gérer tous les utilisateurs
- Modérer les cours
- Gérer les paiements
- Configurer la plateforme
- Analyser les performances globales

### 🤝 Affilié/Info-preneur
- Promouvoir les cours
- Générer des codes promo
- Suivre ses commissions
- Analyser ses performances

## 🔒 Fonctionnalités de Sécurité

- **Anti-téléchargement** des vidéos
- **Protection contre les captures d'écran**
- **Authentification sécurisée** avec Laravel Breeze
- **Validation des données** côté serveur
- **Protection CSRF**
- **Gestion des rôles** et permissions

## 📱 Responsive Design

La plateforme est entièrement responsive et optimisée pour :
- **Desktop** (1200px+)
- **Tablette** (768px - 1199px)
- **Mobile** (320px - 767px)

## 🌐 Contact et Support

- **Téléphone** : +243 824 449 218
- **Adresse** : 25, Croisement Gambela et Lukandu, Commune de Kasavubu, Kinshasa, RDC
- **LinkedIn** : https://www.linkedin.com/company/herime1
- **Instagram** : https://www.instagram.com/herime_1
- **Facebook** : https://www.facebook.com/herime1
- **TikTok** : https://www.tiktok.com/@herime_1
- **YouTube** : https://www.youtube.com/@herime_1
- **WhatsApp** : https://whatsapp.com/channel/0029VaU6teH3mFYCdZPjoT0h

## 📈 Roadmap

### Phase 1 ✅ (Terminée)
- [x] Configuration Laravel et base de données
- [x] Système d'authentification multi-rôles
- [x] Interface utilisateur responsive
- [x] Système de gestion des cours
- [x] Pages publiques (accueil, cours, formateurs)

### Phase 2 🔄 (En cours)
- [ ] Intégration des paiements (Stripe, PayPal, Mobile Money)
- [ ] Espace étudiant complet
- [ ] Espace formateur avec analytics
- [ ] Back-office admin

### Phase 3 📋 (À venir)
- [ ] Système d'affiliation
- [ ] Fonctionnalités de sécurité avancées
- [ ] Messagerie et notifications
- [ ] Blog et ressources
- [ ] API mobile
- [ ] Tests automatisés

## 🤝 Contribution

Les contributions sont les bienvenues ! Pour contribuer :

1. Fork le projet
2. Créer une branche feature (`git checkout -b feature/AmazingFeature`)
3. Commit vos changements (`git commit -m 'Add some AmazingFeature'`)
4. Push vers la branche (`git push origin feature/AmazingFeature`)
5. Ouvrir une Pull Request

## 📄 Licence

Ce projet est sous licence MIT. Voir le fichier `LICENSE` pour plus de détails.

---

**Herime Academie** - Transformez votre apprentissage, transformez votre avenir ! 🚀