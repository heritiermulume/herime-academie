@extends('layouts.app')

@section('title', 'Formation en direct')

@section('content')
<section class="container py-4 py-lg-5">
    <div class="live-header mb-4">
        <h1 class="h3 fw-bold mb-2">Formation en direct</h1>
        <p class="text-muted mb-0">Cet espace vous permet de suivre ou d'animer des sessions de formation en temps reel pour un programme precis, avec echanges audio/video, questions-reponses et accompagnement pedagogique pendant la seance.</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if($isAdmin)
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <h2 class="h5 mb-3">Demarrer une formation en direct (Admin)</h2>
                <form method="POST" action="{{ route('live-training.start') }}" class="row g-3 align-items-end">
                    @csrf
                    <div class="col-md-5">
                        <label for="course_id" class="form-label fw-semibold">Programme</label>
                        <select id="course_id" name="course_id" class="form-select" required>
                            <option value="">Selectionnez un programme</option>
                            @foreach($coursesForStart as $course)
                                <option value="{{ $course->id }}">{{ $course->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label for="room" class="form-label fw-semibold">Nom de salle (optionnel)</label>
                        <input id="room" name="room" type="text" class="form-control" maxlength="80" placeholder="ex: programme-marketing-live">
                    </div>
                    <div class="col-md-2 d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-play me-2"></i>Demarrer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @else
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <h2 class="h5 mb-3">Mes programmes accessibles</h2>
                @if($accessiblePrograms->isEmpty())
                    <p class="text-muted mb-0">Aucun programme avec acces live n'a ete trouve pour votre compte.</p>
                @else
                    <div class="list-group">
                        @foreach($accessiblePrograms as $program)
                            @php
                                $isLiveActive = isset($activeSessions[$program->id]);
                            @endphp
                            <div class="list-group-item d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                                <div>
                                    <strong>{{ $program->title }}</strong>
                                    <div class="small text-muted">
                                        @if($isLiveActive)
                                            Formation en direct active
                                        @else
                                            En attente du demarrage de la formation
                                        @endif
                                    </div>
                                </div>
                                @if($isLiveActive)
                                    <a href="{{ route('live-training.show', $program->slug) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-video me-1"></i>Rejoindre
                                    </a>
                                @else
                                    <button type="button" class="btn btn-sm btn-outline-secondary" disabled>Pas encore actif</button>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @endif

    @if($selectedCourse && $sessionStartedByAdmin && $roomName)
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <h2 class="h5 mb-1">{{ $selectedCourse->title }}</h2>
                    <p class="mb-0 text-muted">Salle: <code>{{ $roomName }}</code></p>
                </div>
                @if($isAdmin)
                    <form method="POST" action="{{ route('live-training.stop', $selectedCourse->slug) }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger">
                            <i class="fas fa-stop me-2"></i>Arreter la formation
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-2 p-md-3">
                <div id="jitsi-container" class="jitsi-frame" aria-label="Visioconference en direct"></div>
            </div>
        </div>
    @endif
</section>
@endsection

@push('scripts')
@if($selectedCourse && $sessionStartedByAdmin && $roomName)
<script src="https://{{ $jitsiDomain }}/external_api.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const container = document.getElementById('jitsi-container');
        const liveSection = container ? container.closest('section.container') : null;
        const roomName = @json($roomName);
        const userName = @json($user?->name ?? 'Participant');
        const userEmail = @json($user?->email ?? null);
        const domain = @json($jitsiDomain);
        const isAdmin = @json($isAdmin);
        const csrfToken = @json(csrf_token());
        const participantPingUrl = @json(route('live-training.participants.ping', $selectedCourse->slug));
        const participantLeaveUrl = @json(route('live-training.participants.leave', $selectedCourse->slug));
        const storeMessageUrl = @json(route('live-training.messages.store', $selectedCourse->slug));

        if (!container || typeof window.JitsiMeetExternalAPI === 'undefined') {
            return;
        }

        const updateContainerHeight = function () {
            if (!container || !liveSection) return;
            const viewportHeight = window.visualViewport ? window.visualViewport.height : window.innerHeight;
            const sectionTop = liveSection.getBoundingClientRect().top;
            const available = Math.floor(viewportHeight - sectionTop - 16);
            const minHeight = window.innerWidth <= 768 ? 360 : 560;
            const maxHeight = window.innerWidth <= 768 ? 560 : 860;
            const clamped = Math.min(Math.max(available, minHeight), maxHeight);
            container.style.height = clamped + 'px';
        };

        updateContainerHeight();
        window.addEventListener('resize', updateContainerHeight, { passive: true });
        window.addEventListener('orientationchange', updateContainerHeight, { passive: true });
        if (window.visualViewport) {
            window.visualViewport.addEventListener('resize', updateContainerHeight, { passive: true });
        }

        const options = {
            roomName: roomName,
            parentNode: container,
            width: '100%',
            height: '100%',
            userInfo: {
                displayName: userName,
                email: userEmail
            },
            configOverwrite: {
                prejoinPageEnabled: true,
                startWithAudioMuted: false,
                startWithVideoMuted: false,
                readOnlyName: true,
                hideConferenceSubject: true,
                prejoinConfig: {
                    enabled: true,
                    hideDisplayName: true
                },
                disableProfile: true
            },
            interfaceConfigOverwrite: {
                SHOW_JITSI_WATERMARK: false,
                SHOW_BRAND_WATERMARK: false,
                SHOW_POWERED_BY: false,
                SHOW_CHROME_EXTENSION_BANNER: false,
                DEFAULT_LOGO_URL: '',
                JITSI_WATERMARK_LINK: '',
                BRAND_WATERMARK_LINK: '',
                MOBILE_APP_PROMO: false,
                TOOLBAR_BUTTONS: [
                    'microphone',
                    'camera',
                    'closedcaptions',
                    'desktop',
                    'fullscreen',
                    'fodeviceselection',
                    'hangup',
                    'chat',
                    'tileview',
                    'raisehand',
                    'videoquality',
                    'participants-pane',
                    'select-background'
                ]
            }
        };

        if (!isAdmin) {
            options.configOverwrite.startSilent = true;
        }

        const api = new window.JitsiMeetExternalAPI(domain, options);
        let currentParticipantId = null;

        const postJson = async function (url, payload) {
            try {
                await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(payload)
                });
            } catch (error) {
                // Tracking best-effort only.
            }
        };

        api.addEventListener('videoConferenceJoined', function (event) {
            currentParticipantId = event?.id || null;
            postJson(participantPingUrl, {
                jitsi_participant_id: currentParticipantId,
                display_name: userName
            });
        });

        api.addEventListener('outgoingMessage', function (event) {
            const message = event?.message || event?.text || '';
            if (typeof message === 'string' && message.trim() !== '') {
                postJson(storeMessageUrl, {
                    message: message.trim(),
                    sender_name: userName,
                    message_type: 'chat_outgoing'
                });
            }
        });

        api.addEventListener('endpointTextMessageReceived', function (event) {
            const message = event?.eventData?.text || event?.data?.text || '';
            const senderName = event?.senderInfo?.displayName || userName;
            if (typeof message === 'string' && message.trim() !== '') {
                postJson(storeMessageUrl, {
                    message: message.trim(),
                    sender_name: senderName,
                    message_type: 'chat_incoming'
                });
            }
        });

        const sendLeave = function () {
            fetch(participantLeaveUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                credentials: 'same-origin',
                keepalive: true,
                body: JSON.stringify({ jitsi_participant_id: currentParticipantId })
            }).catch(function () {
                // Best-effort tracking.
            });
        };

        api.addEventListener('readyToClose', sendLeave);
        window.addEventListener('beforeunload', sendLeave);
    });
</script>
@endif
@endpush

@push('styles')
<style>
    section.container {
        max-width: 100%;
        overflow-x: hidden;
    }

    .live-header,
    .card,
    .card-body,
    .list-group-item {
        max-width: 100%;
        box-sizing: border-box;
    }

    .live-header p,
    .list-group-item,
    .card-body p,
    code {
        overflow-wrap: anywhere;
        word-break: break-word;
    }

    .card {
        overflow: hidden;
    }

    .jitsi-frame {
        width: 100%;
        height: auto;
        min-height: 560px;
        max-width: 100%;
        border-radius: 12px;
        overflow: hidden;
        background: #0f172a;
        position: relative;
    }

    .jitsi-frame > div,
    .jitsi-frame iframe {
        width: 100% !important;
        height: 100% !important;
        max-width: 100% !important;
        max-height: 100% !important;
        border: 0 !important;
        display: block;
    }

    .jitsi-frame > div {
        overflow: hidden !important;
    }

    @media (max-width: 768px) {
        section.container {
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }

        .card-body {
            padding-left: 0.75rem !important;
            padding-right: 0.75rem !important;
        }

        .jitsi-frame {
            height: auto;
            min-height: 360px;
            border-radius: 10px;
        }
    }
</style>
@endpush
