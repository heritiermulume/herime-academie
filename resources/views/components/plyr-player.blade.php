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
<div class="plyr-player-container position-absolute top-0 start-0 w-100 h-100" id="container-{{ $playerId }}" style="background: #000; border-radius: 8px; overflow: hidden; display: block !important; visibility: visible !important;">
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
    
    <!-- Video wrapper -->
    <div class="video-wrapper position-relative w-100 h-100" id="video-wrapper-{{ $playerId }}">
        <!-- YouTube iframe (non-cliquable) -->
        <div id="{{ $playerId }}" class="youtube-iframe-container"></div>
        
        <!-- Custom controls overlay -->
        <div class="custom-video-controls position-absolute w-100 h-100 d-flex flex-column" id="controls-{{ $playerId }}">
            <!-- Progress bar -->
            <div class="video-progress-container position-absolute w-100" id="progress-container-{{ $playerId }}" style="bottom: 60px;">
                <div class="video-progress-bar mx-3" id="progress-bar-{{ $playerId }}">
                    <div class="video-progress-track"></div>
                    <div class="video-progress-buffered" id="progress-buffered-{{ $playerId }}"></div>
                    <div class="video-progress-filled" id="progress-filled-{{ $playerId }}"></div>
                    <div class="video-progress-handle" id="progress-handle-{{ $playerId }}"></div>
                </div>
            </div>
            
            <!-- Bottom controls -->
            <div class="video-controls-bottom position-absolute w-100 d-flex align-items-center justify-content-between px-3" id="controls-bottom-{{ $playerId }}" style="bottom: 0;">
                <div class="d-flex align-items-center gap-3">
                    <!-- Play/Pause button -->
                    <button class="btn btn-light btn-sm control-btn" id="play-btn-{{ $playerId }}" title="Lecture">
                        <i class="fas fa-play"></i>
                    </button>
                    <!-- Time display -->
                    <span class="text-white video-time" id="time-{{ $playerId }}">00:00 / 00:00</span>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <!-- Volume control -->
                    <div class="volume-control d-flex align-items-center">
                        <button class="btn btn-light btn-sm control-btn" id="mute-btn-{{ $playerId }}" title="Son">
                            <i class="fas fa-volume-up"></i>
                        </button>
                        <div class="volume-slider-container" id="volume-container-{{ $playerId }}">
                            <div class="volume-slider" id="volume-slider-{{ $playerId }}">
                                <div class="volume-slider-track"></div>
                                <div class="volume-slider-fill" id="volume-fill-{{ $playerId }}"></div>
                                <div class="volume-slider-handle" id="volume-handle-{{ $playerId }}"></div>
                            </div>
                        </div>
                    </div>
                    <!-- Quality button -->
                    <div class="quality-dropdown dropdown">
                        <button class="btn btn-light btn-sm control-btn dropdown-toggle" id="quality-btn-{{ $playerId }}" data-bs-toggle="dropdown" title="Qualité">
                            <i class="fas fa-cog"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" id="quality-menu-{{ $playerId }}">
                            <li><a class="dropdown-item" href="#" data-quality="auto">Auto</a></li>
                        </ul>
                    </div>
                    <!-- Fullscreen button -->
                    <button class="btn btn-light btn-sm control-btn" id="fullscreen-btn-{{ $playerId }}" title="Plein écran">
                        <i class="fas fa-expand"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Loading spinner -->
    <div class="plyr-loading position-absolute top-50 start-50 translate-middle" id="loading-{{ $playerId }}">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">Chargement de la vidéo...</span>
        </div>
    </div>
</div>
@else
<div class="text-center py-5">
    <i class="fas fa-video fa-3x text-muted mb-3"></i>
    <p class="text-muted">Aucune vidéo YouTube disponible</p>
</div>
@endif

@push('styles')
<style>
/* Video Player Container */
.plyr-player-container {
    width: 100%;
    height: 100%;
    display: block !important;
    visibility: visible !important;
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

.video-watermark {
    bottom: 20px;
    right: 20px;
    background: rgba(0, 0, 0, 0.8) !important;
    color: white;
    z-index: 10;
    display: none;
    backdrop-filter: blur(5px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    font-size: 0.75rem;
}

.video-watermark.show {
    display: block;
}

.plyr-loading {
    z-index: 5;
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
    background: linear-gradient(to top, rgba(0,51,102,0.9) 0%, rgba(0,51,102,0.7) 50%, transparent 100%);
    padding: 15px 10px;
    z-index: 101;
}

/* Progress bar */
.video-progress-bar {
    position: relative;
    height: 5px;
    cursor: pointer;
    transition: height 0.2s ease;
}

.video-progress-bar:hover {
    height: 7px;
}

.video-progress-track {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255,255,255,0.3);
    border-radius: 3px;
    pointer-events: none;
}

.video-progress-buffered {
    position: absolute;
    top: 0;
    left: 0;
    height: 100%;
    background: rgba(255,255,255,0.5);
    border-radius: 3px;
    width: 0%;
    pointer-events: none;
}

.video-progress-filled {
    position: absolute;
    top: 0;
    left: 0;
    height: 100%;
    background: #ffcc33;
    border-radius: 3px;
    width: 0%;
    transition: width 0.1s linear;
    pointer-events: none;
}

.video-progress-handle {
    position: absolute;
    top: 50%;
    left: 0%;
    transform: translate(-50%, -50%);
    width: 16px;
    height: 16px;
    background: #ffcc33;
    border-radius: 50%;
    opacity: 0;
    transition: opacity 0.2s ease, transform 0.2s ease;
    box-shadow: 0 2px 6px rgba(0,0,0,0.3);
    pointer-events: auto;
}

.video-progress-bar:hover .video-progress-handle {
    opacity: 1;
    transform: translate(-50%, -50%) scale(1.2);
}

.control-btn {
    border-radius: 50%;
    width: 38px;
    height: 38px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: none;
    background: rgba(255,255,255,0.95);
    color: #003366;
    transition: all 0.2s ease;
}

.control-btn:hover {
    background: rgba(255,255,255,1);
    transform: scale(1.1);
    box-shadow: 0 2px 8px rgba(0,51,102,0.3);
}

.control-btn i {
    font-size: 0.875rem;
}

.video-time {
    font-size: 0.875rem;
    font-weight: 600;
    text-shadow: 0 1px 3px rgba(0,0,0,0.8);
}

/* Volume control */
.volume-control {
    position: relative;
}

.volume-slider-container {
    width: 0;
    overflow: hidden;
    transition: width 0.3s ease;
}

.volume-control:hover .volume-slider-container {
    width: 80px;
}

.volume-slider {
    position: relative;
    width: 80px;
    height: 4px;
    cursor: pointer;
    margin: 0 10px;
}

.volume-slider-track {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255,255,255,0.3);
    border-radius: 2px;
}

.volume-slider-fill {
    position: absolute;
    top: 0;
    left: 0;
    height: 100%;
    background: #ffcc33;
    border-radius: 2px;
    width: 100%;
    transition: width 0.1s linear;
}

.volume-slider-handle {
    position: absolute;
    top: 50%;
    left: 0;
    transform: translate(-50%, -50%);
    width: 12px;
    height: 12px;
    background: #ffcc33;
    border-radius: 50%;
    opacity: 0;
    transition: opacity 0.2s ease;
}

.volume-slider:hover .volume-slider-handle {
    opacity: 1;
}

/* Quality dropdown */
.quality-dropdown .dropdown-toggle {
    border: none;
}

.quality-dropdown .dropdown-toggle::after {
    display: none;
}

.quality-dropdown .dropdown-menu {
    background: rgba(0,51,102,0.95);
    border: 1px solid rgba(255,255,255,0.2);
    min-width: 100px;
}

.quality-dropdown .dropdown-item {
    color: white;
    padding: 0.5rem 1rem;
    transition: all 0.2s ease;
}

.quality-dropdown .dropdown-item:hover {
    background: rgba(255,255,255,0.1);
    color: #ffcc33;
}

.quality-dropdown .dropdown-item.active {
    background: rgba(255,204,51,0.2);
    color: #ffcc33;
    font-weight: 600;
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
    
    const playerId = '{{ $playerId }}';
    const videoId = '{{ $videoId }}';
    const container = document.getElementById('container-{{ $playerId }}');
    const loading = document.getElementById('loading-{{ $playerId }}');
    const watermark = document.getElementById('watermark-{{ $playerId }}');
    const isPreview = {{ $isPreview ? 'true' : 'false' }};
    const isAuth = {{ auth()->check() ? 'true' : 'false' }};
    
    if (!container) return;
    
    let youtubePlayer = null;
    let isPlaying = false;
    let currentVolume = 100;
    
    // Charger l'API YouTube
    function loadYouTubeAPI() {
        console.log('🔧 Loading YouTube API...');
        if (window.YT && window.YT.Player) {
            console.log('✅ YouTube API already loaded');
            initYouTubePlayer();
        } else {
            console.log('⏳ Loading YouTube API from CDN...');
            const tag = document.createElement('script');
            tag.src = 'https://www.youtube.com/iframe_api';
            const firstScriptTag = document.getElementsByTagName('script')[0];
            firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
            window.onYouTubeIframeAPIReady = initYouTubePlayer;
        }
    }
    
    // Initialiser le lecteur YouTube
    function initYouTubePlayer() {
        console.log('🔧 Initializing YouTube player for:', videoId);
        
        const playerElement = document.getElementById(playerId);
        if (!playerElement) {
            console.error('❌ Player element not found:', playerId);
            return;
        }
        
        youtubePlayer = new YT.Player(playerElement, {
            height: '100%',
            width: '100%',
            videoId: videoId,
            playerVars: {
                autoplay: 0,
                controls: 0,
                disablekb: 1,
                enablejsapi: 1,
                fs: 0,
                iv_load_policy: 3,
                modestbranding: 1,
                playsinline: 1,
                rel: 0,
                showinfo: 0,
                origin: window.location.origin
            },
            events: {
                onReady: onPlayerReady,
                onStateChange: onPlayerStateChange,
                onError: onPlayerError
            }
        });
        
        // Sauvegarder la référence
        window['plyr_' + playerId] = youtubePlayer;
    }
    
    // Callback quand le lecteur est prêt
    function onPlayerReady(event) {
        console.log('✅ YouTube player ready');
        if (loading) loading.style.display = 'none';
        
        // Afficher le watermark si authentifié
        if (watermark && !isPreview && isAuth) {
            setTimeout(() => {
                watermark.classList.add('show');
            }, 1000);
        }
        
        // Ajouter les contrôles personnalisés
        setupCustomControls();
        
        // Setup progress bar interaction
        setupProgressBar();
        
        // Populate quality menu
        setTimeout(populateQualityMenu, 1000);
        
        // Démarrer la mise à jour de la progress bar
        setInterval(updateProgress, 100);
    }
    
    // Callback pour les changements d'état
    function onPlayerStateChange(event) {
        const playBtn = document.getElementById('play-btn-' + playerId);
        if (event.data === YT.PlayerState.PLAYING) {
            isPlaying = true;
            if (playBtn) {
                playBtn.querySelector('i').classList.remove('fa-play');
                playBtn.querySelector('i').classList.add('fa-pause');
            }
        } else {
            isPlaying = false;
            if (playBtn) {
                playBtn.querySelector('i').classList.remove('fa-pause');
                playBtn.querySelector('i').classList.add('fa-play');
            }
        }
    }
    
    // Callback pour les erreurs
    function onPlayerError(event) {
        console.error('❌ YouTube player error:', event.data);
    }
    
    // Formater le temps
    function formatTime(seconds) {
        const hrs = Math.floor(seconds / 3600);
        const mins = Math.floor((seconds % 3600) / 60);
        const secs = Math.floor(seconds % 60);
        
        if (hrs > 0) {
            return `${hrs}:${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
        }
        return `${mins}:${secs.toString().padStart(2, '0')}`;
    }
    
    // Mettre à jour la progress bar
    function updateProgress() {
        if (!youtubePlayer) return;
        
        try {
            const currentTime = youtubePlayer.getCurrentTime();
            const duration = youtubePlayer.getDuration();
            const buffered = youtubePlayer.getVideoLoadedFraction();
            
            if (duration && duration > 0) {
                const percent = (currentTime / duration) * 100;
                
                const progressFilled = document.getElementById('progress-filled-' + playerId);
                const progressHandle = document.getElementById('progress-handle-' + playerId);
                const progressBuffered = document.getElementById('progress-buffered-' + playerId);
                const timeDisplay = document.getElementById('time-' + playerId);
                
                if (progressFilled) progressFilled.style.width = percent + '%';
                if (progressHandle) progressHandle.style.left = percent + '%';
                if (progressBuffered) progressBuffered.style.width = (buffered * 100) + '%';
                if (timeDisplay) timeDisplay.textContent = formatTime(currentTime) + ' / ' + formatTime(duration);
            }
        } catch (e) {
            // Ignorer les erreurs
        }
    }
    
    // Configurer les contrôles personnalisés
    function setupCustomControls() {
        const playBtn = document.getElementById('play-btn-' + playerId);
        const fullscreenBtn = document.getElementById('fullscreen-btn-' + playerId);
        const muteBtn = document.getElementById('mute-btn-' + playerId);
        const volumeSlider = document.getElementById('volume-slider-' + playerId);
        const volumeFill = document.getElementById('volume-fill-' + playerId);
        const volumeHandle = document.getElementById('volume-handle-' + playerId);
        const container = document.getElementById('container-{{ $playerId }}');
        const wrapper = document.getElementById('video-wrapper-{{ $playerId }}');
        
        // Play/Pause
        if (playBtn) {
            playBtn.addEventListener('click', function() {
                if (isPlaying) {
                    youtubePlayer.pauseVideo();
                } else {
                    youtubePlayer.playVideo();
                }
            });
        }
        
        // Fullscreen
        if (fullscreenBtn && container) {
            let isFullscreen = false;
            fullscreenBtn.addEventListener('click', function() {
                if (!isFullscreen) {
                    if (container.requestFullscreen) {
                        container.requestFullscreen();
                    } else if (container.webkitRequestFullscreen) {
                        container.webkitRequestFullscreen();
                    } else if (container.mozRequestFullScreen) {
                        container.mozRequestFullScreen();
                    }
                    fullscreenBtn.querySelector('i').classList.remove('fa-expand');
                    fullscreenBtn.querySelector('i').classList.add('fa-compress');
                    isFullscreen = true;
                } else {
                    if (document.exitFullscreen) {
                        document.exitFullscreen();
                    } else if (document.webkitExitFullscreen) {
                        document.webkitExitFullscreen();
                    } else if (document.mozCancelFullScreen) {
                        document.mozCancelFullScreen();
                    }
                    fullscreenBtn.querySelector('i').classList.remove('fa-compress');
                    fullscreenBtn.querySelector('i').classList.add('fa-expand');
                    isFullscreen = false;
                }
            });
        }
        
        // Mute/Unmute
        if (muteBtn) {
            muteBtn.addEventListener('click', function() {
                if (currentVolume === 0) {
                    currentVolume = 100;
                    youtubePlayer.setVolume(100);
                    muteBtn.querySelector('i').classList.remove('fa-volume-mute');
                    muteBtn.querySelector('i').classList.add('fa-volume-up');
                } else {
                    currentVolume = 0;
                    youtubePlayer.setVolume(0);
                    muteBtn.querySelector('i').classList.remove('fa-volume-up');
                    muteBtn.querySelector('i').classList.add('fa-volume-mute');
                }
                updateVolumeDisplay();
            });
        }
        
        // Volume slider
        if (volumeSlider) {
            // Initialize volume display
            updateVolumeDisplay();
            
            volumeSlider.addEventListener('click', function(e) {
                const rect = volumeSlider.getBoundingClientRect();
                const percent = (e.clientX - rect.left) / rect.width;
                currentVolume = Math.max(0, Math.min(100, percent * 100));
                youtubePlayer.setVolume(currentVolume);
                updateVolumeDisplay();
                
                // Update mute button icon
                if (muteBtn) {
                    if (currentVolume === 0) {
                        muteBtn.querySelector('i').classList.remove('fa-volume-up', 'fa-volume-down');
                        muteBtn.querySelector('i').classList.add('fa-volume-mute');
                    } else if (currentVolume < 50) {
                        muteBtn.querySelector('i').classList.remove('fa-volume-mute', 'fa-volume-up');
                        muteBtn.querySelector('i').classList.add('fa-volume-down');
                    } else {
                        muteBtn.querySelector('i').classList.remove('fa-volume-mute', 'fa-volume-down');
                        muteBtn.querySelector('i').classList.add('fa-volume-up');
                    }
                }
            });
        }
        
        // Quality dropdown
        const qualityMenu = document.getElementById('quality-menu-' + playerId);
        if (qualityMenu) {
            qualityMenu.addEventListener('click', function(e) {
                e.preventDefault();
                const quality = e.target.getAttribute('data-quality');
                if (quality) {
                    console.log('Changing quality to:', quality);
                    youtubePlayer.setPlaybackQuality(quality);
                    e.target.closest('.dropdown-menu').querySelectorAll('.dropdown-item').forEach(item => {
                        item.classList.remove('active');
                    });
                    e.target.classList.add('active');
                }
            });
        }
    }
    
    // Setup progress bar interaction
    function setupProgressBar() {
        const progressBar = document.getElementById('progress-bar-' + playerId);
        if (progressBar) {
            console.log('Progress bar found:', progressBar);
            progressBar.addEventListener('click', function(e) {
                console.log('Progress bar clicked at:', e.clientX);
                e.stopPropagation();
                const rect = progressBar.getBoundingClientRect();
                const percent = (e.clientX - rect.left) / rect.width;
                console.log('Percent:', percent);
                const duration = youtubePlayer.getDuration();
                console.log('Duration:', duration);
                if (duration && duration > 0) {
                    youtubePlayer.seekTo(duration * percent, true);
                    console.log('Seeked to:', duration * percent);
                }
            });
        } else {
            console.log('Progress bar NOT found!');
        }
    }
    
    // Populate quality menu
    function populateQualityMenu() {
        try {
            console.log('🔄 Populating quality menu...');
            const qualityLevels = youtubePlayer.getAvailableQualityLevels();
            console.log('Available quality levels:', qualityLevels);
            const qualityMenu = document.getElementById('quality-menu-' + playerId);
            console.log('Quality menu element:', qualityMenu);
            
            if (qualityMenu && qualityLevels && qualityLevels.length > 0) {
                qualityMenu.innerHTML = '';
                
                const qualityNames = {
                    'auto': 'Auto',
                    'small': '240p',
                    'medium': '360p',
                    'large': '480p',
                    'hd720': '720p',
                    'hd1080': '1080p',
                    'highres': '1080p+'
                };
                
                qualityLevels.forEach(quality => {
                    const li = document.createElement('li');
                    const a = document.createElement('a');
                    a.className = 'dropdown-item';
                    a.href = '#';
                    a.setAttribute('data-quality', quality);
                    a.textContent = qualityNames[quality] || quality;
                    li.appendChild(a);
                    qualityMenu.appendChild(li);
                });
                
                console.log('✅ Quality menu populated with', qualityLevels.length, 'items');
                
                // Mark current quality as active
                const currentQuality = youtubePlayer.getPlaybackQuality();
                console.log('Current quality:', currentQuality);
                const activeItem = qualityMenu.querySelector(`[data-quality="${currentQuality}"]`);
                if (activeItem) {
                    activeItem.classList.add('active');
                    console.log('✅ Marked quality as active');
                }
            } else {
                console.warn('⚠️ Could not populate quality menu - missing data');
            }
        } catch (e) {
            console.error('❌ Error populating quality menu:', e);
        }
    }
    
    // Update volume display
    function updateVolumeDisplay() {
        const volumeFill = document.getElementById('volume-fill-' + playerId);
        const volumeHandle = document.getElementById('volume-handle-' + playerId);
        
        if (volumeFill) {
            volumeFill.style.width = currentVolume + '%';
        }
        if (volumeHandle) {
            volumeHandle.style.left = currentVolume + '%';
        }
    }
    
    // Détecter si le conteneur est dans un modal Bootstrap
    const modalElement = container?.closest('.modal');
    
    if (modalElement) {
        // Si dans un modal, initialiser quand le modal est affiché
        modalElement.addEventListener('shown.bs.modal', function() {
            const existingPlayer = window['plyr_' + playerId];
            if (!existingPlayer) {
                loadYouTubeAPI();
            }
        });
    } else {
        // Sinon, initialiser normalement
        loadYouTubeAPI();
    }
})();
</script>
@endpush
