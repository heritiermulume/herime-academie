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
    
    // Vérifier si c'est une vidéo YouTube - vérifier youtube_video_id d'abord
    $videoId = $lesson->youtube_video_id ?? '';
    $isYoutube = !empty($videoId) && trim($videoId) !== '';
    
    // Si pas de youtube_video_id, vérifier si content_url contient une URL YouTube
    if (!$isYoutube && !empty($lesson->content_url)) {
        $contentUrl = $lesson->content_url;
        if (str_contains($contentUrl, 'youtube.com') || str_contains($contentUrl, 'youtu.be')) {
            // Extraire l'ID de la vidéo YouTube depuis l'URL
            if (str_contains($contentUrl, 'youtube.com/watch')) {
                parse_str(parse_url($contentUrl, PHP_URL_QUERY), $query);
                $videoId = $query['v'] ?? '';
            } elseif (str_contains($contentUrl, 'youtu.be/')) {
                $videoId = basename(parse_url($contentUrl, PHP_URL_PATH));
            } elseif (str_contains($contentUrl, 'youtube.com/embed/')) {
                $videoId = basename(parse_url($contentUrl, PHP_URL_PATH));
            }
            $isYoutube = !empty($videoId) && trim($videoId) !== '';
        }
    }
    
    // Sur la page d'apprentissage, on ignore is_preview car l'utilisateur est déjà inscrit
    // Le flag is_preview est utilisé pour les aperçus sur la page de présentation du cours
    $isPreview = false; // Toujours false sur la page d'apprentissage pour permettre le suivi de progression
    
    // Vérifier les vidéos internes - vérifier d'abord les attributs bruts
    $internalVideoUrl = null;
    $filePath = $lesson->getRawOriginal('file_path') ?? $lesson->file_path ?? null;
    $contentUrlRaw = $lesson->getRawOriginal('content_url') ?? $lesson->content_url ?? null;
    
    // Si on a un file_path, utiliser l'accesseur file_url
    if (!empty($filePath) && trim($filePath) !== '') {
        try {
            $fileUrl = $lesson->file_url;
            // Vérifier que l'URL n'est pas vide
            if (!empty($fileUrl) && trim($fileUrl) !== '') {
                $internalVideoUrl = $fileUrl;
            } else {
                // Si l'accesseur retourne une chaîne vide, construire l'URL manuellement
                if (!filter_var($filePath, FILTER_VALIDATE_URL)) {
                    $internalVideoUrl = route('files.serve', ['type' => 'lessons', 'path' => ltrim($filePath, '/')]);
                } else {
                    $internalVideoUrl = $filePath;
                }
            }
        } catch (\Exception $e) {
            // Si l'accesseur échoue, construire l'URL manuellement
            if (!filter_var($filePath, FILTER_VALIDATE_URL)) {
                $internalVideoUrl = route('files.serve', ['type' => 'lessons', 'path' => ltrim($filePath, '/')]);
            } else {
                $internalVideoUrl = $filePath;
            }
        }
    }
    // Sinon, vérifier content_url (seulement si ce n'est pas YouTube)
    elseif (!empty($contentUrlRaw) && trim($contentUrlRaw) !== '' && !$isYoutube) {
        // Vérifier si c'est une URL externe (Vimeo, etc.) - YouTube est déjà géré
        $isExternalUrl = filter_var($contentUrlRaw, FILTER_VALIDATE_URL) && 
                        (str_contains($contentUrlRaw, 'vimeo.com'));
        
        // Si ce n'est pas une URL externe, c'est probablement un fichier interne
        if (!$isExternalUrl) {
            try {
                $contentFileUrl = $lesson->content_file_url;
                // Vérifier que l'URL n'est pas vide
                if (!empty($contentFileUrl) && trim($contentFileUrl) !== '') {
                    $internalVideoUrl = $contentFileUrl;
                } else {
                    // Si l'accesseur retourne une chaîne vide, construire l'URL manuellement
                    if (!filter_var($contentUrlRaw, FILTER_VALIDATE_URL)) {
                        $internalVideoUrl = route('files.serve', ['type' => 'lessons', 'path' => ltrim($contentUrlRaw, '/')]);
                    } else {
                        $internalVideoUrl = $contentUrlRaw;
                    }
                }
            } catch (\Exception $e) {
                // Si l'accesseur échoue, construire l'URL manuellement
                if (!filter_var($contentUrlRaw, FILTER_VALIDATE_URL)) {
                    $internalVideoUrl = route('files.serve', ['type' => 'lessons', 'path' => ltrim($contentUrlRaw, '/')]);
                } else {
                    $internalVideoUrl = $contentUrlRaw;
                }
            }
        }
    }
    
    // Vérifier que l'URL interne n'est pas vide
    $isInternalVideo = !empty($internalVideoUrl) && trim($internalVideoUrl) !== '' && !$isYoutube;
    
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

<style>
.video-watermark {
    display: none !important;
    visibility: hidden !important;
    opacity: 0 !important;
}

.video-watermark.show {
    display: none !important;
    visibility: hidden !important;
    opacity: 0 !important;
}

.video-watermark .watermark-content {
    background: transparent;
}

/* S'assurer que le lecteur Plyr est visible */
.plyr-player-wrapper {
    position: absolute !important;
    top: 0 !important;
    left: 0 !important;
    width: 100% !important;
    height: 100% !important;
    z-index: 1 !important;
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
}

.plyr-player-wrapper .plyr {
    width: 100% !important;
    height: 100% !important;
    position: relative !important;
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
}

.plyr-player-wrapper .plyr__video-wrapper {
    width: 100% !important;
    height: 100% !important;
    position: relative !important;
    display: block !important;
}

.plyr-player-wrapper video.plyr-player-video,
.plyr-player-wrapper .plyr__video {
    width: 100% !important;
    height: 100% !important;
    object-fit: contain !important;
    position: relative !important;
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
}

.plyr-player-wrapper .plyr__poster {
    width: 100% !important;
    height: 100% !important;
    object-fit: contain !important;
    display: block !important;
}

/* S'assurer que les contrôles Plyr sont visibles */
.plyr-player-wrapper .plyr__controls {
    display: flex !important;
    visibility: visible !important;
}

@media (max-width: 768px) {
    .video-watermark {
        bottom: 10px;
        right: 10px;
        font-size: 0.7rem;
        padding: 0.5rem !important;
    }
}
</style>

@if($isYoutube || ($isInternalVideo && !empty($internalVideoUrl) && trim($internalVideoUrl) !== ''))
<div class="plyr-player-wrapper {{ $isYoutube ? 'plyr-external-video' : 'plyr-internal-video' }} position-absolute top-0 start-0 w-100 h-100" id="wrapper-{{ $playerId }}" style="margin: 0; padding: 0; width: 100% !important; height: 100% !important; min-width: 100% !important; min-height: 100% !important; max-width: 100% !important; max-height: 100% !important; overflow: hidden;">
    <!-- Watermark overlay dynamique - Désactivé -->
    {{-- @if(auth()->check() && !$isPreview)
    <div class="video-watermark position-absolute" id="watermark-{{ $playerId }}">
        <div class="watermark-content p-2 rounded">
            <small class="d-block fw-bold">{{ auth()->user()->name }}</small>
            <small class="d-block text-white-50">{{ auth()->user()->email }}</small>
            <small class="d-block text-white-50">Session: {{ Str::limit(session()->getId(), 8) }}</small>
        </div>
    </div>
    @endif --}}
    
    @if($isYoutube)
        <!-- Plyr Player Container pour YouTube -->
        <div class="plyr__video-embed" id="{{ $playerId }}" data-plyr-provider="youtube" data-plyr-embed-id="{{ $videoId }}" style="width: 100%; height: 100%; margin: 0; padding: 0;"></div>
    @else
        <!-- Plyr Player Container pour vidéo interne -->
        @if(!empty($internalVideoUrl) && trim($internalVideoUrl) !== '')
        <video id="{{ $playerId }}" class="plyr-player-video" playsinline controls preload="metadata" controlsList="nodownload" style="width: 100%; height: 100%; margin: 0; padding: 0;">
            <source src="{{ $internalVideoUrl }}" type="{{ $videoMimeType }}">
            Votre navigateur ne supporte pas la lecture vidéo.
        </video>
        @else
        <div class="d-flex flex-column align-items-center justify-content-center bg-dark text-white p-5 position-absolute top-0 start-0 w-100 h-100">
            <i class="fas fa-exclamation-triangle fa-3x mb-3 text-warning"></i>
            <p class="text-white mb-2">Erreur: URL de la vidéo introuvable</p>
            @if(config('app.debug'))
            <p class="text-muted small">Internal Video URL: {{ $internalVideoUrl ?? 'null' }}</p>
            <p class="text-muted small">File Path: {{ $filePath ?? 'null' }}</p>
            <p class="text-muted small">Content URL: {{ $contentUrlRaw ?? 'null' }}</p>
            @endif
        </div>
        @endif
    @endif
</div>
@else
<div class="text-center py-5 position-absolute top-0 start-0 w-100 h-100 d-flex flex-column align-items-center justify-content-center bg-dark" style="min-height: 450px;">
    <i class="fas fa-video fa-3x text-muted mb-3"></i>
    <p class="text-white mb-2">Aucune vidéo disponible pour cette leçon</p>
    <p class="text-muted small mb-2">Type de leçon: {{ $lesson->type ?? 'non défini' }}</p>
    @if(config('app.debug'))
    <div class="text-muted small mt-2 p-3 bg-dark rounded" style="font-size: 0.75rem; max-width: 90%; text-align: left;">
        <strong>Debug info:</strong><br>
        YouTube ID: {{ $videoId ?: 'vide' }}<br>
        File Path: {{ $filePath ?? 'vide' }}<br>
        Content URL: {{ $contentUrlRaw ?? 'vide' }}<br>
        Internal Video URL: {{ $internalVideoUrl ?? 'vide' }}<br>
        Is YouTube: {{ $isYoutube ? 'oui' : 'non' }}<br>
        Is Internal Video: {{ $isInternalVideo ? 'oui' : 'non' }}
    </div>
    @endif
</div>
@endif

@push('scripts')
@if($isYoutube || $isInternalVideo)
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
        
        // Debug logging
        if ({{ config('app.debug') ? 'true' : 'false' }}) {
            const videoElement = isInternalVideo && playerElement ? playerElement.querySelector('source') : null;
            console.log('Plyr Player Initialization:', {
                playerId: playerId,
                videoId: videoId,
                isYoutube: isYoutube,
                isInternalVideo: isInternalVideo,
                playerElement: playerElement ? 'found' : 'not found',
                wrapper: wrapper ? 'found' : 'not found',
                watermark: watermark ? 'found' : 'not found',
                lessonId: lessonId,
                courseSlug: courseSlug,
                videoSource: videoElement ? videoElement.src : 'no source element',
                videoType: videoElement ? videoElement.type : 'no type'
            });
        }
        
        // URLs pour les routes de progression
        const startLessonUrl = lessonId && courseSlug ? `/learning/courses/${courseSlug}/lessons/${lessonId}/start` : null;
        const updateProgressUrl = lessonId && courseSlug ? `/learning/courses/${courseSlug}/lessons/${lessonId}/progress` : null;
        
        // Variables pour le suivi de progression
        let lastProgressUpdateTime = 0; // Timestamp de la dernière mise à jour
        let lastVideoTime = 0; // Temps de la vidéo lors de la dernière mise à jour
        let hasStarted = false;
        let hasRestoredPosition = false; // Indique si la position a été restaurée
        
    if (!window.Plyr) {
        console.error('Plyr library not loaded');
        if (wrapper) {
            wrapper.innerHTML = '<div class="d-flex flex-column align-items-center justify-content-center bg-dark text-white p-5 position-absolute top-0 start-0 w-100 h-100"><i class="fas fa-exclamation-triangle fa-3x mb-3 text-warning"></i><p class="text-white mb-2">Erreur: La bibliothèque Plyr n\'est pas chargée</p><p class="text-muted small">Veuillez rafraîchir la page</p></div>';
        }
        return;
    }
    
    if (!playerElement) {
        console.error('Player element not found:', playerId);
        if (wrapper) {
            wrapper.innerHTML = '<div class="d-flex flex-column align-items-center justify-content-center bg-dark text-white p-5 position-absolute top-0 start-0 w-100 h-100"><i class="fas fa-exclamation-triangle fa-3x mb-3 text-warning"></i><p class="text-white mb-2">Erreur: Élément du lecteur introuvable</p></div>';
        }
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
            if (!playerElement) {
                console.error('Player element not found:', playerId);
                return null;
            }
            
            // Pour les vidéos internes, vérifier que la source existe
            if (isInternalVideo && playerElement.tagName === 'VIDEO') {
                const sourceElement = playerElement.querySelector('source');
                if (sourceElement) {
                    const videoSrc = sourceElement.getAttribute('src');
                    if ({{ config('app.debug') ? 'true' : 'false' }}) {
                        console.log('Video source URL:', videoSrc);
                    }
                    if (!videoSrc || videoSrc.trim() === '') {
                        console.error('Video source URL is empty');
                        if (wrapper) {
                            wrapper.innerHTML = '<div class="d-flex flex-column align-items-center justify-content-center bg-dark text-white p-5 position-absolute top-0 start-0 w-100 h-100"><i class="fas fa-exclamation-triangle fa-3x mb-3 text-warning"></i><p class="text-white mb-2">Erreur: URL de la vidéo introuvable</p><p class="text-muted small">Vérifiez que la leçon a bien un fichier vidéo</p></div>';
                        }
                        return null;
                    }
                } else {
                    console.error('Source element not found in video element');
                    if (wrapper) {
                        wrapper.innerHTML = '<div class="d-flex flex-column align-items-center justify-content-center bg-dark text-white p-5 position-absolute top-0 start-0 w-100 h-100"><i class="fas fa-exclamation-triangle fa-3x mb-3 text-warning"></i><p class="text-white mb-2">Erreur: Élément source introuvable</p></div>';
                    }
                    return null;
                }
            }
            
            player = new Plyr(playerElement, plyrConfig);
            
            // Vérifier que le lecteur est bien créé
            if (!player) {
                console.error('Failed to initialize Plyr player');
                return null;
            }
            
            if ({{ config('app.debug') ? 'true' : 'false' }}) {
                console.log('Plyr player initialized successfully');
            }
            
            // Forcer la visibilité du lecteur après l'initialisation
            if (player && player.media) {
                // S'assurer que le conteneur Plyr est visible
                const plyrContainer = player.media.closest('.plyr');
                if (plyrContainer) {
                    plyrContainer.style.width = '100%';
                    plyrContainer.style.height = '100%';
                    plyrContainer.style.position = 'relative';
                    plyrContainer.style.display = 'block';
                }
                
                // S'assurer que le wrapper vidéo est visible
                const videoWrapper = player.media.parentElement;
                if (videoWrapper) {
                    videoWrapper.style.width = '100%';
                    videoWrapper.style.height = '100%';
                    videoWrapper.style.position = 'relative';
                }
                
                // S'assurer que l'élément vidéo est visible
                if (player.media.tagName === 'VIDEO') {
                    player.media.style.width = '100%';
                    player.media.style.height = '100%';
                    player.media.style.display = 'block';
                    player.media.style.visibility = 'visible';
                    player.media.style.opacity = '1';
                }
            }
            
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
                    // Forcer la visibilité du lecteur après qu'il soit prêt
                    setTimeout(function() {
                        if (player && player.media) {
                            // S'assurer que le conteneur Plyr est visible
                            const plyrContainer = player.media.closest('.plyr');
                            if (plyrContainer) {
                                plyrContainer.style.width = '100%';
                                plyrContainer.style.height = '100%';
                                plyrContainer.style.position = 'relative';
                                plyrContainer.style.display = 'block';
                                plyrContainer.style.visibility = 'visible';
                                plyrContainer.style.opacity = '1';
                                plyrContainer.style.zIndex = '1';
                            }
                            
                            // S'assurer que le wrapper vidéo est visible
                            const videoWrapper = player.media.parentElement;
                            if (videoWrapper) {
                                videoWrapper.style.width = '100%';
                                videoWrapper.style.height = '100%';
                                videoWrapper.style.position = 'relative';
                                videoWrapper.style.display = 'block';
                            }
                            
                            // S'assurer que l'élément vidéo est visible
                            if (player.media.tagName === 'VIDEO') {
                                player.media.style.width = '100%';
                                player.media.style.height = '100%';
                                player.media.style.display = 'block';
                                player.media.style.visibility = 'visible';
                                player.media.style.opacity = '1';
                            }
                            
                            // S'assurer que le wrapper parent est visible
                            if (wrapper) {
                                wrapper.style.display = 'block';
                                wrapper.style.visibility = 'visible';
                                wrapper.style.opacity = '1';
                            }
                            
                            if ({{ config('app.debug') ? 'true' : 'false' }}) {
                                console.log('Player visibility forced');
                            }
                        }
                    }, 100);
                    
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
            
            // Observer pour s'assurer que le conteneur Plyr est visible dès qu'il est créé
            if (wrapper && player.media) {
                const observer = new MutationObserver(function(mutations) {
                    const plyrContainer = wrapper.querySelector('.plyr');
                    if (plyrContainer) {
                        plyrContainer.style.width = '100%';
                        plyrContainer.style.height = '100%';
                        plyrContainer.style.position = 'relative';
                        plyrContainer.style.display = 'block';
                        plyrContainer.style.visibility = 'visible';
                        plyrContainer.style.opacity = '1';
                        plyrContainer.style.zIndex = '1';
                    }
                });
                
                observer.observe(wrapper, {
                    childList: true,
                    subtree: true
                });
                
                // Arrêter l'observer après 5 secondes
                setTimeout(function() {
                    observer.disconnect();
                }, 5000);
            }
            
            // Gérer les erreurs de chargement pour les vidéos internes
            if (isInternalVideo && playerElement.tagName === 'VIDEO') {
                playerElement.addEventListener('error', function(e) {
                    const error = playerElement.error;
                    if (error) {
                        console.error('Video loading error:', {
                            code: error.code,
                            message: error.message,
                            networkState: playerElement.networkState,
                            readyState: playerElement.readyState,
                            src: playerElement.querySelector('source')?.src
                        });
                        
                        // Afficher un message d'erreur à l'utilisateur
                        if (wrapper) {
                            let errorMessage = 'Erreur lors du chargement de la vidéo';
                            switch(error.code) {
                                case 1: // MEDIA_ERR_ABORTED
                                    errorMessage = 'Le chargement de la vidéo a été annulé';
                                    break;
                                case 2: // MEDIA_ERR_NETWORK
                                    errorMessage = 'Erreur réseau lors du chargement de la vidéo';
                                    break;
                                case 3: // MEDIA_ERR_DECODE
                                    errorMessage = 'Erreur de décodage de la vidéo';
                                    break;
                                case 4: // MEDIA_ERR_SRC_NOT_SUPPORTED
                                    errorMessage = 'Format de vidéo non supporté';
                                    break;
                            }
                            wrapper.innerHTML = '<div class="d-flex flex-column align-items-center justify-content-center bg-dark text-white p-5 position-absolute top-0 start-0 w-100 h-100"><i class="fas fa-exclamation-triangle fa-3x mb-3 text-warning"></i><p class="text-white mb-2">' + errorMessage + '</p><p class="text-muted small">Code d\'erreur: ' + error.code + '</p></div>';
                        }
                    }
                });
                
                // Écouter aussi les événements de chargement pour le debug
                if ({{ config('app.debug') ? 'true' : 'false' }}) {
                    playerElement.addEventListener('loadstart', function() {
                        console.log('Video load started');
                    });
                    playerElement.addEventListener('loadedmetadata', function() {
                        console.log('Video metadata loaded');
                    });
                    playerElement.addEventListener('loadeddata', function() {
                        console.log('Video data loaded');
                    });
                    playerElement.addEventListener('canplay', function() {
                        console.log('Video can play');
                    });
                }
            }
            
        } catch (error) {
            console.error('Error initializing Plyr player:', error);
            if (wrapper) {
                wrapper.innerHTML = '<div class="d-flex flex-column align-items-center justify-content-center bg-dark text-white p-5 position-absolute top-0 start-0 w-100 h-100"><i class="fas fa-exclamation-triangle fa-3x mb-3 text-warning"></i><p class="text-white mb-2">Erreur lors de l\'initialisation du lecteur</p><p class="text-muted small">' + (error.message || 'Erreur inconnue') + '</p></div>';
            }
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
        
        // Watermark désactivé - Code commenté
        // Afficher le watermark si authentifié et si le lecteur est bien initialisé
        // if (watermark && !isPreview && isAuth && player) {
        //     player.on('ready', function() {
        //         setTimeout(() => {
        //             if (player && player.media) {
        //                 watermark.classList.add('show');
        //             }
        //         }, 1000);
        //     });
        // }
        
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
@endif
@endpush
