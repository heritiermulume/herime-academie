@extends('students.admin.layout')

@section('admin-title', 'Tableau de bord étudiant')
@section('admin-subtitle', 'Suivez votre progression, vos achats et vos réussites en un coup d’œil.')

@section('admin-actions')
    <a href="{{ route('courses.index') }}" class="admin-btn primary">
        <i class="fas fa-search me-2"></i>Découvrir de nouveaux cours
    </a>
    <a href="{{ route('orders.index') }}" class="admin-btn outline">
        <i class="fas fa-receipt me-2"></i>Historique des commandes
    </a>
@endsection

@section('admin-content')
<div class="student-dashboard">
    <div class="student-dashboard__stats">
        <div class="student-stat">
            <span class="student-stat__label">Cours inscrits</span>
            <strong class="student-stat__value">{{ number_format($stats['total_courses']) }}</strong>
            <small class="student-stat__hint">{{ $stats['active_courses'] }} en cours · {{ $stats['completed_courses'] }} terminés</small>
        </div>
        <div class="student-stat">
            <span class="student-stat__label">Progression moyenne</span>
            <strong class="student-stat__value">{{ $stats['average_progress'] }}%</strong>
            <small class="student-stat__hint">Continuez sur cette lancée</small>
        </div>
        <div class="student-stat">
            <span class="student-stat__label">Certificats obtenus</span>
            <strong class="student-stat__value">{{ number_format($stats['certificates_earned']) }}</strong>
            <small class="student-stat__hint">
                {{ max(0, 5 - $stats['certificates_earned']) }} avant le prochain palier
            </small>
        </div>
        <div class="student-stat">
            <span class="student-stat__label">Temps d’apprentissage</span>
            <strong class="student-stat__value">
                {{ $stats['learning_minutes'] > 0 ? number_format(round($stats['learning_minutes'] / 60, 1), 1) : '0' }} h
            </strong>
            <small class="student-stat__hint">Durée cumulée de vos cours suivis</small>
        </div>
    </div>

    @if($lastUpdatedEnrollment && $lastUpdatedEnrollment->course)
        @php($highlightCourse = $lastUpdatedEnrollment->course)
        <div class="admin-card student-highlight">
            <div class="student-highlight__content">
                <div>
                    <span class="student-highlight__eyebrow">Reprendre là où vous vous êtes arrêté</span>
                    <h2 class="student-highlight__title">{{ $highlightCourse->title }}</h2>
                    <p class="student-highlight__subtitle">
                        {{ $highlightCourse->instructor->name ?? 'Formateur' }}
                        @if($highlightCourse->category?->name)
                            · {{ $highlightCourse->category->name }}
                        @endif
                    </p>
                    <div class="student-progress">
                        <div class="student-progress__meta">
                            <span>Progression du cours</span>
                            <strong>{{ $lastUpdatedEnrollment->progress }}%</strong>
                        </div>
                        <div class="student-progress__bar">
                            <span style="width: {{ $lastUpdatedEnrollment->progress }}%"></span>
                        </div>
                        <small class="student-progress__hint">
                            Dernière mise à jour {{ $lastUpdatedEnrollment->updated_at?->diffForHumans() }}
                        </small>
                    </div>
                </div>
                <div class="student-highlight__actions">
                    <a href="{{ route('student.courses.learn', $highlightCourse->slug) }}" class="admin-btn primary lg">
                        <i class="fas fa-play me-2"></i>Continuer
                    </a>
                    <a href="{{ route('student.courses') }}" class="admin-btn ghost">
                        Voir tous mes cours
                    </a>
                </div>
            </div>
        </div>
    @else
        <div class="admin-card student-highlight empty">
            <div class="student-highlight__content">
                <div>
                    <span class="student-highlight__eyebrow">Commencez votre parcours</span>
                    <h2 class="student-highlight__title">Inscrivez-vous à votre premier cours</h2>
                    <p class="student-highlight__subtitle">
                        Explorez des formations adaptées à vos objectifs et avancez à votre rythme.
                    </p>
                </div>
                <div class="student-highlight__actions">
                    <a href="{{ route('courses.index') }}" class="admin-btn primary lg">
                        <i class="fas fa-compass me-2"></i>Explorer les cours
                    </a>
                    <a href="{{ route('student.courses') }}" class="admin-btn ghost">
                        Voir l’espace de formation
                    </a>
                </div>
            </div>
        </div>
    @endif

    <div class="student-dashboard__grid">
        <div class="admin-card">
            <div class="student-card-header">
                <div>
                    <h3 class="admin-card__title">Mes cours en cours</h3>
                    <p class="admin-card__subtitle">Accédez à vos cours actifs et à leur progression.</p>
                </div>
                <a href="{{ route('student.courses') }}" class="admin-btn soft">
                    Voir tout
                </a>
            </div>
            @if($enrollments->isEmpty())
                <div class="admin-empty-state">
                    <i class="fas fa-book-open"></i>
                    <p>Vous n’êtes inscrit à aucun cours pour le moment.</p>
                    <a href="{{ route('courses.index') }}" class="admin-btn primary sm mt-3">
                        Trouver un cours
                    </a>
                </div>
            @else
                <div class="student-course-list">
                    @foreach($enrollments as $enrollment)
                        @php($course = $enrollment->course)
                        @if($course)
                            <div class="student-course-item">
                                <div class="student-course-item__meta">
                                    <div class="student-course-item__icon">
                                        <i class="fas fa-graduation-cap"></i>
                                    </div>
                                    <div>
                                        <h4>{{ $course->title }}</h4>
                                        <p>
                                            {{ $course->instructor->name ?? 'Formateur' }}
                                            @if($course->category?->name)
                                                · {{ $course->category->name }}
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                <div class="student-course-item__progress">
                                    <div class="student-progress">
                                        <div class="student-progress__bar">
                                            <span style="width: {{ $enrollment->progress }}%"></span>
                                        </div>
                                        <span class="student-progress__value">{{ $enrollment->progress }}%</span>
                                    </div>
                                    <div class="student-course-item__stats">
                                        <span>
                                            <i class="fas fa-layer-group me-1"></i>
                                            {{ $course->lessons_count ?? $course->lessons()->count() }} leçons
                                        </span>
                                        @if($course->duration ?? false)
                                            <span>
                                                <i class="fas fa-clock me-1"></i>
                                                {{ $course->duration }} min
                                            </span>
                                        @endif
                                        @if(($course->is_downloadable ?? false) && isset($course->user_downloads_count))
                                            <span>
                                                <i class="fas fa-download me-1"></i>
                                                {{ $course->user_downloads_count }} téléchargements
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="student-course-item__actions">
                                    <a href="{{ route('student.courses.learn', $course->slug) }}" class="admin-btn primary sm">
                                        Continuer
                                    </a>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>

        <div class="admin-card">
            <div class="student-card-header">
                <div>
                    <h3 class="admin-card__title">Dernières commandes</h3>
                    <p class="admin-card__subtitle">Vos achats récents et leur statut.</p>
                </div>
                <a href="{{ route('orders.index') }}" class="admin-btn soft">
                    Historique complet
                </a>
            </div>
            @if($orders->isEmpty())
                <div class="admin-empty-state">
                    <i class="fas fa-shopping-bag"></i>
                    <p>Aucune commande enregistrée pour l’instant.</p>
                    <a href="{{ route('courses.index') }}" class="admin-btn primary sm mt-3">
                        Acheter un cours
                    </a>
                </div>
            @else
                <div class="student-order-list">
                    @foreach($orders as $order)
                        <div class="student-order-item">
                            <div>
                                <span class="student-order-item__number">{{ $order->order_number }}</span>
                                <p class="student-order-item__meta">
                                    {{ $order->created_at->format('d/m/Y') }} ·
                                    {{ \App\Helpers\CurrencyHelper::formatWithSymbol($order->total_amount ?? $order->total ?? 0) }}
                                </p>
                            </div>
                            <div class="student-order-item__status">
                                <span class="admin-badge {{ in_array($order->status, ['paid', 'completed']) ? 'success' : ($order->status === 'pending' ? 'warning' : 'info') }}">
                                    <i class="fas fa-circle"></i>
                                    @switch($order->status)
                                        @case('pending') En attente @break
                                        @case('confirmed') Confirmée @break
                                        @case('paid') Payée @break
                                        @case('completed') Terminée @break
                                        @case('cancelled') Annulée @break
                                        @default {{ ucfirst($order->status) }}
                                    @endswitch
                                </span>
                            </div>
                            <div class="student-order-item__actions">
                                <a href="{{ route('orders.show', $order) }}" class="admin-btn ghost sm">
                                    Détails
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="student-dashboard__grid">
        <div class="admin-card">
            <div class="student-card-header">
                <div>
                    <h3 class="admin-card__title">Certificats récents</h3>
                    <p class="admin-card__subtitle">Téléchargez vos attestations de réussite.</p>
                </div>
                <a href="{{ route('student.certificates') }}" class="admin-btn soft">
                    Voir tous les certificats
                </a>
            </div>
            @if($certificates->isEmpty())
                <div class="admin-empty-state">
                    <i class="fas fa-certificate"></i>
                    <p>Terminez un cours pour obtenir votre premier certificat.</p>
                    <a href="{{ route('student.courses') }}" class="admin-btn primary sm mt-3">
                        Continuer mes cours
                    </a>
                </div>
            @else
                <div class="student-certificate-list">
                    @foreach($certificates as $certificate)
                        <div class="student-certificate-item">
                            <div class="student-certificate-item__icon">
                                <i class="fas fa-award"></i>
                            </div>
                            <div class="student-certificate-item__info">
                                <h4>{{ $certificate->course->title ?? $certificate->title }}</h4>
                                <p>Délivré le {{ optional($certificate->issued_at)->format('d/m/Y') }}</p>
                                <span>#{{ $certificate->certificate_number }}</span>
                            </div>
                            @if($certificate->file_path)
                                <a href="{{ asset('storage/' . $certificate->file_path) }}" target="_blank" class="admin-btn ghost sm">
                                    Télécharger
                                </a>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="admin-card">
            <div class="student-card-header">
                <div>
                    <h3 class="admin-card__title">Recommandations pour vous</h3>
                    <p class="admin-card__subtitle">Complétez votre parcours avec ces cours sélectionnés.</p>
                </div>
                <a href="{{ route('courses.index') }}" class="admin-btn soft">
                    Explorer davantage
                </a>
            </div>
            @if($recommendedCourses->isEmpty())
                <div class="admin-empty-state">
                    <i class="fas fa-lightbulb"></i>
                    <p>De nouvelles recommandations apparaîtront après vos prochaines inscriptions.</p>
                </div>
            @else
                <div class="student-recommendations">
                    @foreach($recommendedCourses as $course)
                        <div class="student-recommendations__item">
                            <div class="student-recommendations__media">
                                @if($course->thumbnail_url)
                                    <img src="{{ $course->thumbnail_url }}" alt="{{ $course->title }}">
                                @else
                                    <span>{{ $course->initials }}</span>
                                @endif
                            </div>
                            <div class="student-recommendations__content">
                                <h4>{{ $course->title }}</h4>
                                <p>
                                    {{ $course->instructor->name ?? 'Formateur' }}
                                    @if($course->category?->name)
                                        · {{ $course->category->name }}
                                    @endif
                                </p>
                            </div>
                            <div class="student-recommendations__actions">
                                <a href="{{ route('courses.show', $course->slug) }}" class="admin-btn ghost sm">
                                    Voir le cours
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .admin-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        border-radius: 0.85rem;
        font-weight: 600;
        text-decoration: none;
        padding: 0.65rem 1.2rem;
        border: 1px solid transparent;
        transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.2s ease;
        color: inherit;
    }

    .admin-btn.primary {
        background: linear-gradient(90deg, #2563eb, #4f46e5);
        color: #ffffff;
        box-shadow: 0 22px 38px -28px rgba(37, 99, 235, 0.55);
    }

    .admin-btn.primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 26px 44px -28px rgba(37, 99, 235, 0.45);
    }

    .admin-btn.outline {
        border-color: rgba(37, 99, 235, 0.32);
        color: #2563eb;
        background: rgba(37, 99, 235, 0.08);
    }

    .admin-btn.soft {
        border-color: rgba(148, 163, 184, 0.4);
        background: rgba(148, 163, 184, 0.12);
        color: #0f172a;
        padding: 0.55rem 1rem;
        font-size: 0.85rem;
    }

    .admin-btn.ghost {
        border-color: rgba(37, 99, 235, 0.18);
        color: #2563eb;
        background: transparent;
    }

    .admin-btn.sm {
        padding: 0.5rem 0.9rem;
        border-radius: 0.75rem;
        font-size: 0.85rem;
    }

    .admin-btn.lg {
        padding: 0.82rem 1.65rem;
        font-size: 1.02rem;
        border-radius: 1rem;
    }

    .student-dashboard {
        display: flex;
        flex-direction: column;
        gap: 2rem;
    }

    .student-dashboard__stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.35rem;
    }

    .student-stat {
        padding: 1.4rem 1.65rem;
        border-radius: 1.2rem;
        background: #ffffff;
        border: 1px solid rgba(226, 232, 240, 0.7);
        box-shadow: 0 18px 45px -32px rgba(15, 23, 42, 0.18);
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }

    .student-stat:hover {
        transform: translateY(-3px);
        box-shadow: 0 28px 60px -45px rgba(37, 99, 235, 0.35);
    }

    .student-stat__label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        font-weight: 600;
        color: #64748b;
    }

    .student-stat__value {
        font-size: clamp(1.8rem, 1.5rem + 1vw, 2.2rem);
        font-weight: 700;
        color: #0f172a;
        line-height: 1.2;
    }

    .student-stat__hint {
        font-size: 0.85rem;
        color: #64748b;
    }

    .student-highlight {
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 1.8rem;
        background: linear-gradient(135deg, rgba(37, 99, 235, 0.12), rgba(79, 70, 229, 0.08));
        border: 1px solid rgba(37, 99, 235, 0.25);
    }

    .student-highlight.empty {
        background: linear-gradient(135deg, rgba(34, 197, 94, 0.12), rgba(59, 130, 246, 0.1));
        border-color: rgba(34, 197, 94, 0.3);
    }

    .student-highlight::before {
        content: "";
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at top right, rgba(59, 130, 246, 0.18), transparent 55%);
        opacity: 0.9;
    }

    .student-highlight.empty::before {
        background: radial-gradient(circle at top right, rgba(34, 197, 94, 0.22), rgba(59, 130, 246, 0.05));
    }

    .student-highlight__content {
        position: relative;
        z-index: 2;
        display: flex;
        flex-direction: column;
        gap: 1.2rem;
        width: 100%;
    }

    .student-highlight__eyebrow {
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.12em;
        color: #1e3a8a;
        font-weight: 700;
    }

    .student-highlight.empty .student-highlight__eyebrow {
        color: #15803d;
    }

    .student-highlight__title {
        font-size: clamp(1.8rem, 1.4rem + 1vw, 2.2rem);
        font-weight: 700;
        color: #0f172a;
        margin: 0;
    }

    .student-highlight__subtitle {
        margin: 0;
        color: #475569;
        font-size: 0.98rem;
    }

    .student-highlight__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
    }

    .student-progress {
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
    }

    .student-progress__meta {
        display: flex;
        justify-content: space-between;
        font-size: 0.82rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #475569;
        font-weight: 600;
    }

    .student-progress__bar {
        position: relative;
        width: 100%;
        height: 8px;
        border-radius: 999px;
        background: rgba(148, 163, 184, 0.3);
        overflow: hidden;
    }

    .student-progress__bar span {
        position: absolute;
        inset: 0;
        border-radius: inherit;
        background: linear-gradient(90deg, #22c55e, #0ea5e9);
    }

    .student-progress__value {
        font-size: 0.85rem;
        font-weight: 600;
        color: #2563eb;
        margin-top: 0.5rem;
        display: inline-block;
    }

    .student-progress__hint {
        font-size: 0.78rem;
        color: #64748b;
    }

    .student-dashboard__grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 1.5rem;
    }

    .student-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .student-card-header h3 {
        margin: 0 0 0.35rem;
    }

    .student-course-list,
    .student-order-list,
    .student-certificate-list,
    .student-recommendations {
        display: flex;
        flex-direction: column;
        gap: 1.1rem;
    }

    .student-course-item,
    .student-order-item,
    .student-certificate-item,
    .student-recommendations__item {
        display: grid;
        gap: 1rem;
        padding: 1.15rem 1.35rem;
        border-radius: 1.1rem;
        border: 1px solid rgba(226, 232, 240, 0.7);
        background: rgba(248, 250, 252, 0.6);
        transition: box-shadow 0.18s ease, transform 0.18s ease;
    }

    .student-course-item {
        grid-template-columns: minmax(0, 1.55fr) minmax(0, 1.2fr) auto;
        align-items: center;
    }

    .student-order-item {
        grid-template-columns: minmax(0, 1.3fr) auto auto;
        align-items: center;
    }

    .student-certificate-item {
        grid-template-columns: auto minmax(0, 1fr) auto;
        align-items: center;
    }

    .student-recommendations__item {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr);
        grid-template-rows: auto auto;
        column-gap: 1rem;
        row-gap: 0.6rem;
        align-items: center;
    }

    .student-recommendations__media {
        width: 56px;
        height: 56px;
        border-radius: 1rem;
        overflow: hidden;
        background: rgba(37, 99, 235, 0.12);
        display: grid;
        place-items: center;
        font-weight: 700;
        font-size: 1.1rem;
        color: #2563eb;
        flex-shrink: 0;
        grid-row: 1 / span 2;
    }

    .student-recommendations__media img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .student-recommendations__content {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .student-recommendations__actions {
        grid-column: 1 / span 2;
        display: flex;
        justify-content: flex-end;
    }

    .student-course-item:hover,
    .student-order-item:hover,
    .student-certificate-item:hover,
    .student-recommendations__item:hover {
        transform: translateY(-3px);
        box-shadow: 0 18px 45px -35px rgba(37, 99, 235, 0.28);
    }

    .student-course-item__meta {
        display: flex;
        gap: 1rem;
        align-items: center;
    }

    .student-course-item__icon {
        width: 48px;
        height: 48px;
        border-radius: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(37, 99, 235, 0.12);
        color: #2563eb;
        font-size: 1.2rem;
    }

    .student-course-item__meta h4 {
        font-size: 1rem;
        margin: 0 0 0.25rem;
        font-weight: 700;
        color: #0f172a;
    }

    .student-course-item__meta p {
        margin: 0;
        font-size: 0.85rem;
        color: #64748b;
    }

    .student-course-item__stats {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        font-size: 0.8rem;
        color: #475569;
    }

    .student-course-item__actions {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        align-items: flex-end;
    }

    .student-order-item__number {
        font-weight: 700;
        font-size: 0.95rem;
        color: #0f172a;
    }

    .student-order-item__meta {
        margin: 0.25rem 0 0;
        font-size: 0.82rem;
        color: #64748b;
    }

    .student-order-item__status,
    .student-order-item__actions {
        display: flex;
        gap: 0.5rem;
    }

    .student-certificate-item__icon {
        width: 50px;
        height: 50px;
        border-radius: 999px;
        background: rgba(250, 204, 21, 0.18);
        color: #d97706;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
    }

    .student-certificate-item__info h4 {
        margin: 0;
        font-size: 1rem;
        font-weight: 700;
        color: #0f172a;
    }

    .student-certificate-item__info p {
        margin: 0.3rem 0;
        font-size: 0.85rem;
        color: #475569;
    }

    .student-certificate-item__info span {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #94a3b8;
        font-weight: 600;
    }

    .student-recommendations__item h4 {
        margin: 0;
        font-size: 0.95rem;
        font-weight: 700;
        color: #0f172a;
    }

    .student-recommendations__item p {
        margin: 0.3rem 0 0;
        font-size: 0.82rem;
        color: #64748b;
    }

    .admin-empty-state {
        padding: 3rem 1.5rem;
        text-align: center;
        color: var(--student-muted);
        border: 1px dashed rgba(148, 163, 184, 0.35);
        border-radius: 1.25rem;
        background: rgba(255, 255, 255, 0.65);
    }

    .admin-empty-state i {
        font-size: 2.2rem;
        margin-bottom: 1rem;
        color: rgba(30, 58, 138, 0.35);
    }

    @media (max-width: 1080px) {
        .student-highlight {
            gap: 1.4rem;
        }
    }

    @media (max-width: 900px) {
        .student-course-item,
        .student-order-item,
        .student-certificate-item,
        .student-course-item__actions,
        .student-order-item__actions {
            align-items: flex-start;
        }

        .student-order-item__status {
            justify-content: flex-start;
        }
    }

    @media (max-width: 640px) {
        .admin-btn {
            width: 100%;
        }

        .student-card-header {
            flex-direction: column;
            align-items: stretch;
        }
    }
</style>
@endpush
 
