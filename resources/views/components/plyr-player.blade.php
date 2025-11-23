@props([
    'lesson',
    'course',
    'lessonProgress' => null,
    'isMobile' => false
])

@php
    $lessonId = $lesson->id ?? null;
    $courseSlug = $course->slug ?? null;
    // Récupérer le temps de visionnage sauvegardé pour restaurer la position
    $savedTime = $lessonProgress && $lessonProgress->time_watched ? (int)$lessonProgress->time_watched : 0;
    $playerId = ($isMobile ? 'plyr-mobile-' : 'plyr-player-') . ($lesson->id ?? 'preview');
    $videoId = $lesson->youtube_video_id ?? '';
    // Sur la page d'apprentissage, on ignore is_preview car l'utilisateur est déjà inscrit
    // Le flag is_preview est utilisé pour les aperçus sur la page de présentation du cours
    $isPreview = false; // Toujours false sur la page d'apprentissage pour permettre le suivi de progression
    
    // Vérifier si c'est une vidéo YouTube
    $isYoutube = !empty($videoId);
    
    // Vérifier les vidéos internes - vérifier d'abord les attributs bruts
    $internalVideoUrl = null;
    $contentUrl = $lesson->getRawOriginal('content_url') ?? $lesson->content_url ?? null;
    $filePath = $lesson->getRawOriginal('file_path') ?? $lesson->file_path ?? null;
    
    // Si on a un file_path, utiliser l'accesseur file_url
    if (!empty($filePath)) {
        try {
            $internalVideoUrl = $lesson->file_url;
        } catch (\Exception $e) {
            // Si l'accesseur échoue, construire l'URL manuellement
            if (!filter_var($filePath, FILTER_VALIDATE_URL)) {
                $internalVideoUrl = route('files.serve', ['type' => 'lessons', 'path' => ltrim($filePath, '/')]);
            } else {
                $internalVideoUrl = $filePath;
            }
        }
    }
    // Sinon, vérifier content_url
    elseif (!empty($contentUrl)) {
        // Vérifier si c'est une URL externe (YouTube, Vimeo, etc.)
        $isExternalUrl = filter_var($contentUrl, FILTER_VALIDATE_URL) && 
                        (str_contains($contentUrl, 'youtube.com') || 
                         str_contains($contentUrl, 'youtu.be') || 
                         str_contains($contentUrl, 'vimeo.com'));
        
        // Si ce n'est pas une URL externe, c'est probablement un fichier interne
        if (!$isExternalUrl) {
            try {
                $internalVideoUrl = $lesson->content_file_url;
            } catch (\Exception $e) {
                // Si l'accesseur échoue, construire l'URL manuellement
                if (!filter_var($contentUrl, FILTER_VALIDATE_URL)) {
                    $internalVideoUrl = route('files.serve', ['type' => 'lessons', 'path' => ltrim($contentUrl, '/')]);
                } else {
                    $internalVideoUrl = $contentUrl;
                }
            }
        }
    }
    
    $isInternalVideo = !empty($internalVideoUrl) && !$isYoutube;
    
    // Détecter le type MIME à partir de l'extension du fichier
    $videoMimeType = 'video/mp4'; // Par défaut
    if ($isInternalVideo && $internalVideoUrl) {
        $parsedUrl = parse_url($internalVideoUrl);
        $path = $parsedUrl['path'] ?? $internalVideoUrl;
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mimeTypes = [
            'mp4' => 'video/mp4',
            'webm' => 'video/webm',
            'ogg' => 'video/ogg',
            'ogv' => 'video/ogg',
            'mov' => 'video/quicktime',
            'avi' => 'video/x-msvideo',
        ];
        $videoMimeType = $mimeTypes[$extension] ?? 'video/mp4';
    }
@endphp

@if($isYoutube || $isInternalVideo)
<div class="plyr-player-wrapper {{ $isYoutube ? 'plyr-external-video' : 'plyr-internal-video' }} position-absolute top-0 start-0 w-100 h-100" id="wrapper-{{ $playerId }}" style="margin: 0; padding: 0; width: 100% !important; height: 100% !important; min-width: 100% !important; min-height: 100% !important; max-width: 100% !important; max-height: 100% !important; overflow: hidden;">
    <!-- Watermark overlay dynamique -->
    @if(auth()->check() && !$isPreview)
    <div class="video-watermark position-absolute" id="watermark-{{ $playerId }}">
        <div class="watermark-content p-2 rounded">
            <small class="d-block fw-bold">{{ auth()->user()->name }}</small>
            <small class="d-block text-white-50">{{ auth()->user()->email }}</small>
            <small class="d-block text-white-50">Session: {{ Str::limit(session()->getId(), 8) }}</small>
        </div>
    </div>
    @endif
    
    @if($isYoutube)
        <!-- Plyr Player Container pour YouTube -->
        <div class="plyr__video-embed" id="{{ $playerId }}" data-plyr-provider="youtube" data-plyr-embed-id="{{ $videoId }}" style="width: 100%; height: 100%; margin: 0; padding: 0;"></div>
    @else
        <!-- Plyr Player Container pour vidéo interne -->
        <video id="{{ $playerId }}" class="plyr-player-video" playsinline controls preload="metadata" controlsList="nodownload" style="width: 100%; height: 100%; margin: 0; padding: 0;">
            <source src="{{ $internalVideoUrl }}" type="{{ $videoMimeType }}">
            Votre navigateur ne supporte pas la lecture vidéo.
        </video>
    @endif
</div>
@else
<div class="text-center py-5 position-absolute top-0 start-0 w-100 h-100 d-flex flex-column align-items-center justify-content-center" style="min-height: 450px;">
    <i class="fas fa-video fa-3x text-muted mb-3"></i>
    <p class="text-muted">Aucune vidéo disponible</p>
</div>
@endif


@push('scripts')
<script>
(function() {
    'use strict';
    
    // Attendre que Plyr soit chargé
    function waitForPlyr(callback, maxAttempts = 50) {
        let attempts = 0;
        const checkPlyr = setInterval(function() {
            attempts++;
            if (window.Plyr) {
                clearInterval(checkPlyr);
                callback();
            } else if (attempts >= maxAttempts) {
                clearInterval(checkPlyr);
                // Charger Plyr depuis CDN si pas disponible
                const link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = 'https://cdn.plyr.io/3.7.8/plyr.css';
                document.head.appendChild(link);
                
                const script = document.createElement('script');
                script.src = 'https://cdn.plyr.io/3.7.8/plyr.polyfilled.js';
                script.onload = callback;
                document.body.appendChild(script);
            }
        }, 100);
    }
    
    waitForPlyr(function() {
        initializePlayer();
    });
    
    function initializePlayer() {
        const playerId = '{{ $playerId }}';
        const videoId = '{{ $videoId }}';
        const isYoutube = {{ $isYoutube ? 'true' : 'false' }};
        const isInternalVideo = {{ $isInternalVideo ? 'true' : 'false' }};
        const playerElement = document.getElementById('{{ $playerId }}');
        const wrapper = document.getElementById('wrapper-{{ $playerId }}');
        const watermark = document.getElementById('watermark-{{ $playerId }}');
        const isPreview = {{ $isPreview ? 'true' : 'false' }};
        const isAuth = {{ auth()->check() ? 'true' : 'false' }};
        const lessonId = {{ $lessonId ?? 'null' }};
        const courseSlug = '{{ $courseSlug ?? '' }}';
        const savedTime = {{ $savedTime ?? 0 }}; // Temps sauvegardé pour restaurer la position
        
        // URLs pour les routes de progression
        const startLessonUrl = lessonId && courseSlug ? `/learning/courses/${courseSlug}/lessons/${lessonId}/start` : null;
        const updateProgressUrl = lessonId && courseSlug ? `/learning/courses/${courseSlug}/lessons/${lessonId}/progress` : null;
        
        // Variables pour le suivi de progression
        let lastProgressUpdateTime = 0; // Timestamp de la dernière mise à jour
        let lastVideoTime = 0; // Temps de la vidéo lors de la dernière mise à jour
        let hasStarted = false;
        let hasRestoredPosition = false; // Indique si la position a été restaurée
        
        if (!window.Plyr) {
            return;
        }
    
    if (!playerElement) {
        return;
    }
    
    // Fonction pour initialiser Plyr
    function initPlyrPlayer() {
        // Configuration de base pour Plyr
        const plyrConfig = {
            controls: ['play-large', 'play', 'progress', 'current-time', 'mute', 'volume', 'settings', 'fullscreen'],
            settings: ['quality', 'speed'],
            keyboard: { focused: true, global: false },
            tooltips: { controls: true, seek: true },
            clickToPlay: true,
            hideControls: true,
            resetOnEnd: false,
            disableContextMenu: true,
            download: false,
            // Forcer la langue française
            locale: 'fr',
            i18n: {
                restart: 'Redémarrer',
                rewind: 'Rembobiner',
                play: 'Lire',
                pause: 'Pause',
                fastForward: 'Avance rapide',
                seek: 'Rechercher',
                seekLabel: '{currentTime} sur {duration}',
                played: 'Lu',
                buffered: 'En mémoire tampon',
                currentTime: 'Temps actuel',
                duration: 'Durée',
                volume: 'Volume',
                mute: 'Couper le son',
                unmute: 'Activer le son',
                enableCaptions: 'Activer les sous-titres',
                disableCaptions: 'Désactiver les sous-titres',
                download: 'Télécharger',
                enterFullscreen: 'Plein écran',
                exitFullscreen: 'Quitter le plein écran',
                frameTitle: 'Lecteur pour {title}',
                captions: 'Sous-titres',
                settings: 'Paramètres',
                pip: 'Image dans l\'image',
                menuBack: 'Retour au menu précédent',
                speed: 'Vitesse',
                normal: 'Normal',
                quality: 'Qualité',
                loop: 'Boucle',
                start: 'Début',
                end: 'Fin',
                all: 'Tout',
                reset: 'Réinitialiser',
                disabled: 'Désactivé',
                enabled: 'Activé',
                advertisement: 'Publicité',
                qualityBadge: {
                    2160: '4K',
                    1440: 'HD',
                    1080: 'HD',
                    720: 'HD',
                    576: 'SD',
                    480: 'SD'
                }
            }
        };
        
        // Ajouter la configuration YouTube uniquement si c'est une vidéo YouTube
        if (isYoutube) {
            plyrConfig.youtube = {
                noCookie: false,
                rel: 0,
                showinfo: 0,
                iv_load_policy: 3,
                modestbranding: 1,
                controls: 0,
                disablekb: 1,
                fs: 0,
                cc_load_policy: 0
            };
        }
        
        let player;
        try {
            player = new Plyr(playerElement, plyrConfig);
            
            // Forcer la langue française après l'initialisation et mettre à jour les tooltips
            if (player) {
                // Mettre à jour la langue
                if (typeof player.language !== 'undefined') {
                    player.language = 'fr';
                }
                
                // Forcer la mise à jour des tooltips en français
                if (player.config && player.config.i18n) {
                    // Les traductions sont déjà dans la config, mais on s'assure qu'elles sont appliquées
                    player.config.i18n = plyrConfig.i18n;
                }
                
                // Forcer la mise à jour des tooltips après que le lecteur soit prêt
                player.on('ready', function() {
                    // Fonction pour mettre à jour les tooltips
                    const updateTooltips = function() {
                        const tooltipMap = {
                            'play': 'Lire',
                            'pause': 'Pause',
                            'restart': 'Redémarrer',
                            'rewind': 'Rembobiner',
                            'fastForward': 'Avance rapide',
                            'mute': 'Couper le son',
                            'unmute': 'Activer le son',
                            'volume': 'Volume',
                            'enterFullscreen': 'Plein écran',
                            'exitFullscreen': 'Quitter le plein écran',
                            'settings': 'Paramètres',
                            'pip': 'Image dans l\'image',
                            'download': 'Télécharger',
                            'captions': 'Sous-titres'
                        };
                        
                        // Mettre à jour les attributs aria-label et title des boutons
                        const controls = player.media.parentElement.querySelectorAll('.plyr__control');
                        controls.forEach(function(control) {
                            const action = control.getAttribute('data-plyr');
                            if (action && tooltipMap[action]) {
                                control.setAttribute('aria-label', tooltipMap[action]);
                                control.setAttribute('title', tooltipMap[action]);
                            }
                        });
                        
                        // Mettre à jour les tooltips directement (Plyr les génère dynamiquement)
                        const tooltips = player.media.parentElement.querySelectorAll('.plyr__tooltip');
                        tooltips.forEach(function(tooltip) {
                            const text = tooltip.textContent.toLowerCase().trim();
                            for (const [key, value] of Object.entries(tooltipMap)) {
                                if (text.includes(key) || text === key) {
                                    tooltip.textContent = value;
                                    break;
                                }
                            }
                        });
                    };
                    
                    // Exécuter immédiatement et périodiquement pour attraper les tooltips générés dynamiquement
                    updateTooltips();
                    setTimeout(updateTooltips, 200);
                    setTimeout(updateTooltips, 500);
                    setTimeout(updateTooltips, 1000);
                    
                    // Observer les changements du DOM pour mettre à jour les tooltips quand ils apparaissent
                    const observer = new MutationObserver(function(mutations) {
                        updateTooltips();
                    });
                    
                    if (player.media && player.media.parentElement) {
                        observer.observe(player.media.parentElement, {
                            childList: true,
                            subtree: true,
                            attributes: true,
                            attributeFilter: ['aria-label', 'title']
                        });
                    }
                });
            }
            
            // Sauvegarder la référence
            window['plyr_' + playerId] = player;
            
            // Gérer les erreurs de chargement pour les vidéos internes
            if (isInternalVideo && playerElement.tagName === 'VIDEO') {
                playerElement.addEventListener('error', function(e) {
                    const error = playerElement.error;
                    if (error) {
                        // Gestion silencieuse des erreurs
                    }
                });
            }
            
        } catch (error) {
            return null;
        }
        
        // Désactiver le menu contextuel
        if (wrapper) {
            wrapper.addEventListener('contextmenu', function(e) {
                e.preventDefault();
                return false;
            });
            
            wrapper.addEventListener('dragstart', function(e) {
                e.preventDefault();
                return false;
            });
        }
        
        // Afficher le watermark si authentifié
        if (watermark && !isPreview && isAuth) {
            player.on('ready', function() {
                setTimeout(() => {
                    watermark.classList.add('show');
                }, 1000);
            });
        }
        
        // Restaurer la position sauvegardée pour toutes les vidéos (YouTube et internes)
        if (savedTime > 0) {
            // Fonction pour restaurer la position
            const restorePosition = function() {
                if (hasRestoredPosition) {
                    return true;
                }
                
                // Vérifier que la durée est disponible et valide
                if (player.duration && player.duration > 0 && savedTime < player.duration) {
                    try {
                        player.currentTime = savedTime;
                        hasRestoredPosition = true;
                        return true;
                    } catch (error) {
                        // Erreur silencieuse
                    }
                }
                return false;
            };
            
            // Pour les vidéos internes, restaurer dès que les métadonnées sont chargées
            if (isInternalVideo && playerElement.tagName === 'VIDEO') {
                const tryRestore = function() {
                    if (playerElement.readyState >= 1) { // HAVE_METADATA
                        if (playerElement.duration && playerElement.duration > savedTime) {
                            playerElement.currentTime = savedTime;
                            hasRestoredPosition = true;
                        }
                    }
                };
                
                // Essayer immédiatement si déjà chargé
                if (playerElement.readyState >= 1) {
                    tryRestore();
                }
                
                // Écouter les événements de chargement
                playerElement.addEventListener('loadedmetadata', tryRestore);
                playerElement.addEventListener('loadeddata', tryRestore);
                playerElement.addEventListener('canplay', tryRestore);
            }
            
            // Pour YouTube et toutes les vidéos via Plyr
            player.on('ready', function() {
                // Attendre un peu que la durée soit chargée
                setTimeout(function() {
                    if (!restorePosition()) {
                        // Si ça n'a pas fonctionné, réessayer périodiquement
                        let attempts = 0;
                        const maxAttempts = 25; // 5 secondes max (25 * 200ms)
                        const checkInterval = setInterval(function() {
                            attempts++;
                            if (restorePosition() || attempts >= maxAttempts) {
                                clearInterval(checkInterval);
                            }
                        }, 200);
                    }
                }, 300);
            });
            
            // Événements supplémentaires pour YouTube
            if (isYoutube) {
                player.on('loadeddata', function() {
                    restorePosition();
                });
                
                player.on('canplay', function() {
                    restorePosition();
                });
            }
        }
        
        // Suivi de progression (uniquement si authentifié et pas en preview)
        if (isAuth && !isPreview && lessonId && courseSlug) {
            // Marquer comme commencée quand la vidéo commence
            player.on('play', function() {
                if (!hasStarted && startLessonUrl) {
                    hasStarted = true;
                    const currentTime = Math.floor(player.currentTime || 0);
                    
                    fetch(startLessonUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            time_watched: currentTime
                        })
                    })
                    .then(response => response.json())
                    .catch(error => {
                        // Erreur silencieuse
                    });
                }
            });
            
            // Mettre à jour la progression toutes les 10 secondes
            player.on('timeupdate', function() {
                if (!updateProgressUrl) {
                    return;
                }
                
                const currentTime = Math.floor(player.currentTime || 0);
                const now = Date.now();
                
                // Mettre à jour toutes les 10 secondes ou si la différence de temps vidéo est significative (10 secondes)
                if (now - lastProgressUpdateTime > 10000 || Math.abs(currentTime - lastVideoTime) >= 10) {
                    lastProgressUpdateTime = now;
                    lastVideoTime = currentTime;
                    
                    // Calculer si la vidéo est presque terminée (95% ou plus)
                    const duration = player.duration || 0;
                    const isAlmostComplete = duration > 0 && (currentTime / duration) >= 0.95;
                    
                    fetch(updateProgressUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            time_watched: currentTime,
                            is_completed: isAlmostComplete
                        })
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .catch(error => {
                        // Erreur silencieuse
                    });
                }
            });
            
            // Marquer comme complétée quand la vidéo se termine
            player.on('ended', function() {
                if (updateProgressUrl) {
                    const duration = Math.floor(player.duration || 0);
                    
                    fetch(updateProgressUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            time_watched: duration,
                            is_completed: true
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Recharger la page pour mettre à jour l'interface
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        }
                    })
                    .catch(error => {
                        // Erreur silencieuse
                    });
                }
            });
        }
        
        return player;
    }
    
    // Détecter si le conteneur est dans un modal Bootstrap
    const modalElement = wrapper?.closest('.modal');
    
    if (modalElement) {
        // Si dans un modal, initialiser quand le modal est affiché
        modalElement.addEventListener('shown.bs.modal', function() {
            const existingPlayer = window['plyr_' + playerId];
            if (!existingPlayer) {
                initPlyrPlayer();
            }
        });
    } else {
        // Sinon, initialiser normalement
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initPlyrPlayer);
        } else {
            initPlyrPlayer();
        }
    }
    }
})();
</script>
@endpush
