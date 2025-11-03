@props([
    'lesson',
    'course',
    'isMobile' => false
])

@php
    $containerId = $isMobile ? 'youtube-player-mobile' : 'youtube-player';
    $wrapperId = $isMobile ? 'youtube-wrapper-mobile' : 'youtube-wrapper';
@endphp

@if($lesson->isYoutubeVideo())
    <div class="youtube-player-container {{ $isMobile ? 'mobile-youtube' : '' }}" id="{{ $containerId }}">
        <div class="youtube-wrapper position-relative" id="{{ $wrapperId }}" 
             data-lesson-id="{{ $lesson->id }}" 
             data-course-id="{{ $course->id }}">
            
            <!-- Watermark overlay dynamique -->
            @if(auth()->check() && !$lesson->is_preview)
            <div class="video-watermark position-absolute" id="watermark-{{ $containerId }}">
                <div class="watermark-content p-2 rounded">
                    <small class="d-block fw-bold">{{ auth()->user()->name }}</small>
                    <small class="d-block text-white-50">{{ auth()->user()->email }}</small>
                    <small class="d-block text-white-50">Session: {{ Str::limit(session()->getId(), 8) }}</small>
                </div>
            </div>
            @endif
            
            <!-- Iframe YouTube - chargé dynamiquement après validation token -->
            <div id="youtube-iframe-container-{{ $containerId }}" 
                 class="youtube-iframe-container" 
                 style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; background: #000;">
                <iframe 
                    id="youtube-iframe-{{ $containerId }}"
                    class="position-absolute top-0 start-0 w-100 h-100"
                    frameborder="0" 
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                    allowfullscreen
                    src=""
                    loading="lazy">
                </iframe>
                
                <!-- Overlay pour masquer le logo YouTube et les infos de chaîne -->
                <div class="yt-top-overlay position-absolute w-100" style="top: 0; left: 0; height: 80px; background: linear-gradient(180deg, rgba(0,0,0,0.6) 0%, rgba(0,0,0,0) 100%); z-index: 10; pointer-events: none;"></div>
                
                <!-- Custom Controls (UI du site) -->
                <div class="yt-custom-controls d-flex align-items-center justify-content-between px-3 py-2">
                    <div class="d-flex align-items-center gap-2">
                        <button class="btn btn-sm btn-light-subtle text-white control-play" type="button" aria-label="Lire/Pause">
                            <i class="fas fa-play"></i>
                        </button>
                        <button class="btn btn-sm btn-light-subtle text-white control-mute ms-1" type="button" aria-label="Muet">
                            <i class="fas fa-volume-up"></i>
                        </button>
                        <div class="volume-wrapper ms-2">
                            <input type="range" min="0" max="100" value="100" class="form-range control-volume">
                        </div>
                        <div class="time ms-3">
                            <small class="current-time">0:00</small>
                            <small class="text-white-50"> / </small>
                            <small class="duration">0:00</small>
                        </div>
                    </div>
                    <div class="progress-wrapper flex-grow-1 mx-3">
                        <div class="progress bg-dark-subtle">
                            <div class="progress-bar bg-primary progress-played" role="progressbar" style="width: 0%"></div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button class="btn btn-sm btn-light-subtle text-white control-speed" type="button" aria-label="Vitesse">1x</button>
                        <button class="btn btn-sm btn-light-subtle text-white control-mini" type="button" aria-label="Mode compact">
                            <i class="fas fa-compress-alt"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Loading spinner -->
            <div class="youtube-loading position-absolute top-50 start-50 translate-middle" id="youtube-loading-{{ $containerId }}">
                <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                    <span class="visually-hidden">Chargement de la vidéo...</span>
                </div>
            </div>
            
            <!-- Error message -->
            <div class="youtube-error d-none position-absolute top-50 start-50 translate-middle" id="youtube-error-{{ $containerId }}" style="width: 90%;">
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <p class="mb-0">Erreur de chargement de la vidéo. Veuillez réessayer.</p>
                </div>
            </div>
        </div>
    </div>
@else
    <div class="text-center py-5">
        <i class="fas fa-video fa-3x text-muted mb-3"></i>
        <p class="text-muted">Aucune vidéo YouTube disponible pour cette leçon</p>
    </div>
@endif

@push('styles')
<style>
.youtube-player-container {
    position: relative;
    width: 100%;
    background: #000;
    border-radius: 8px;
    overflow: hidden;
}

.youtube-wrapper {
    width: 100%;
}

.video-watermark {
    bottom: 20px;
    right: 20px;
    background: rgba(0, 0, 0, 0.8) !important;
    color: white;
    z-index: 10;
    display: none;
    backdrop-filter: blur(5px);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.video-watermark.show {
    display: block;
}

.youtube-loading {
    z-index: 5;
}

.youtube-error {
    z-index: 10;
}

/* Custom controls */
.yt-custom-controls {
    position: absolute;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(180deg, rgba(0,0,0,0) 0%, rgba(0,0,0,0.75) 60%);
    color: white;
    z-index: 8;
}

.yt-custom-controls .btn.btn-light-subtle {
    background: rgba(255,255,255,0.08);
    border: 1px solid rgba(255,255,255,0.1);
}

.yt-custom-controls .btn i { pointer-events: none; }

.yt-custom-controls .progress { height: 6px; cursor: pointer; }
.yt-custom-controls .progress .progress-bar { transition: width 0.2s ease; }

.yt-custom-controls .form-range.control-volume {
    width: 100px;
    accent-color: #0d6efd;
}

/* Overlay pour bloquer les clics sur les éléments natifs YouTube */
.yt-overlay-blocker {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 80px;
    background: transparent;
    z-index: 99;
    pointer-events: none;
}

/* Masquer les éléments natifs YouTube au survol */
.youtube-player-container:hover .yt-overlay-blocker {
    pointer-events: all;
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
@endpush

@push('scripts')
<script>
(function() {
    'use strict';
    
    const containerId = '{{ $containerId }}';
    const wrapper = document.getElementById('{{ $wrapperId }}');
    const iframeContainer = document.getElementById('youtube-iframe-container-{{ $containerId }}');
    const iframe = document.getElementById('youtube-iframe-{{ $containerId }}');
    const loading = document.getElementById('youtube-loading-{{ $containerId }}');
    const error = document.getElementById('youtube-error-{{ $containerId }}');
    const watermark = document.getElementById('watermark-{{ $containerId }}');
    
    const lessonId = {{ $lesson->id }};
    const courseId = {{ $course->id }};
    
    if (!wrapper) return;
    
    // Fonction pour charger la vidéo YouTube sécurisée
    async function loadYouTubeVideo() {
        try {
            loading.classList.remove('d-none');
            error.classList.add('d-none');
            if (iframeContainer) iframeContainer.classList.add('d-none');
            
            const isPreview = {{ $lesson->is_preview ? 'true' : 'false' }};
            const isAuth = {{ auth()->check() ? 'true' : 'false' }};
            
            let embedUrl;
            const baseParams = {
                rel: 0,
                modestbranding: 1,
                iv_load_policy: 3,
                origin: window.location.origin,
                playsinline: 1,
                controls: 0,
                fs: 0,
                disablekb: 1,
                cc_load_policy: 0,
                autoplay: 0,
                showinfo: 0,
                mute: 0,
                loop: 0,
                start: 0,
                end: 0,
                playlist: '',
                widget_referrer: window.location.origin
            };
            
            // Si c'est une prévisualisation (lesson.is_preview ou pas d'auth), charger directement
            if (isPreview || !isAuth) {
                // URL YouTube directe pour preview (pas de token)
                const videoId = '{{ $lesson->youtube_video_id }}';
                const qp = new URLSearchParams(baseParams).toString();
                embedUrl = `https://www.youtube.com/embed/${videoId}?${qp}`;
            } else {
                // Générer un token d'accès pour les utilisateurs authentifiés
                const lessonId = {{ $lesson->id }};
                if (lessonId && lessonId > 0) {
                    try {
                        @php
                            // Générer l'URL de route seulement si l'ID est valide
                            if ($lesson->id > 0) {
                                $tokenUrl = route('video.generate-access-token', ['lesson' => $lesson->id]);
                            } else {
                                $tokenUrl = '#';
                            }
                        @endphp
                        
                        const response = await fetch('{{ $tokenUrl }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            credentials: 'same-origin'
                        });
                        
                        if (!response.ok) {
                            throw new Error('Échec de la génération du token');
                        }
                        
                        const data = await response.json();
                        
                        if (!data.success || !data.embed_url) {
                            throw new Error('Token invalide ou URL manquante');
                        }
                        
                        embedUrl = data.embed_url;
                        
                        // Afficher le watermark après un court délai
                        if (watermark && data.user_info) {
                            setTimeout(() => {
                                watermark.classList.add('show');
                            }, 1000);
                        }
                    } catch (err) {
                        console.error('Error generating token, using direct URL:', err);
                        // Fallback: URL directe en cas d'erreur
                        const videoId = '{{ $lesson->youtube_video_id }}';
                        const qp = new URLSearchParams(baseParams).toString();
                        embedUrl = `https://www.youtube.com/embed/${videoId}?${qp}`;
                    }
                } else {
                    // Fallback: URL directe si pas d'ID de leçon valide
                    const videoId = '{{ $lesson->youtube_video_id }}';
                    const qp = new URLSearchParams(baseParams).toString();
                    embedUrl = `https://www.youtube.com/embed/${videoId}?${qp}`;
                }
            }
            
            // Injecter l'iframe YouTube
            if (iframe && embedUrl) {
                iframe.src = embedUrl;
            }
            
            // Afficher le conteneur
            if (iframeContainer) {
                iframeContainer.classList.remove('d-none');
            }
            loading.classList.add('d-none');
            
            // Initialiser API et contrôles personnalisés
            setupCustomControls();
            
            console.log('YouTube video loaded successfully');
            
        } catch (err) {
            console.error('Error loading YouTube video:', err);
            if (loading) loading.classList.add('d-none');
            if (error) error.classList.remove('d-none');
        }
    }
    
    // YouTube API Loader (singleton)
    function ensureYouTubeAPI() {
        return new Promise((resolve) => {
            if (window.YT && window.YT.Player) {
                resolve();
                return;
            }
            const prev = document.querySelector('script[src="https://www.youtube.com/iframe_api"]');
            if (!prev) {
                const tag = document.createElement('script');
                tag.src = 'https://www.youtube.com/iframe_api';
                document.head.appendChild(tag);
            }
            window.onYouTubeIframeAPIReady = function() {
                resolve();
            };
        });
    }

    function parseVideoId(url) {
        try {
            const m = url.match(/\/embed\/([a-zA-Z0-9_-]{11})/);
            return m ? m[1] : null;
        } catch (_) { return null; }
    }

    function formatTime(secs) {
        secs = Math.max(0, Math.floor(secs || 0));
        const m = Math.floor(secs / 60);
        const s = secs % 60;
        return `${m}:${s.toString().padStart(2, '0')}`;
    }

    async function setupCustomControls() {
        const controlsBar = iframeContainer?.querySelector('.yt-custom-controls');
        if (!controlsBar) return;
        await ensureYouTubeAPI();
        const videoId = parseVideoId(iframe.src);
        if (!videoId) return;
        
        let player;
        const playBtn = controlsBar.querySelector('.control-play');
        const muteBtn = controlsBar.querySelector('.control-mute');
        const volumeRange = controlsBar.querySelector('.control-volume');
        const speedBtn = controlsBar.querySelector('.control-speed');
        const progress = controlsBar.querySelector('.progress');
        const progressBar = controlsBar.querySelector('.progress-played');
        const currentEl = controlsBar.querySelector('.current-time');
        const durationEl = controlsBar.querySelector('.duration');
        
        // Remplacer l'iframe par un lecteur API tout en conservant l'ID
        player = new YT.Player(iframe.id, {
            videoId: videoId,
            playerVars: {
                rel: 0,
                modestbranding: 1,
                iv_load_policy: 3,
                origin: window.location.origin,
                playsinline: 1,
                controls: 0,
                fs: 0,
                disablekb: 1,
                cc_load_policy: 0,
                autoplay: 0,
                showinfo: 0,
                mute: 0,
                loop: 0,
                start: 0,
                end: 0,
                playlist: '',
                widget_referrer: window.location.origin,
                enablejsapi: 1
            },
            events: {
                onReady: onReady,
                onStateChange: onStateChange
            }
        });
        
        let duration = 0;
        let updateTimer = null;
        
        function onReady() {
            try {
                duration = player.getDuration() || 0;
                durationEl.textContent = formatTime(duration);
                updateTimer = setInterval(() => {
                    const ct = player.getCurrentTime() || 0;
                    currentEl.textContent = formatTime(ct);
                    if (duration > 0) {
                        const pct = (ct / duration) * 100;
                        progressBar.style.width = pct + '%';
                    }
                }, 250);
                
                // Injecter du CSS pour cacher les overlays natifs YouTube
                setTimeout(() => {
                    injectHideOverlaysCSS();
                }, 1000);
            } catch (e) {}
        }
        
        function injectHideOverlaysCSS() {
            // Comme l'iframe est cross-origin, on ne peut pas injecter de CSS directement
            // Mais on peut utiliser l'API YouTube pour cacher les overlays
            try {
                // Masquer les overlays via l'API si disponible
                if (player && player.getIframe) {
                    const iframeElement = player.getIframe();
                    if (iframeElement) {
                        // Créer un overlay personnalisé pour bloquer les clics sur les logos
                        const blocker = document.createElement('div');
                        blocker.className = 'yt-overlay-blocker';
                        blocker.style.cssText = `
                            position: absolute;
                            top: 0;
                            left: 0;
                            right: 0;
                            height: 80px;
                            background: transparent;
                            z-index: 9;
                            pointer-events: none;
                        `;
                        iframeContainer.appendChild(blocker);
                    }
                }
            } catch (e) {
                console.warn('Could not inject overlay blocker:', e);
            }
        }
        
        function onStateChange(e) {
            const ic = playBtn.querySelector('i');
            if (!ic) return;
            if (e.data === YT.PlayerState.PLAYING) {
                ic.classList.remove('fa-play');
                ic.classList.add('fa-pause');
            } else {
                ic.classList.remove('fa-pause');
                ic.classList.add('fa-play');
            }
        }
        
        playBtn.addEventListener('click', () => {
            const state = player.getPlayerState();
            if (state === YT.PlayerState.PLAYING) player.pauseVideo();
            else player.playVideo();
        });
        
        muteBtn.addEventListener('click', () => {
            const ic = muteBtn.querySelector('i');
            if (player.isMuted()) {
                player.unMute();
                ic.classList.remove('fa-volume-mute');
                ic.classList.add('fa-volume-up');
            } else {
                player.mute();
                ic.classList.remove('fa-volume-up');
                ic.classList.add('fa-volume-mute');
            }
        });
        
        volumeRange.addEventListener('input', (e) => {
            const v = parseInt(e.target.value, 10) || 0;
            player.setVolume(v);
            if (v === 0) {
                if (!player.isMuted()) player.mute();
            } else if (player.isMuted()) {
                player.unMute();
            }
        });
        
        speedBtn.addEventListener('click', () => {
            const rates = [1, 1.25, 1.5, 1.75, 2];
            const cur = player.getPlaybackRate();
            const idx = rates.indexOf(cur);
            const next = rates[(idx + 1) % rates.length];
            player.setPlaybackRate(next);
            speedBtn.textContent = next + 'x';
        });
        
        progress.addEventListener('click', (e) => {
            const rect = progress.getBoundingClientRect();
            const ratio = Math.min(1, Math.max(0, (e.clientX - rect.left) / rect.width));
            if (duration > 0) player.seekTo(duration * ratio, true);
        });
    }

    // Charger la vidéo au chargement de la page
    loadYouTubeVideo();
})();
</script>
@endpush
