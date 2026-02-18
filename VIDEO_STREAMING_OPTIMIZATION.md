# Optimisation du streaming vidéo

Ce document décrit les optimisations appliquées pour une lecture vidéo fluide type YouTube.

## Paramètres

### `.env`

```env
# Activer l'optimisation MP4 faststart à l'upload (requiert FFmpeg)
VIDEO_OPTIMIZE_FASTSTART=true
```

Mettre à `false` si FFmpeg n'est pas disponible sur l'hébergement (hébergement mutualisé sans FFmpeg).

## Vidéos existantes

Pour optimiser les MP4 déjà présents sur le serveur (uploadés avant cette mise à jour) :

```bash
# Voir les fichiers concernés (sans modification)
php artisan videos:optimize-streaming --dry-run

# Optimiser tous les MP4
php artisan videos:optimize-streaming
```

## Techniques utilisées

1. **Chunks 2 Mo** : Taille de lecture élevée pour réduire les appels système
2. **Cache navigateur** : Les segments sont mis en cache 24 h
3. **MP4 Faststart** : Le moov atom est placé au début du fichier pour permettre une lecture progressive sans télécharger tout le fichier
4. **Preload auto** : Buffering agressif dès le chargement
5. **Prefetch au survol** : Sur la page détail du cours, préchargement au survol du bouton « Voir les aperçus »
