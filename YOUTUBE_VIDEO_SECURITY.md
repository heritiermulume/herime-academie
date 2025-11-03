# Implémentation YouTube Video Security - Herime Academie

## Vue d'ensemble

Ce document décrit l'implémentation complète du système de gestion sécurisée des vidéos YouTube pour les cours en ligne de Herime Academie. Cette implémentation remplace complètement le système précédent de streaming vidéo direct.

## Stratégie de Sécurité

### Principes Fondamentaux

1. **Ne jamais exposer le lien brut publiquement** - Les vidéos YouTube ne sont jamais directement accessibles
2. **Accès temporaire tokenisé** - Chaque session utilisateur génère un token unique avec expiration
3. **Chargement différé de l'iframe** - L'URL YouTube est injectée dynamiquement après validation
4. **Watermark dynamique** - Affichage de l'identité utilisateur en overlay sur la vidéo
5. **Limitation des streams concurrents** - Maximum 3 streams simultanés par utilisateur
6. **Surveillance et blacklist** - Tracking des IPs et tokens suspects

## Architecture

### Base de Données

#### 1. Table `course_lessons` (modifiée)
Ajout de 3 champs :
- `youtube_video_id` : ID de la vidéo YouTube (11 caractères)
- `is_unlisted` : Booléen indiquant si la vidéo est en mode non répertorié
- `youtube_embed_url` : URL d'embed générée automatiquement

#### 2. Table `video_access_tokens` (nouvelle)
Gestion des tokens d'accès temporaires :
- `user_id` : Utilisateur propriétaire du token
- `lesson_id` : Leçon associée
- `token` : Token unique de 64 caractères
- `ip_address` : Adresse IP de génération
- `user_agent` : Navigateur utilisateur
- `expires_at` : Date d'expiration
- `is_revoked` : Statut de révocation
- `concurrent_streams` : Compteur de streams

### Modèles

#### CourseLesson (modifié)
```php
- isYoutubeVideo() : Vérifie si la leçon utilise YouTube
- getSecureYouTubeEmbedUrl() : Génère l'URL d'embed sécurisée
- getYouTubeWatchUrl() : URL de visualisation YouTube
```

#### VideoAccessToken (nouveau)
```php
- isValid() : Vérifie la validité du token
- createForUser() : Crée un nouveau token
- cleanupExpired() : Nettoie les tokens expirés
- canAddConcurrentStream() : Vérifie la limite de streams
```

### Services

#### VideoSecurityService
Service central de gestion de la sécurité :

**Méthodes principales :**
- `generateAccessToken()` : Génère un token pour un utilisateur
- `validateToken()` : Valide un token d'accès
- `revokeToken()` : Révoque un token (en cas de fuite)
- `blacklistIp()` : Blacklist une IP
- `monitorSuspiciousActivity()` : Surveillance des activités suspectes

**Protection :**
- Limitation des streams concurrents (max 3)
- Tracking des IPs blacklistées (Cache Redis/File)
- Détection d'accès suspects (seuil configurable)
- Logging complet des accès

### Contrôleurs

#### YouTubeAccessController
Gestion des accès aux vidéos YouTube :

**Routes :**
- `POST /video/lessons/{lesson}/access-token` : Générer un token
- `POST /video/validate-token` : Valider un token
- `POST /video/cleanup-tokens` : Nettoyer les tokens expirés

**Flux de validation :**
1. Vérification de l'authentification
2. Vérification de l'inscription au cours
3. Vérification du type YouTube de la leçon
4. Génération du token via VideoSecurityService
5. Retour de l'URL d'embed sécurisée

#### AdminController (modifié)
Gestion des leçons avec support YouTube :

**Modifications :**
- Ajout de `youtube_video_id` et `is_unlisted` dans la validation
- Méthode `extractYouTubeVideoId()` pour parser les URLs YouTube
- Support des formats d'URL : youtube.com/watch, youtu.be, embed, etc.

### Configuration

Fichier `config/video.php` :
```php
'token_validity_hours' => 24
'max_concurrent_streams' => 3
'strict_ip_check' => false
'suspicious_access_threshold' => 100
'watermark' => [...]
'youtube' => [...]
```

## Flux Utilisateur

### Chargement d'une Vidéo

1. **Accès à la leçon**
   - Utilisateur authentifié et inscrit au cours
   - Chargement de la page avec placeholder

2. **Génération du token (côté client)**
   ```javascript
   fetch('/video/lessons/{id}/access-token', {
       method: 'POST',
       headers: { 'X-CSRF-TOKEN': token }
   })
   ```

3. **Validation côté serveur**
   - Vérification de l'authentification
   - Vérification de l'inscription
   - Vérification des streams concurrents
   - Vérification de l'IP blacklistée

4. **Retour du token**
   ```json
   {
       "success": true,
       "embed_url": "https://www.youtube.com/embed/...",
       "token": "64-char-token",
       "expires_at": "2025-10-31T12:00:00Z"
   }
   ```

5. **Injection de l'iframe**
   - Création dynamique de l'iframe YouTube
   - Ajout du watermark overlay
   - Tracking de la progression

6. **Watermark dynamique**
   - Nom de l'utilisateur
   - Email de l'utilisateur
   - ID de session (limité à 8 caractères)
   - Position : bottom-right
   - Opacité : 70%

## Sécurité

### Protection Multi-Niveaux

1. **Authentification** : Uniquement utilisateurs connectés
2. **Autorisation** : Vérification de l'inscription au cours
3. **Token unique** : Chaque session génère son propre token
4. **Expiration** : Tokens expirant après 24h
5. **IP tracking** : Surveillance des accès par IP
6. **Concurrent streams** : Limite de 3 streams simultanés
7. **Blacklist** : IPs suspectes automatiquement blacklistées
8. **Révocation** : Tokens révocables en cas de fuite
9. **Watermark** : Overlay dissuasif pour le partage
10. **Logging** : Traçabilité complète des accès

### Monitoring

- **Logs d'accès** : Tous les accès vidéo sont loggés
- **Détection d'anomalies** : Activités suspectes détectées automatiquement
- **Nettoyage automatique** : Tokens expirés nettoyés régulièrement
- **Alertes** : Notifications en cas de fuite détectée

## Migration

### Champs ajoutés à `course_lessons`
```sql
ALTER TABLE course_lessons ADD COLUMN youtube_video_id VARCHAR(100) NULL AFTER content_url;
ALTER TABLE course_lessons ADD COLUMN is_unlisted BOOLEAN DEFAULT FALSE AFTER youtube_video_id;
ALTER TABLE course_lessons ADD COLUMN youtube_embed_url TEXT NULL AFTER is_unlisted;
```

### Nouvelle table `video_access_tokens`
```sql
CREATE TABLE video_access_tokens (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    lesson_id BIGINT NOT NULL,
    token VARCHAR(64) UNIQUE NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT NULL,
    expires_at TIMESTAMP NOT NULL,
    is_revoked BOOLEAN DEFAULT FALSE,
    concurrent_streams INT DEFAULT 1,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (lesson_id) REFERENCES course_lessons(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_user_lesson (user_id, lesson_id),
    INDEX idx_expires (expires_at)
);
```

## API Endpoints

### POST /video/lessons/{lesson}/access-token
Génère un nouveau token d'accès.

**Réponse :**
```json
{
    "success": true,
    "embed_url": "https://www.youtube.com/embed/v=?",
    "token": "...",
    "expires_at": "...",
    "user_info": {
        "name": "...",
        "email": "..."
    }
}
```

### POST /video/validate-token
Valide un token existant.

**Réponse :**
```json
{
    "success": true,
    "valid": true,
    "embed_url": "...",
    "lesson_id": 123,
    "expires_at": "..."
}
```

## Variables d'Environnement

```env
VIDEO_TOKEN_VALIDITY_HOURS=24
VIDEO_MAX_CONCURRENT_STREAMS=3
VIDEO_STRICT_IP_CHECK=false
VIDEO_SUSPICIOUS_ACCESS_THRESHOLD=100
VIDEO_WATERMARK_ENABLED=true
VIDEO_WATERMARK_POSITION=bottom-right
VIDEO_WATERMARK_OPACITY=0.7
VIDEO_ROTATION_ENABLED=false
VIDEO_ROTATION_INTERVAL_DAYS=30
YOUTUBE_API_KEY=your-api-key
YOUTUBE_EMBED_DOMAIN=herime-academie.com
```

## TODO - Intégration Complète

### À faire pour finaliser :

1. **Vues Blade** : Remplacer les balises `<video>` par le système YouTube
   - Créer le composant `youtube-player.blade.php`
   - Modifier `learning/course.blade.php`
   - Ajouter les formulaires d'upload YouTube dans l'admin

2. **JavaScript** : Ajouter le script de chargement dynamique
   - Chargement de l'iframe après validation token
   - Gestion du watermark
   - Tracking de progression avec YouTube IFrame API

3. **Cron Job** : Nettoyage automatique des tokens
   ```php
   // Dans app/Console/Kernel.php
   $schedule->call(function() {
       \App\Models\VideoAccessToken::cleanupExpired();
       \App\Services\VideoSecurityService::monitorSuspiciousActivity();
   })->hourly();
   ```

4. **Tests** : Créer des tests unitaires et fonctionnels
   - Tests du VideoSecurityService
   - Tests du YouTubeAccessController
   - Tests des modèles

## Fichiers Modifiés/Créés

### Nouveaux Fichiers
- `database/migrations/..._add_youtube_fields_to_course_lessons_table.php`
- `database/migrations/..._create_video_access_tokens_table.php`
- `app/Models/VideoAccessToken.php`
- `app/Services/VideoSecurityService.php`
- `app/Http/Controllers/YouTubeAccessController.php`
- `config/video.php`
- `resources/views/components/youtube-player.blade.php` (à créer)

### Fichiers Modifiés
- `app/Models/CourseLesson.php`
- `app/Http/Controllers/AdminController.php`
- `routes/web.php`
- `resources/views/learning/course.blade.php` (à modifier)
- `resources/views/admin/courses/lessons/create.blade.php` (à modifier)
- `resources/views/admin/courses/lessons/edit.blade.php` (à modifier)

### Fichiers Supprimés
- `app/Http/Controllers/VideoStreamController.php` (remplacé)

## Conclusion

Cette implémentation fournit une solution robuste et sécurisée pour la gestion des vidéos de cours via YouTube en mode non répertorié. Le système est conçu pour dissuader le partage non autorisé tout en offrant une expérience utilisateur fluide.

