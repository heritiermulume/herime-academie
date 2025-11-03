# Configuration des Variables d'Environnement YouTube

## Ajouter ces variables à votre fichier `.env`

```env
# ==========================================
# YouTube Video Security Configuration
# ==========================================

# Durée de validité des tokens d'accès (en heures)
VIDEO_TOKEN_VALIDITY_HOURS=24

# Nombre maximum de streams simultanés par utilisateur
VIDEO_MAX_CONCURRENT_STREAMS=3

# Vérification stricte de l'adresse IP (true/false)
# Si true, l'IP doit correspondre exactement au token
# Si false, tolérance pour les changements d'IP légitimes
VIDEO_STRICT_IP_CHECK=false

# Seuil d'accès suspects pour déclencher le blacklist
# Nombre de requêtes depuis la même IP dans une fenêtre de temps
VIDEO_SUSPICIOUS_ACCESS_THRESHOLD=100

# Configuration du watermark dynamique
VIDEO_WATERMARK_ENABLED=true
VIDEO_WATERMARK_POSITION=bottom-right
VIDEO_WATERMARK_OPACITY=0.7

# Configuration YouTube API (optionnel pour stats avancées)
YOUTUBE_API_KEY=your_youtube_api_key_here
YOUTUBE_EMBED_DOMAIN=herime-academie.com

# Rotation automatique des uploads (recommandé: false en production)
VIDEO_ROTATION_ENABLED=false
VIDEO_ROTATION_INTERVAL_DAYS=30
```

## Documentation Complète

Voir `YOUTUBE_VIDEO_SECURITY.md` pour la documentation complète.

