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
<div class="plyr-player-wrapper plyr-external-video position-absolute top-0 start-0 w-100 h-100" id="wrapper-{{ $playerId }}" style="margin: 0; padding: 0;">
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
    <div class="plyr__video-embed" id="{{ $playerId }}" data-plyr-provider="youtube" data-plyr-embed-id="{{ $videoId }}" style="width: 100%; height: 100%; margin: 0; padding: 0;"></div>
</div>
@else
<div class="text-center py-5">
    <i class="fas fa-video fa-3x text-muted mb-3"></i>
    <p class="text-muted">Aucune vidéo disponible</p>
</div>
@endif


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
