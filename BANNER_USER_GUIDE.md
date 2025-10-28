# Guide Utilisateur - Gestion des Bannières

## 🎯 Introduction

Ce guide vous explique comment gérer les bannières de votre page d'accueil depuis l'interface d'administration.

## 📍 Accès à la Gestion

1. Connectez-vous en tant qu'administrateur
2. Dans le menu principal, cliquez sur **"Administration"**
3. Sélectionnez **"Gérer les bannières"**

Ou depuis le tableau de bord admin, cliquez sur le bouton **"Gérer les bannières"**.

---

## ➕ Créer une Nouvelle Bannière

### Étape 1 : Accéder au formulaire
Cliquez sur le bouton **"Nouvelle bannière"** en haut à droite de la liste.

### Étape 2 : Remplir les informations

#### 📝 Informations Générales
- **Titre** (obligatoire) : Le texte principal affiché sur la bannière
  - Exemple : *"Apprenez sans limites avec Herime Académie"*
- **Sous-titre** (optionnel) : Texte secondaire sous le titre
  - Exemple : *"Découvrez des milliers de cours en ligne de qualité"*

#### 🖼️ Images
##### Image Principale (Desktop)
- **Format accepté** : JPG, PNG, WEBP
- **Taille maximum** : 10 MB
- **Dimensions recommandées** : 1920x1080 pixels (Full HD)
- **Obligatoire** : Oui

**Comment uploader :**
1. Cliquez dans la zone grise avec l'icône de nuage
2. Sélectionnez votre image
3. L'image s'affichera automatiquement en preview
4. Si l'image ne convient pas, cliquez sur **"Supprimer"**

⚠️ **Erreurs possibles :**
- ❌ "Fichier trop volumineux" → Votre image dépasse 10MB
- ❌ "Format invalide" → Utilisez uniquement JPG, PNG ou WEBP

##### Image Mobile (Optionnel)
- **Format accepté** : JPG, PNG, WEBP
- **Taille maximum** : 10 MB
- **Dimensions recommandées** : 1920x1080 pixels (16:9)
- **Usage** : Image adaptée aux mobiles (si non fournie, l'image desktop sera utilisée)

#### 🖱️ Boutons d'Action

##### Bouton Principal
- **Texte** : Ce qui sera affiché (ex: "Commencer à apprendre")
- **URL** : Lien de destination
  - **Liens internes** : Commencez par `/` (ex: `/courses`, `/categories/marketing`)
  - **Liens externes** : URL complète avec `http://` ou `https://` (ex: `https://youtube.com`)
  - **Ancres** : Pour scroller vers une section (ex: `#categories`)
- **Style** : Couleur du bouton
  - Warning (Jaune) : Recommandé pour attirer l'attention
  - Primary (Bleu) : Couleur principale du site
  - Success (Vert) : Action positive
  - Danger (Rouge) : Action importante
  - Info (Cyan) : Information
- **Ouverture** : Comment le lien s'ouvre
  - **Même onglet** : Le lien s'ouvre dans l'onglet actuel (recommandé pour liens internes)
  - **Nouvel onglet** : Le lien s'ouvre dans un nouvel onglet (recommandé pour liens externes)

##### Bouton Secondaire
- **Texte** : Texte du second bouton (ex: "Explorer les catégories")
- **URL** : Lien de destination
  - **Liens internes** : Commencez par `/` (ex: `/courses`, `/about`)
  - **Liens externes** : URL complète avec `http://` ou `https://` (ex: `https://partenaire.com`)
  - **Ancres** : Pour scroller vers une section (ex: `#categories`)
- **Style** : Couleur du bouton
  - Outline Light : Recommandé (transparent avec bordure)
  - Outline Primary : Bordure bleue
  - Secondary : Gris
  - Light : Blanc
- **Ouverture** : Comment le lien s'ouvre
  - **Même onglet** : Le lien s'ouvre dans l'onglet actuel (recommandé pour liens internes)
  - **Nouvel onglet** : Le lien s'ouvre dans un nouvel onglet (recommandé pour liens externes)

#### ⚙️ Paramètres

##### Ordre d'Affichage
- **Numéro** : Position dans le carrousel
- **Règle** : Plus le nombre est petit, plus la bannière apparaît en premier
  - Ordre 0 = Première bannière
  - Ordre 1 = Deuxième bannière
  - Etc.
- **Suggestion** : Le système suggère automatiquement le prochain ordre disponible

##### Statut
- **Actif** : ✅ La bannière sera visible sur le site
- **Inactif** : ❌ La bannière reste en base mais n'est pas affichée

### Étape 3 : Enregistrer
Cliquez sur **"Créer la bannière"**

✅ Message de succès : "Bannière créée avec succès"

---

## ✏️ Modifier une Bannière

### Accès
Depuis la liste des bannières, cliquez sur le bouton jaune **"Modifier"** (icône crayon).

### Modifications Possibles
- Tous les champs peuvent être modifiés
- **Images** : Si vous ne changez pas d'image, l'image actuelle est conservée
- L'**image actuelle** est affichée pour référence

### Conseils
- Pour changer seulement le texte : Modifiez le titre/sous-titre sans toucher aux images
- Pour changer seulement les images : Uploadez les nouvelles images
- Pour tout changer : Modifiez tous les champs selon vos besoins

---

## 🔄 Réorganiser les Bannières

### Méthode 1 : Boutons Flèches
Dans la colonne **"Ordre"** de la liste :
- **Flèche vers le haut** ⬆️ : Faire monter la bannière (ordre -1)
- **Flèche vers le bas** ⬇️ : Faire descendre la bannière (ordre +1)

**Note** : Les autres bannières s'ajustent automatiquement.

### Méthode 2 : Modification Manuelle
1. Cliquez sur **"Modifier"** la bannière
2. Changez le champ **"Ordre d'affichage"**
3. Enregistrez

### Exemple Pratique
Vous avez 3 bannières :
- Bannière A (ordre 0)
- Bannière B (ordre 1)
- Bannière C (ordre 2)

Pour faire passer C en premier :
1. Cliquez 2 fois sur la flèche ⬆️ de C
2. Résultat :
   - Bannière C (ordre 0) ← en premier
   - Bannière A (ordre 1)
   - Bannière B (ordre 2)

---

## 🔄 Activer/Désactiver une Bannière

### Méthode Rapide
Dans la liste, cliquez sur le bouton de statut :
- **Vert "Actif"** → Cliquez pour désactiver
- **Gris "Inactif"** → Cliquez pour activer

Une confirmation sera demandée.

### Utilité
- **Désactiver temporairement** : Pour une bannière saisonnière ou promotionnelle
- **Tester** : Désactiver pour voir le site sans cette bannière
- **Archiver** : Garder en base mais retirer du site

---

## 🗑️ Supprimer une Bannière

### Avertissement
⚠️ **La suppression est définitive et irréversible !**

### Procédure
1. Dans la liste, cliquez sur le bouton rouge **"Supprimer"** (icône poubelle)
2. Confirmez la suppression
3. La bannière et ses images sont définitivement supprimées

### Recommandation
💡 Préférez **désactiver** plutôt que supprimer si vous pensez réutiliser la bannière plus tard.

---

## 📱 Affichage Mobile

### Comportement
- Sur **desktop/tablette** : L'image principale est affichée
- Sur **mobile** :
  - Si une image mobile est fournie : Elle est utilisée
  - Sinon : L'image desktop est utilisée (avec adaptation automatique)

### Recommandation
Pour une meilleure expérience utilisateur sur mobile, fournissez toujours une image mobile optimisée.

### Conseils pour les Images Mobiles
- Privilégiez les compositions verticales ou carrées
- Évitez les petits textes (difficiles à lire)
- Assurez-vous que les éléments importants sont centrés
- Format 16:9 recommandé

---

## 🔗 Ouverture des Liens : Même Onglet vs Nouvel Onglet

### Quand Utiliser "Même Onglet" (_self)

**Recommandé pour :**
- ✅ Liens internes vers votre propre site
  - Pages de cours (`/courses`)
  - Catégories (`/categories/marketing-digital`)
  - Page À propos (`/about`)
  - Inscription (`/register`)
- ✅ Navigation principale du site
- ✅ Actions de formulaire

**Avantages :**
- Garde l'utilisateur dans le flux naturel de navigation
- Préserve l'historique de navigation
- Meilleure expérience utilisateur pour la navigation interne

**Exemple :**
```
Bouton : "Voir les cours"
URL : /courses
Ouverture : Même onglet
```

### Quand Utiliser "Nouvel Onglet" (_blank)

**Recommandé pour :**
- ✅ Liens externes vers d'autres sites
  - Sites partenaires
  - Ressources externes
  - Documentation externe
  - Réseaux sociaux
- ✅ PDF ou documents téléchargeables
- ✅ Vidéos YouTube ou contenus externes
- ✅ Tout contenu qui pourrait faire perdre le contexte

**Avantages :**
- L'utilisateur peut revenir facilement à votre site
- Évite de perdre du trafic
- Permet de consulter plusieurs ressources simultanément

**Exemple :**
```
Bouton : "Voir sur YouTube"
URL : https://youtube.com/watch?v=...
Ouverture : Nouvel onglet
```

### 🔒 Sécurité

Lorsque vous choisissez **"Nouvel onglet"**, le système ajoute automatiquement l'attribut `rel="noopener noreferrer"` pour :
- Prévenir les failles de sécurité
- Protéger les informations de navigation
- Améliorer les performances

**Vous n'avez rien à faire**, c'est géré automatiquement ! 🎉

### 💡 Bonnes Pratiques

1. **Par défaut : Même onglet**
   - Si vous hésitez, utilisez "Même onglet"
   - C'est le comportement attendu par les utilisateurs

2. **Cohérence**
   - Gardez le même comportement pour des actions similaires
   - Exemple : tous les boutons vers des cours internes en "Même onglet"

3. **Transparence**
   - Si un lien s'ouvre en nouvel onglet, le texte du bouton peut l'indiquer
   - Exemple : "Voir sur YouTube ↗" ou "Documentation externe ↗"

4. **Tests**
   - Testez toujours vos liens après création
   - Vérifiez que le comportement est celui attendu

---

## ✅ Bonnes Pratiques

### Images
1. **Qualité** : Utilisez des images haute définition (minimum 1920x1080)
2. **Format** : WEBP offre le meilleur compromis qualité/poids
3. **Contenu** :
   - Évitez le texte dans l'image (utilisez les champs titre/sous-titre)
   - Images lumineuses et contrastées
   - Pas de copyright/watermark visible

### Textes
1. **Titre** : Court et percutant (5-8 mots max)
2. **Sous-titre** : Complète le titre (10-15 mots max)
3. **Boutons** : Verbes d'action ("Découvrir", "Commencer", "Explorer")

### Ordre
1. **Première position** : Votre bannière la plus importante
2. **Rotation** : Changez l'ordre régulièrement pour tester
3. **Quantité** : 3-5 bannières actives maximum

### Statut
1. **Actif** : Maximum 5 bannières actives simultanément
2. **Désactiver** : Les bannières obsolètes plutôt que les supprimer
3. **Tester** : Toujours prévisualiser sur le site après création

---

## 🐛 Problèmes Fréquents

### "Fichier trop volumineux"
**Cause** : Image > 10MB  
**Solution** : 
1. Compressez votre image avec un outil en ligne (TinyPNG, Compressor.io)
2. Réduisez les dimensions si nécessaire
3. Changez de format (WEBP est plus léger)

### "Format invalide"
**Cause** : Format non supporté (GIF, BMP, etc.)  
**Solution** : Convertissez en JPG, PNG ou WEBP

### "L'image ne s'affiche pas sur le site"
**Causes possibles** :
1. Bannière désactivée → Vérifiez le statut
2. Ordre trop élevé → Vérifiez qu'elle n'est pas en dernière position si vous avez beaucoup de bannières
3. Cache du navigateur → Rafraîchissez avec Ctrl+F5

### "Les boutons ne fonctionnent pas"
**Vérifiez** :
1. L'URL est correcte (commence par / ou http://)
2. Pas d'espace avant/après l'URL
3. Le texte du bouton n'est pas vide

### "Le lien externe ne fonctionne pas correctement"
**Cause** : URL mal formatée  
**Solution** :
1. Assurez-vous d'inclure `https://` ou `http://` au début
2. ❌ Mauvais : `youtube.com/watch?v=123`
3. ✅ Correct : `https://youtube.com/watch?v=123`

---

## 🔗 Format des URLs : Guide Détaillé

### Types d'URLs Acceptées

#### 1. Liens Internes (Pages du Site)

**Format** : `/chemin/vers/la/page`

**Exemples :**
```
✅ /courses
✅ /categories/marketing-digital
✅ /about
✅ /register
✅ /courses/1234
```

**Comment ça marche :**
- Le système ajoute automatiquement votre domaine devant
- `/courses` devient → `https://votre-site.com/courses`
- Parfait pour la navigation interne

#### 2. Liens Externes (Autres Sites)

**Format** : `https://site-externe.com` ou `http://site-externe.com`

**Exemples :**
```
✅ https://youtube.com/watch?v=abc123
✅ https://www.google.com
✅ https://partenaire-exemple.com
✅ http://ancien-site.com (si pas de HTTPS)
```

**⚠️ IMPORTANT :**
- Vous **DEVEZ** inclure `https://` ou `http://`
- Sinon, le lien sera traité comme interne

**Erreurs Fréquentes :**
```
❌ youtube.com (manque https://)
❌ www.google.com (manque https://)
❌ partenaire.com/page (manque https://)

✅ https://youtube.com
✅ https://www.google.com
✅ https://partenaire.com/page
```

#### 3. Ancres (Sections de la Page)

**Format** : `#nom-de-la-section`

**Exemples :**
```
✅ #categories
✅ #about
✅ #contact
✅ #featured-courses
```

**Comment ça marche :**
- Fait défiler la page vers une section spécifique
- Pas de rechargement de page
- Nécessite que la section ait un `id` correspondant dans le HTML

#### 4. Combinaisons (Page + Ancre)

**Format** : `/page#section`

**Exemples :**
```
✅ /about#team
✅ /courses#popular
✅ /contact#form
```

### Tableau Récapitulatif

| Type | Format | Exemple | Ouverture Recommandée |
|------|--------|---------|----------------------|
| Interne | `/chemin` | `/courses` | Même onglet |
| Externe | `https://...` | `https://youtube.com` | Nouvel onglet |
| Ancre | `#section` | `#categories` | Même onglet |
| Interne + Ancre | `/page#section` | `/about#team` | Même onglet |

### Tests à Effectuer

Après avoir créé une bannière, testez toujours :

1. **Cliquez sur le bouton** : Le lien fonctionne-t-il ?
2. **Vérifiez la destination** : Arrivez-vous au bon endroit ?
3. **Vérifiez l'ouverture** : S'ouvre-t-il dans le bon onglet ?

### Exemples Pratiques

#### Exemple 1 : Bannière Cours Internes
```
Bouton 1:
- Texte: "Voir tous les cours"
- URL: /courses
- Ouverture: Même onglet
```

#### Exemple 2 : Bannière Vidéo YouTube
```
Bouton 1:
- Texte: "Regarder la démo ↗"
- URL: https://youtube.com/watch?v=abc123
- Ouverture: Nouvel onglet
```

#### Exemple 3 : Bannière avec Ancre
```
Bouton 1:
- Texte: "Découvrir les catégories"
- URL: #categories
- Ouverture: Même onglet
```

#### Exemple 4 : Bannière Partenaire
```
Bouton 1:
- Texte: "Visiter notre partenaire ↗"
- URL: https://partenaire-exemple.com
- Ouverture: Nouvel onglet
```

---

## 📞 Support

Si vous rencontrez un problème non mentionné dans ce guide :
1. Vérifiez que vous êtes bien connecté en tant qu'administrateur
2. Essayez de vider le cache du navigateur (Ctrl+F5)
3. Contactez le support technique avec :
   - Une capture d'écran du problème
   - Le message d'erreur exact
   - Les étapes pour reproduire le problème

---

## 🎓 Exemple Complet

### Créer une Bannière Promotionnelle

**Objectif** : Promouvoir les cours de marketing

#### Informations
- **Titre** : "Devenez Expert en Marketing Digital"
- **Sous-titre** : "Formation complète avec certification"

#### Images
- **Desktop** : `marketing-banner-desktop.jpg` (1920x1080, 3.2MB)
- **Mobile** : `marketing-banner-mobile.jpg` (1080x1920, 2.8MB)

#### Boutons
- **Bouton 1** :
  - Texte : "Voir les cours"
  - URL : `/categories/marketing-digital`
  - Style : Warning
- **Bouton 2** :
  - Texte : "En savoir plus"
  - URL : `/about`
  - Style : Outline Light

#### Paramètres
- **Ordre** : 0 (première position)
- **Statut** : Actif ✅

**Résultat** : La bannière apparaît en premier sur la page d'accueil avec deux boutons d'action.

---

**Version** : 1.0  
**Dernière mise à jour** : 28 octobre 2025

