# Implémentation du Carousel de Bannières Dynamiques

## Vue d'ensemble
Ce document décrit l'implémentation complète d'un système de bannières dynamiques avec carousel automatique pour la page d'accueil, avec support du format 16:9 sur mobile.

## Fonctionnalités implémentées

### 1. Base de données
- **Migration**: `2025_10_28_085534_create_banners_table.php`
- **Table**: `banners`
- **Champs**:
  - `id`: Identifiant unique
  - `title`: Titre de la bannière
  - `subtitle`: Sous-titre (optionnel)
  - `image`: Image principale
  - `mobile_image`: Image optimisée pour mobile en 16:9
  - `button1_text`, `button1_url`, `button1_style`: Premier bouton d'action
  - `button2_text`, `button2_url`, `button2_style`: Deuxième bouton d'action
  - `sort_order`: Ordre d'affichage
  - `is_active`: Statut actif/inactif
  - `timestamps`: Dates de création et mise à jour

### 2. Modèle et Contrôleurs

#### Modèle Banner (`app/Models/Banner.php`)
- Gestion des bannières avec scopes `active()` et `ordered()`
- Casting des types de données

#### Contrôleur Admin (`app/Http/Controllers/Admin/BannerController.php`)
- CRUD complet pour les bannières
- Upload et gestion des images (principale et mobile)
- Toggle du statut actif/inactif via AJAX
- Validation des données
- Suppression automatique des images lors de la suppression d'une bannière

#### HomeController
- Récupération des bannières actives triées par ordre
- Passage des données à la vue home

### 3. Routes
Routes ajoutées dans `routes/web.php`:
```php
Route::resource('banners', BannerController::class);
Route::post('/banners/{banner}/toggle-active', [BannerController::class, 'toggleActive'])
    ->name('banners.toggle-active');
```

### 4. Vues d'administration

#### Index (`resources/views/admin/banners/index.blade.php`)
- Liste des bannières avec preview
- Toggle du statut actif/inactif
- Actions: Modifier, Supprimer
- Pagination

#### Création (`resources/views/admin/banners/create.blade.php`)
- Formulaire complet pour créer une bannière
- Upload d'image avec preview en temps réel
- Configuration des deux boutons d'action
- Gestion de l'ordre d'affichage

#### Édition (`resources/views/admin/banners/edit.blade.php`)
- Formulaire de modification
- Affichage des images actuelles
- Preview des nouvelles images sélectionnées

### 5. Page d'accueil (`resources/views/home.blade.php`)

#### HTML
- Carousel dynamique avec slides multiples
- Navigation par flèches et dots
- Support des images responsive (picture element pour mobile)
- Fallback si aucune bannière n'existe
- Boutons d'action configurables

#### JavaScript
- Auto-défilement toutes les 5 secondes
- Navigation au clavier (flèches gauche/droite)
- Support du swipe sur mobile
- Pause au survol de la souris
- Transitions fluides entre les slides

#### CSS
- Design moderne et responsive
- Format 16:9 automatique sur mobile (padding-bottom: 56.25%)
- Overlay avec gradient pour une meilleure lisibilité
- Animations et transitions fluides
- Navigation optimisée pour mobile (boutons plus petits, dots adaptés)

### 6. Seeder
Le seeder `BannerSeeder.php` crée 3 bannières d'exemple avec:
- Titres et sous-titres différents
- Boutons d'action configurés
- Ordre défini
- Toutes actives par défaut

## Format mobile 16:9

Sur mobile (max-width: 767px), la bannière utilise un format 16:9 pour afficher les images en Full HD:
```css
.hero-container {
    position: relative;
    width: 100%;
    height: 0;
    padding-bottom: 56.25%; /* 16:9 ratio */
    min-height: 0;
}
```

Cette technique utilise le padding-bottom pour maintenir le ratio d'aspect 16:9, garantissant que les images s'affichent correctement en Full HD sur tous les appareils mobiles.

## Utilisation

### Accéder à l'administration des bannières
1. Se connecter en tant qu'administrateur
2. Aller sur `/admin/banners`
3. Créer, modifier ou supprimer des bannières

### Créer une bannière
1. Cliquer sur "Nouvelle bannière"
2. Remplir le formulaire:
   - Titre (obligatoire)
   - Sous-titre (optionnel)
   - Image principale 1920x1080px (obligatoire)
   - Image mobile 16:9 1920x1080px (optionnel)
   - Configurer les boutons d'action
   - Définir l'ordre d'affichage
3. Activer/désactiver la bannière
4. Enregistrer

### Format recommandé des images
- **Image principale**: 1920x1080px (Full HD, 16:9)
- **Image mobile**: 1920x1080px (Full HD, 16:9)
- **Formats supportés**: JPEG, PNG, WebP
- **Taille maximale**: 5MB

## Caractéristiques techniques

### Performance
- Lazy loading des images (sauf la première)
- Optimisation du carousel (pas de carousel pour une seule slide)
- Transitions CSS optimisées
- Support du format picture pour responsive images

### Accessibilité
- Labels ARIA pour la navigation
- Support du clavier
- Alt text sur les images
- Contraste optimisé avec overlays

### Sécurité
- Validation des uploads
- Protection CSRF
- Nettoyage automatique des anciennes images
- Vérification des droits d'administration

## Fichiers modifiés/créés

### Nouveaux fichiers
- `app/Models/Banner.php`
- `app/Http/Controllers/Admin/BannerController.php`
- `database/migrations/2025_10_28_085534_create_banners_table.php`
- `database/seeders/BannerSeeder.php`
- `resources/views/admin/banners/index.blade.php`
- `resources/views/admin/banners/create.blade.php`
- `resources/views/admin/banners/edit.blade.php`

### Fichiers modifiés
- `app/Http/Controllers/HomeController.php`
- `resources/views/home.blade.php`
- `routes/web.php`

## Prochaines étapes possibles
1. Ajouter un système de planification (date de début/fin)
2. Implémenter des analytics (clics sur les boutons)
3. Ajouter un éditeur WYSIWYG pour le texte
4. Support de vidéos en arrière-plan
5. A/B testing des bannières
6. Export/import de bannières

## Support
Pour toute question ou problème, consultez la documentation Laravel ou contactez l'équipe de développement.

