@props([
    'lesson',
    'course',
    'isMobile' => false
])

@php
    $playerId = ($isMobile ? 'plyr-mobile-' : 'plyr-player-') . ($lesson->id ?? 'preview');
    $videoId = $lesson->youtube_video_id ?? '';
    $isPreview = $lesson->is_preview ?? false;
@endphp

@if(!empty($videoId))
<div class="plyr-player-wrapper position-absolute top-0 start-0 w-100 h-100" id="wrapper-{{ $playerId }}">
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
    
    <!-- Plyr Player Container -->
    <div class="plyr__video-embed" id="{{ $playerId }}" data-plyr-provider="youtube" data-plyr-embed-id="{{ $videoId }}"></div>
</div>
@else
<div class="text-center py-5">
    <i class="fas fa-video fa-3x text-muted mb-3"></i>
    <p class="text-muted">Aucune vidéo YouTube disponible</p>
</div>
@endif

@push('styles')
<style>
/* Variables Plyr personnalisées selon la charte graphique */
:root {
    --plyr-color-main: #ffcc33;
    --plyr-video-background: #000;
    --plyr-video-controls-background: linear-gradient(to top, rgba(0, 51, 102, 0.95) 0%, rgba(0, 51, 102, 0.7) 50%, transparent 100%);
    --plyr-video-control-color: #ffffff;
    --plyr-video-control-color-hover: #ffcc33;
    --plyr-audio-controls-background: rgba(0, 51, 102, 0.95);
    --plyr-audio-control-color: #ffffff;
    --plyr-menu-background: rgba(0, 51, 102, 0.95);
    --plyr-menu-color: #ffffff;
    --plyr-menu-shadow: 0 4px 16px rgba(0, 0, 0, 0.5);
    --plyr-tooltip-background: rgba(0, 51, 102, 0.95);
    --plyr-tooltip-color: #ffffff;
    --plyr-progress-loading-background: rgba(255, 255, 255, 0.25);
    --plyr-progress-buffered-background: rgba(255, 255, 255, 0.4);
    --plyr-range-thumb-background: #ffcc33;
    --plyr-range-thumb-active-shadow-width: 0 0 12px rgba(255, 204, 51, 0.6);
    --plyr-range-track-height: 6px;
    --plyr-range-thumb-height: 18px;
    --plyr-range-thumb-width: 18px;
    --plyr-control-icon-size: 20px;
    --plyr-control-spacing: 10px;
    --plyr-control-radius: 50%;
    --plyr-control-padding: 8px;
    --plyr-video-controls-padding: 15px 10px;
}

/* Wrapper du lecteur */
.plyr-player-wrapper {
    width: 100%;
    height: 100%;
    position: relative;
    background: #000;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0, 51, 102, 0.3);
    border: 2px solid rgba(0, 51, 102, 0.2);
    transition: box-shadow 0.3s ease, border-color 0.3s ease;
}

.plyr-player-wrapper:hover {
    box-shadow: 0 6px 30px rgba(0, 51, 102, 0.4);
    border-color: rgba(255, 204, 51, 0.3);
}

/* Désactiver le menu contextuel pour empêcher le téléchargement */
.plyr-player-wrapper,
.plyr-player-wrapper * {
    -webkit-touch-callout: none;
    -webkit-user-select: none;
    -khtml-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
}

/* Styles Plyr personnalisés */
.plyr {
    width: 100%;
    height: 100%;
    border-radius: 8px;
    overflow: hidden;
}

.plyr__video-wrapper {
    background: #000;
}

/* Contrôles Plyr personnalisés */
.plyr__controls {
    background: var(--plyr-video-controls-background) !important;
    backdrop-filter: blur(10px);
    padding: var(--plyr-video-controls-padding) !important;
}

.plyr__control {
    background: rgba(0, 51, 102, 0.95) !important;
    border: 2px solid rgba(255, 255, 255, 0.3) !important;
    color: white !important;
    border-radius: 50% !important;
    width: 40px !important;
    height: 40px !important;
    transition: all 0.3s ease !important;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3) !important;
}

.plyr__control:hover {
    background: var(--plyr-color-main) !important;
    color: #003366 !important;
    border-color: var(--plyr-color-main) !important;
    transform: scale(1.15) !important;
    box-shadow: 0 4px 12px rgba(255, 204, 51, 0.5) !important;
}

.plyr__control:focus {
    outline: none !important;
    box-shadow: 0 0 0 3px rgba(255, 204, 51, 0.3) !important;
}

.plyr__control[aria-pressed="true"] {
    background: var(--plyr-color-main) !important;
    color: #003366 !important;
}

/* Barre de progression */
.plyr__progress__container {
    height: 6px !important;
    cursor: pointer;
}

.plyr__progress__container:hover {
    height: 8px !important;
}

.plyr__progress__buffer {
    background: rgba(255, 255, 255, 0.4) !important;
}

.plyr__progress__played {
    background: linear-gradient(90deg, var(--plyr-color-main) 0%, #ff9933 100%) !important;
    box-shadow: 0 0 8px rgba(255, 204, 51, 0.5) !important;
}

.plyr__progress__container:hover .plyr__progress__played {
    box-shadow: 0 0 12px rgba(255, 204, 51, 0.7) !important;
}

/* Curseur de progression */
.plyr__progress__container::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    transform: translate(-50%, -50%);
    width: 18px;
    height: 18px;
    background: var(--plyr-color-main);
    border: 3px solid white;
    border-radius: 50%;
    opacity: 0;
    transition: opacity 0.2s ease, transform 0.2s ease;
    box-shadow: 0 2px 8px rgba(0, 51, 102, 0.5), 0 0 12px rgba(255, 204, 51, 0.6);
    pointer-events: none;
}

.plyr__progress__container:hover::before {
    opacity: 1;
    transform: translate(-50%, -50%) scale(1.3);
}

/* Volume */
.plyr__volume {
    max-width: 90px;
}

.plyr__volume input[type="range"] {
    color: var(--plyr-color-main) !important;
}

.plyr__volume input[type="range"]::-webkit-slider-thumb {
    background: var(--plyr-color-main) !important;
    border: 2px solid white !important;
    box-shadow: 0 2px 6px rgba(0, 51, 102, 0.4) !important;
}

.plyr__volume input[type="range"]::-moz-range-thumb {
    background: var(--plyr-color-main) !important;
    border: 2px solid white !important;
    box-shadow: 0 2px 6px rgba(0, 51, 102, 0.4) !important;
}

/* Temps */
.plyr__time {
    color: white !important;
    font-weight: 600 !important;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.8) !important;
    letter-spacing: 0.5px !important;
}

/* Menu (qualité, etc.) */
.plyr__menu {
    background: var(--plyr-menu-background) !important;
    border: 2px solid var(--plyr-color-main) !important;
    border-radius: 8px !important;
    box-shadow: var(--plyr-menu-shadow) !important;
    backdrop-filter: blur(10px) !important;
}

.plyr__menu__container button {
    color: white !important;
    transition: all 0.2s ease !important;
}

.plyr__menu__container button:hover,
.plyr__menu__container button[aria-checked="true"] {
    background: rgba(255, 204, 51, 0.15) !important;
    color: var(--plyr-color-main) !important;
}

.plyr__menu__container button[aria-checked="true"] {
    background: rgba(255, 204, 51, 0.25) !important;
    font-weight: 700 !important;
    border-left: 3px solid var(--plyr-color-main) !important;
}

/* Tooltip */
.plyr__tooltip {
    background: var(--plyr-tooltip-background) !important;
    color: var(--plyr-tooltip-color) !important;
    border: 1px solid var(--plyr-color-main) !important;
}

/* Bouton plein écran */
.plyr__control[data-plyr="fullscreen"] {
    background: rgba(0, 51, 102, 0.95) !important;
}

.plyr__control[data-plyr="fullscreen"]:hover {
    background: var(--plyr-color-main) !important;
}

/* Watermark */
.video-watermark {
    bottom: 20px;
    right: 20px;
    background: rgba(0, 51, 102, 0.95) !important;
    color: white;
    z-index: 10;
    display: none;
    backdrop-filter: blur(10px);
    border: 2px solid var(--plyr-color-main);
    border-radius: 8px;
    font-size: 0.75rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5);
}

.video-watermark.show {
    display: block;
}

.watermark-content {
    background: transparent;
}

/* Responsive */
@media (max-width: 768px) {
    .plyr__control {
        width: 36px !important;
        height: 36px !important;
    }
    
    .plyr__time {
        font-size: 0.75rem !important;
    }
    
    .plyr__controls {
        padding: 12px 8px !important;
    }
    
    .video-watermark {
        bottom: 10px;
        right: 10px;
        font-size: 0.7rem;
        padding: 0.5rem !important;
    }
}

/* Video Player Container */
.plyr-player-container {
    width: 100%;
    height: 100%;
    display: block !important;
    visibility: visible !important;
    position: relative;
    background: #000;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0, 51, 102, 0.3);
    border: 2px solid rgba(0, 51, 102, 0.2);
    transition: box-shadow 0.3s ease, border-color 0.3s ease;
}

.plyr-player-container:hover {
    box-shadow: 0 6px 30px rgba(0, 51, 102, 0.4);
    border-color: rgba(255, 204, 51, 0.3);
}

/* Désactiver le menu contextuel pour empêcher le téléchargement */
.plyr-player-container,
.plyr-player-container * {
    -webkit-touch-callout: none;
    -webkit-user-select: none;
    -khtml-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
}

.plyr-player-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 999;
    pointer-events: none;
}

.video-wrapper {
    width: 100%;
    height: 100%;
}

.youtube-iframe-container {
    width: 100%;
    height: 100%;
    pointer-events: none;
}

/* Désactiver le menu contextuel sur l'iframe */
.youtube-iframe-container iframe {
    pointer-events: none;
}

.video-watermark {
    bottom: 20px;
    right: 20px;
    background: var(--plyr-bg-dark) !important;
    color: white;
    z-index: 10;
    display: none;
    backdrop-filter: blur(10px);
    border: 2px solid var(--plyr-accent-color);
    border-radius: 8px;
    font-size: 0.75rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5);
}

.video-watermark.show {
    display: block;
}

.watermark-content {
    background: transparent;
}

.plyr-loading {
    z-index: 5;
}

.plyr-loading .spinner-border {
    color: var(--plyr-accent-color) !important;
    border-width: 3px;
}

/* Contrôles personnalisés */
.custom-video-controls {
    top: 0;
    left: 0;
    z-index: 100;
    opacity: 0;
    transition: opacity 0.3s ease;
    pointer-events: none;
}

.plyr-player-container:hover .custom-video-controls {
    opacity: 1;
    pointer-events: auto;
}

.video-progress-container {
    z-index: 102;
}

.video-controls-bottom {
    background: linear-gradient(to top, var(--plyr-bg-dark) 0%, var(--plyr-bg-light) 50%, transparent 100%);
    padding: 15px 10px;
    z-index: 101;
    backdrop-filter: blur(10px);
}

/* Progress bar - Style charte graphique */
.video-progress-bar {
    position: relative;
    height: 6px;
    cursor: pointer;
    transition: height 0.2s ease;
    border-radius: 3px;
    background: rgba(255, 255, 255, 0.2);
}

.video-progress-bar:hover {
    height: 8px;
}

.video-progress-track {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.25);
    border-radius: 3px;
    pointer-events: none;
}

.video-progress-buffered {
    position: absolute;
    top: 0;
    left: 0;
    height: 100%;
    background: rgba(255, 255, 255, 0.4);
    border-radius: 3px;
    width: 0%;
    pointer-events: none;
}

.video-progress-filled {
    position: absolute;
    top: 0;
    left: 0;
    height: 100%;
    background: linear-gradient(90deg, var(--plyr-accent-color) 0%, #ff9933 100%);
    border-radius: 3px;
    width: 0%;
    transition: width 0.1s linear;
    pointer-events: none;
    box-shadow: 0 0 8px rgba(255, 204, 51, 0.5);
}

.video-progress-handle {
    position: absolute;
    top: 50%;
    left: 0%;
    transform: translate(-50%, -50%);
    width: 18px;
    height: 18px;
    background: var(--plyr-accent-color);
    border: 3px solid white;
    border-radius: 50%;
    opacity: 0;
    transition: opacity 0.2s ease, transform 0.2s ease;
    box-shadow: 0 2px 8px rgba(0, 51, 102, 0.5), 0 0 12px rgba(255, 204, 51, 0.6);
    pointer-events: auto;
}

.video-progress-bar:hover .video-progress-handle {
    opacity: 1;
    transform: translate(-50%, -50%) scale(1.3);
}

.control-btn {
    border-radius: 50%;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid rgba(255, 255, 255, 0.3);
    background: var(--plyr-bg-dark);
    color: white;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

.control-btn:hover {
    background: var(--plyr-accent-color);
    color: var(--plyr-primary-color);
    border-color: var(--plyr-accent-color);
    transform: scale(1.15);
    box-shadow: 0 4px 12px rgba(255, 204, 51, 0.5);
}

.control-btn:active {
    transform: scale(1.05);
}

.control-btn i {
    font-size: 0.9rem;
}

.video-time {
    font-size: 0.875rem;
    font-weight: 600;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.8);
    color: white;
    letter-spacing: 0.5px;
}

/* Volume control - Style charte graphique */
.volume-control {
    position: relative;
}

.volume-slider-container {
    width: 0;
    overflow: hidden;
    transition: width 0.3s ease;
}

.volume-control:hover .volume-slider-container {
    width: 90px;
}

.volume-slider {
    position: relative;
    width: 90px;
    height: 5px;
    cursor: pointer;
    margin: 0 12px;
}

.volume-slider-track {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.25);
    border-radius: 3px;
}

.volume-slider-fill {
    position: absolute;
    top: 0;
    left: 0;
    height: 100%;
    background: linear-gradient(90deg, var(--plyr-accent-color) 0%, #ff9933 100%);
    border-radius: 3px;
    width: 100%;
    transition: width 0.1s linear;
    box-shadow: 0 0 6px rgba(255, 204, 51, 0.4);
}

.volume-slider-handle {
    position: absolute;
    top: 50%;
    left: 0;
    transform: translate(-50%, -50%);
    width: 14px;
    height: 14px;
    background: var(--plyr-accent-color);
    border: 2px solid white;
    border-radius: 50%;
    opacity: 0;
    transition: opacity 0.2s ease;
    box-shadow: 0 2px 6px rgba(0, 51, 102, 0.4);
}

.volume-slider:hover .volume-slider-handle {
    opacity: 1;
}

/* Quality dropdown - Style charte graphique */
.quality-dropdown .dropdown-toggle {
    border: none;
}

.quality-dropdown .dropdown-toggle::after {
    display: none;
}

.quality-dropdown .dropdown-menu {
    background: var(--plyr-bg-dark);
    border: 2px solid var(--plyr-accent-color);
    min-width: 120px;
    border-radius: 8px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(10px);
    padding: 0.5rem 0;
}

.quality-dropdown .dropdown-item {
    color: white;
    padding: 0.65rem 1.25rem;
    transition: all 0.2s ease;
    font-size: 0.875rem;
}

.quality-dropdown .dropdown-item:hover {
    background: rgba(255, 204, 51, 0.15);
    color: var(--plyr-accent-color);
    padding-left: 1.5rem;
}

.quality-dropdown .dropdown-item.active {
    background: rgba(255, 204, 51, 0.25);
    color: var(--plyr-accent-color);
    font-weight: 700;
    border-left: 3px solid var(--plyr-accent-color);
}

/* Responsive styles */
@media (max-width: 768px) {
    .video-watermark {
        bottom: 10px;
        right: 10px;
        font-size: 0.7rem;
        padding: 0.5rem !important;
    }
    
    .control-btn {
        width: 36px;
        height: 36px;
    }
    
    .control-btn i {
        font-size: 0.8rem;
    }
    
    .video-time {
        font-size: 0.75rem;
    }
    
    .video-controls-bottom {
        padding: 12px 8px;
    }
    
    .video-progress-bar {
        height: 5px;
    }
    
    .video-progress-bar:hover {
        height: 6px;
    }
}
</style>
@endpush

@push('scripts')
<script>
(function() {
    'use strict';
    
    const playerId = '{{ $playerId }}';
    const videoId = '{{ $videoId }}';
    const playerElement = document.getElementById('{{ $playerId }}');
    const wrapper = document.getElementById('wrapper-{{ $playerId }}');
    const watermark = document.getElementById('watermark-{{ $playerId }}');
    const isPreview = {{ $isPreview ? 'true' : 'false' }};
    const isAuth = {{ auth()->check() ? 'true' : 'false' }};
    
    if (!playerElement || !window.Plyr) {
        console.warn('Plyr not available or element not found');
        return;
    }
    
    // Fonction pour initialiser Plyr
    function initPlyrPlayer() {
        const player = new Plyr(playerElement, {
            youtube: {
                noCookie: false,
                rel: 0,
                showinfo: 0,
                iv_load_policy: 3,
                modestbranding: 1,
                controls: 0,
                disablekb: 1,
                fs: 0,
                cc_load_policy: 0
            },
            controls: ['play-large', 'play', 'progress', 'current-time', 'mute', 'volume', 'settings', 'fullscreen'],
            settings: ['quality', 'speed'],
            keyboard: { focused: true, global: false },
            tooltips: { controls: true, seek: true },
            clickToPlay: true,
            hideControls: true,
            resetOnEnd: false,
            disableContextMenu: true
        });
        
        // Sauvegarder la référence
        window['plyr_' + playerId] = player;
        
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
})();
</script>
@endpush
