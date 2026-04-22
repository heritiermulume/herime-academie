@php
    $authUser = auth()->user();
    $liveLayout = 'layouts.app';
    $useDashboardSection = false;
    $liveShowRouteName = 'live-training.show';
    $liveShowPathPrefix = '/live-training/';
    $currentRouteName = optional(request()->route())->getName();

    if ($currentRouteName && str_starts_with($currentRouteName, 'customer.')) {
        $liveLayout = 'customers.admin.layout';
        $useDashboardSection = true;
        $liveShowRouteName = 'customer.live-training.show';
        $liveShowPathPrefix = '/customer/live-training/';
    } elseif ($currentRouteName && str_starts_with($currentRouteName, 'provider.')) {
        $liveLayout = 'providers.admin.layout';
        $useDashboardSection = true;
        $liveShowRouteName = 'provider.live-training.show';
        $liveShowPathPrefix = '/provider/live-training/';
    } elseif ($authUser && method_exists($authUser, 'isCustomer') && $authUser->isCustomer()) {
        $liveLayout = 'customers.admin.layout';
        $useDashboardSection = true;
        $liveShowRouteName = 'customer.live-training.show';
        $liveShowPathPrefix = '/customer/live-training/';
    } elseif ($authUser && method_exists($authUser, 'isProvider') && $authUser->isProvider()) {
        $liveLayout = 'providers.admin.layout';
        $useDashboardSection = true;
        $liveShowRouteName = 'provider.live-training.show';
        $liveShowPathPrefix = '/provider/live-training/';
    }
    $contextSpace = $useDashboardSection ? (str_starts_with($currentRouteName ?? '', 'provider.') ? 'provider' : 'customer') : 'default';
@endphp

@extends($liveLayout)

@section('title', 'Formations en direct')
@section('admin-title', 'Formations en direct')
@section('admin-subtitle', 'Espace de cours en visioconference')

@if($useDashboardSection)
@section('admin-content')
@else
@section('content')
@endif
<section class="container live-training-page {{ $useDashboardSection ? 'live-training-page--dashboard' : '' }} pt-2 pb-4 pt-lg-3 pb-lg-5">
    @if(!$useDashboardSection)
        <div class="live-header mb-4">
            <h1 class="h3 fw-bold mb-2">Formations en direct</h1>
            <p class="text-muted mb-0">Cet espace vous permet de suivre ou d'animer des sessions de formation en temps reel pour un programme precis, avec echanges audio/video, questions-reponses et accompagnement pedagogique pendant la seance.</p>
        </div>
    @endif

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
                    <input type="hidden" name="context_space" value="{{ $contextSpace }}">
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
                            @php $courseSessions = $activeSessionsByCourse[$program->id] ?? []; @endphp
                            <div class="list-group-item d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                                <div>
                                    <strong>{{ $program->title }}</strong>
                                    <div class="small text-muted">
                                        @if(!empty($courseSessions))
                                            {{ count($courseSessions) }} session(s) active(s)
                                        @else
                                            En attente du demarrage de la formation
                                        @endif
                                    </div>
                                </div>
                                @if(!empty($courseSessions))
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($courseSessions as $sessionInfo)
                                            <a href="{{ url($liveShowPathPrefix . $program->slug) }}?session_owner={{ (int) ($sessionInfo['started_by'] ?? 0) }}" class="btn btn-sm btn-primary">
                                                <i class="fas fa-video me-1"></i>Rejoindre {{ $sessionInfo['started_by_name'] ?? 'admin' }}
                                            </a>
                                        @endforeach
                                    </div>
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
                    <form class="stop-live-form stop-live-form--main" method="POST" action="{{ route('live-training.stop', $selectedCourse->slug) }}">
                        @csrf
                        <input type="hidden" name="context_space" value="{{ $contextSpace }}">
                        <input type="hidden" name="session_owner" value="{{ $sessionOwnerId ?? ($user?->id ?? 0) }}">
                        <button type="submit" class="btn btn-danger stop-live-btn-main">
                            <i class="fas fa-stop me-2"></i>Arreter la formation
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <div class="card shadow-sm border-0 live-jitsi-card">
            <div class="card-body p-0">
                <div id="jitsi-container" class="jitsi-frame" aria-label="Visioconference en direct"></div>
            </div>
        </div>
    @endif

    @if($isAdmin)
        <div class="card shadow-sm border-0 mt-4">
            <div class="card-body">
                <h2 class="h5 mb-3">Sessions actives par admin</h2>
                @php $hasAdminSessions = false; @endphp
                @foreach($coursesForStart as $course)
                    @php $courseSessions = $activeSessionsByCourse[$course->id] ?? []; @endphp
                    @if(!empty($courseSessions))
                        @php $hasAdminSessions = true; @endphp
                        <div class="mb-3">
                            <h3 class="h6 mb-2">{{ $course->title }}</h3>
                            <div class="list-group">
                                @foreach($courseSessions as $sessionInfo)
                                    <div class="list-group-item live-session-item">
                                        <div class="live-session-item__meta">
                                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                                <strong>{{ $sessionInfo['started_by_name'] ?? 'Administrateur' }}</strong>
                                                @if((int) ($sessionInfo['started_by'] ?? 0) === (int) ($user?->id ?? 0))
                                                    <span class="badge rounded-pill text-bg-success">Ma session</span>
                                                @endif
                                            </div>
                                            <div class="small text-muted">
                                                Salle: <code>{{ $sessionInfo['room'] ?? '-' }}</code>
                                            </div>
                                        </div>
                                        <div class="live-session-item__actions">
                                            <a href="{{ url($liveShowPathPrefix . $course->slug) }}?session_owner={{ (int) ($sessionInfo['started_by'] ?? 0) }}" class="btn btn-sm btn-primary">
                                                <i class="fas fa-video me-1"></i>Rejoindre
                                            </a>
                                            <form method="POST" action="{{ route('live-training.stop', $course->slug) }}">
                                                @csrf
                                                <input type="hidden" name="context_space" value="{{ $contextSpace }}">
                                                <input type="hidden" name="session_owner" value="{{ (int) ($sessionInfo['started_by'] ?? 0) }}">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-stop me-1"></i>Arreter
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
                @if(!$hasAdminSessions)
                    <p class="text-muted mb-0">Aucune session active pour le moment.</p>
                @endif
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
                prejoinPageEnabled: !isAdmin,
                startWithAudioMuted: false,
                startWithVideoMuted: false,
                readOnlyName: true,
                hideConferenceSubject: true,
                prejoinConfig: {
                    enabled: !isAdmin,
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
        let stopSubmitInProgress = false;

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

        if (isAdmin) {
            const stopForms = document.querySelectorAll('.stop-live-form');
            stopForms.forEach(function (stopForm) {
                stopForm.addEventListener('submit', function (event) {
                    if (stopSubmitInProgress) {
                        return;
                    }

                    event.preventDefault();
                    stopSubmitInProgress = true;

                    try {
                        // Ferme la conference pour tous les participants (moderateur requis).
                        api.executeCommand('endConference');
                    } catch (error) {
                        // Fallback: la soumission serveur continue meme si la commande echoue.
                    }

                    setTimeout(function () {
                        sendLeave();
                        stopForm.submit();
                    }, 450);
                });
            });
        }
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

    .live-training-page--dashboard {
        padding-top: 0.2rem !important;
        padding-bottom: 0.7rem !important;
    }

    .live-training-page--dashboard .card.mb-4 {
        margin-bottom: 0.55rem !important;
    }

    .live-training-page--dashboard .card.mb-3 {
        margin-bottom: 0.45rem !important;
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
        overflow-wrap: break-word;
        word-break: normal;
    }

    .live-header p {
        max-width: 900px;
        line-height: 1.6;
    }

    .live-session-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
    }

    .live-session-item__meta {
        min-width: 0;
        flex: 1;
    }

    .live-session-item__actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .live-session-item__actions form {
        margin: 0;
    }

    .stop-live-form--main {
        margin-left: auto;
    }

    .stop-live-btn-main {
        background-color: #dc3545;
        border-color: #dc3545;
        color: #fff;
        font-weight: 600;
    }

    .stop-live-btn-main:hover,
    .stop-live-btn-main:focus {
        background-color: #bb2d3b;
        border-color: #b02a37;
        color: #fff;
    }

    .card {
        overflow: hidden;
    }

    .live-jitsi-card {
        margin: 0 !important;
    }

    .live-jitsi-card .card-body {
        padding: 0 !important;
    }

    .jitsi-frame {
        width: 100%;
        height: auto;
        min-height: 560px;
        max-width: 100%;
        border-radius: 0;
        overflow: hidden;
        background: #0f172a;
        position: relative;
        margin: 0 !important;
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
        .student-admin-shell {
            padding-top: calc(var(--site-navbar-height, 64px) + 1.1rem);
        }

        .live-training-page--dashboard {
            padding-top: 0.1rem !important;
            padding-bottom: 0.45rem !important;
        }

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
            border-radius: 0;
        }

        .live-session-item {
            flex-direction: column;
            align-items: flex-start;
        }

        .live-session-item__actions {
            width: 100%;
            justify-content: flex-start;
        }

        .stop-live-form--main {
            margin-left: 0;
            width: 100%;
            display: flex;
            justify-content: center;
        }

        .stop-live-btn-main {
            font-size: 0.85rem;
            padding: 0.4rem 0.75rem;
        }
    }

    @media (max-width: 640px) {
        .student-admin-shell {
            padding-top: calc(var(--site-navbar-height, 64px) + 0.85rem);
        }
    }
</style>
@endpush
