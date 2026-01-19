@extends('students.admin.layout')

@section('admin-title', 'Tableau de bord')
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
<section class="admin-panel admin-panel--main">
    <div class="admin-panel__body">
        <div class="admin-stats-grid">
            <div class="admin-stat-card">
                <p class="admin-stat-card__label">Contenus inscrits</p>
                <p class="admin-stat-card__value">{{ number_format($stats['enrolled_courses']) }}</p>
                <p class="admin-stat-card__muted">{{ $stats['active_courses'] }} en cours · {{ $stats['completed_courses'] }} terminés</p>
            </div>
            <div class="admin-stat-card">
                <p class="admin-stat-card__label">Contenus achetés</p>
                <p class="admin-stat-card__value">{{ number_format($stats['purchased_courses']) }}</p>
                <p class="admin-stat-card__muted">Total de vos achats</p>
            </div>
            <div class="admin-stat-card">
                <p class="admin-stat-card__label">Produits téléchargeables</p>
                <p class="admin-stat-card__value">{{ number_format($stats['purchased_downloadable_courses']) }}</p>
                <p class="admin-stat-card__muted">Contenus digitaux achetés</p>
            </div>
            <div class="admin-stat-card">
                <p class="admin-stat-card__label">Progression moyenne</p>
                <p class="admin-stat-card__value">{{ $stats['average_progress'] }}%</p>
                <p class="admin-stat-card__muted">Continuez sur cette lancée</p>
            </div>
            <div class="admin-stat-card">
                <p class="admin-stat-card__label">Certificats obtenus</p>
                <p class="admin-stat-card__value">{{ number_format($stats['certificates_earned']) }}</p>
                <p class="admin-stat-card__muted">
                    {{ max(0, 5 - $stats['certificates_earned']) }} avant le prochain palier
                </p>
            </div>
            <div class="admin-stat-card">
                <p class="admin-stat-card__label">Temps d'apprentissage</p>
                <p class="admin-stat-card__value">
                    {{ $stats['learning_minutes'] > 0 ? number_format(round($stats['learning_minutes'] / 60, 1), 1) : '0' }} h
                </p>
                <p class="admin-stat-card__muted">Durée cumulée de vos formations suivies</p>
            </div>
        </div>

        @if($lastUpdatedEnrollment && $lastUpdatedEnrollment->course)
            @php
                $highlightCourse = $lastUpdatedEnrollment->course;
            @endphp
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
                        @if(!($highlightCourse->is_downloadable ?? false))
                            <div class="student-progress">
                                <div class="student-progress__meta">
                                    <span>Progression</span>
                                    <strong>{{ $lastUpdatedEnrollment->progress }}%</strong>
                                </div>
                                <div class="student-progress__bar">
                                    <span style="width: {{ $lastUpdatedEnrollment->progress }}%"></span>
                                </div>
                                <small class="student-progress__hint">
                                    Dernière mise à jour {{ $lastUpdatedEnrollment->updated_at?->diffForHumans() }}
                                </small>
                            </div>
                        @endif
                    </div>
                    <div class="student-highlight__actions">
                        @php
                            $progress = $lastUpdatedEnrollment->progress ?? 0;
                        @endphp
                        @if($highlightCourse->is_downloadable ?? false)
                            <a href="{{ route('courses.download', $highlightCourse->slug) }}" class="admin-btn primary lg">
                                <i class="fas fa-download me-2"></i>Télécharger
                            </a>
                        @else
                            <a href="{{ route('learning.course', $highlightCourse->slug) }}" class="admin-btn success lg">
                                <i class="fas fa-play me-2"></i>{{ $progress > 0 ? 'Continuer' : 'Commencer' }}
                            </a>
                        @endif
                        <a href="{{ route('student.courses') }}" class="admin-btn ghost">
                            Voir tous mes contenus
                        </a>
                    </div>
                </div>
            </div>
        @else
        <div class="admin-card student-highlight empty">
            <div class="student-highlight__content">
                <div>
                    <span class="student-highlight__eyebrow">Commencez votre parcours</span>
                    <h2 class="student-highlight__title">Inscrivez-vous à votre première formation</h2>
                    <p class="student-highlight__subtitle">
                        Explorez des contenus adaptés à vos objectifs et avancez à votre rythme.
                    </p>
                </div>
                <div class="student-highlight__actions">
                    <a href="{{ route('courses.index') }}" class="admin-btn primary lg">
                        <i class="fas fa-compass me-2"></i>Explorer les contenus
                    </a>
                    <a href="{{ route('student.courses') }}" class="admin-btn ghost">
                        Voir l’espace de formation
                    </a>
                </div>
            </div>
        </div>
    @endif
    </div>
</section>

    <section class="admin-panel">
        <div class="admin-panel__header">
            <h3>
                <i class="fas fa-book-open me-2"></i>Mes contenus en cours
            </h3>
            <div class="admin-panel__actions">
                <a href="{{ route('student.courses') }}" class="admin-btn soft">
                    Voir tout
                </a>
            </div>
        </div>
        <div class="admin-panel__body">
            @if($enrollments->isEmpty())
                <div class="admin-empty-state">
                    <i class="fas fa-book-open"></i>
                    <p>Vous n'êtes inscrit à aucun contenu pour le moment.</p>
                    <a href="{{ route('courses.index') }}" class="admin-btn primary sm mt-3">
                        Trouver un contenu
                    </a>
                </div>
            @else
                <div class="student-course-list">
                    @foreach($enrollments as $enrollment)
                        @php
                            $course = $enrollment->course;
                        @endphp
                        @if($course)
                            <div class="student-course-item">
                                <div class="student-course-item__meta">
                                    <div class="student-course-item__thumbnail">
                                        @if($course->thumbnail_url)
                                            <img src="{{ $course->thumbnail_url }}" alt="{{ $course->title }}">
                                        @else
                                            <span>{{ $course->initials }}</span>
                                        @endif
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
                                    @if(!($course->is_downloadable ?? false))
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
                                        </div>
                                    @else
                                        <div class="student-course-item__stats">
                                            @if(isset($course->user_downloads_count))
                                                <span>
                                                    <i class="fas fa-download me-1"></i>
                                                    {{ $course->user_downloads_count }} téléchargements
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                                <div class="student-course-item__actions">
                                    @php
                                        $progress = $enrollment->progress ?? 0;
                                    @endphp
                                    @if($course->is_downloadable ?? false)
                                        <a href="{{ route('courses.download', $course->slug) }}" class="admin-btn primary sm">
                                            <i class="fas fa-download me-1"></i>Télécharger
                                        </a>
                                    @else
                                        <a href="{{ route('learning.course', $course->slug) }}" class="admin-btn success sm">
                                            <i class="fas fa-play me-1"></i>{{ $progress > 0 ? 'Continuer' : 'Commencer' }}
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <section class="admin-panel">
        <div class="admin-panel__header">
            <h3>
                <i class="fas fa-shopping-bag me-2"></i>Dernières commandes
            </h3>
            <div class="admin-panel__actions">
                <a href="{{ route('orders.index') }}" class="admin-btn soft">
                    Historique complet
                </a>
            </div>
        </div>
        <div class="admin-panel__body">
            @if($orders->isEmpty())
                <div class="admin-empty-state">
                    <i class="fas fa-shopping-bag"></i>
                    <p>Aucune commande enregistrée pour l’instant.</p>
                    <a href="{{ route('courses.index') }}" class="admin-btn primary sm mt-3">
                        Acheter un contenu
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
    </section>

    <section class="admin-panel">
        <div class="admin-panel__header">
            <h3>
                <i class="fas fa-certificate me-2"></i>Certificats récents
            </h3>
            <div class="admin-panel__actions">
                <a href="{{ route('student.certificates') }}" class="admin-btn soft">
                    Voir tous les certificats
                </a>
            </div>
        </div>
        <div class="admin-panel__body">
            @if($certificates->isEmpty())
                <div class="admin-empty-state">
                    <i class="fas fa-certificate"></i>
                    <p>Terminez une formation pour obtenir votre premier certificat.</p>
                    <a href="{{ route('student.courses') }}" class="admin-btn primary sm mt-3">
                        Continuer mes formations
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
    </section>

    <section class="admin-panel">
        <div class="admin-panel__header">
            <h3>
                <i class="fas fa-lightbulb me-2"></i>Recommandations pour vous
            </h3>
            <div class="admin-panel__actions">
                <a href="{{ route('courses.index') }}" class="admin-btn soft">
                    Explorer davantage
                </a>
            </div>
        </div>
        <div class="admin-panel__body">
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
                                    Voir le contenu
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
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
        background: linear-gradient(90deg, var(--student-primary), #0b4f99);
        color: #ffffff;
        box-shadow: 0 22px 38px -28px rgba(30, 58, 138, 0.55);
    }

    .admin-btn.primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 26px 44px -28px rgba(30, 58, 138, 0.45);
    }

    .admin-btn.success {
        background: linear-gradient(90deg, #22c55e, #16a34a);
        color: #ffffff;
        box-shadow: 0 22px 38px -28px rgba(34, 197, 94, 0.55);
    }

    .admin-btn.success:hover {
        transform: translateY(-2px);
        box-shadow: 0 26px 44px -28px rgba(34, 197, 94, 0.45);
        background: linear-gradient(90deg, #16a34a, #15803d);
    }

    .admin-btn.outline {
        border-color: rgba(30, 58, 138, 0.32);
        color: var(--student-primary);
        background: rgba(30, 58, 138, 0.08);
    }

    .admin-btn.soft {
        border-color: rgba(148, 163, 184, 0.4);
        background: rgba(148, 163, 184, 0.12);
        color: var(--student-primary-dark);
        padding: 0.55rem 1rem;
        font-size: 0.85rem;
    }

    .admin-btn.ghost {
        border-color: rgba(30, 58, 138, 0.3);
        color: #ffffff;
        background: var(--student-primary);
    }

    .admin-btn.ghost:hover {
        background: rgba(30, 58, 138, 0.9);
        border-color: var(--student-primary);
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

    .admin-panel {
        margin-bottom: 1.5rem;
        background: var(--student-card-bg);
        border-radius: 1.25rem;
        box-shadow: 0 22px 45px -35px rgba(15, 23, 42, 0.25);
        border: 1px solid rgba(226, 232, 240, 0.7);
    }

    .admin-panel__header {
        padding: 1.25rem 1.75rem;
        background: linear-gradient(120deg, var(--student-primary) 0%, var(--student-primary-dark) 100%);
        color: #ffffff;
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        justify-content: space-between;
        align-items: center;
        border-radius: 1.25rem 1.25rem 0 0;
    }

    .admin-panel__header h2,
    .admin-panel__header h3,
    .admin-panel__header h4 {
        margin: 0;
        font-weight: 600;
        color: #ffffff;
    }

    .admin-panel__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.65rem;
    }

    .admin-panel__actions .admin-btn.soft {
        color: #ffffff;
        background: rgba(255, 255, 255, 0.15);
        border-color: rgba(255, 255, 255, 0.3);
    }

    .admin-panel__actions .admin-btn.soft:hover {
        background: rgba(255, 255, 255, 0.25);
        border-color: rgba(255, 255, 255, 0.5);
    }
    
    .admin-panel__body {
        padding: 1.75rem;
    }

    .admin-card {
        background: #ffffff;
        border-radius: 16px;
        border: 1px solid rgba(226, 232, 240, 0.8);
    }
    
    .student-dashboard {
        display: flex;
        flex-direction: column;
        gap: 2rem;
    }

    .admin-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 1.25rem;
    }

    .admin-stat-card {
        background: linear-gradient(135deg, rgba(30, 58, 138, 0.07) 0%, rgba(30, 58, 138, 0.15) 100%);
        border-radius: 1rem;
        padding: 1rem 1.25rem;
        color: var(--student-primary-dark);
        border: 1px solid rgba(30, 58, 138, 0.1);
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }

    .admin-stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 28px 60px -45px rgba(30, 58, 138, 0.35);
    }

    .admin-stat-card__label {
        text-transform: uppercase;
        letter-spacing: 0.08em;
        font-size: 0.65rem;
        margin-bottom: 0.4rem;
        color: var(--student-primary);
        font-weight: 600;
    }

    .admin-stat-card__value {
        font-size: 1.5rem;
        font-weight: 700;
        margin: 0;
        color: var(--student-primary-dark);
        line-height: 1.2;
    }

    .admin-stat-card__muted {
        margin-top: 0.25rem;
        color: var(--student-muted);
        font-size: 0.8rem;
    }

    .student-highlight {
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 1.8rem;
        background: linear-gradient(135deg, rgba(30, 58, 138, 0.12), rgba(56, 189, 248, 0.08));
        border: 1px solid rgba(30, 58, 138, 0.25);
    }

    .student-highlight.empty {
        background: linear-gradient(135deg, rgba(34, 197, 94, 0.12), rgba(56, 189, 248, 0.1));
        border-color: rgba(34, 197, 94, 0.3);
    }

    .student-highlight::before {
        content: "";
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at top right, rgba(56, 189, 248, 0.18), transparent 55%);
        opacity: 0.9;
    }

    .student-highlight.empty::before {
        background: radial-gradient(circle at top right, rgba(34, 197, 94, 0.22), rgba(56, 189, 248, 0.05));
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
        color: var(--student-primary);
        font-weight: 700;
    }

    .student-highlight.empty .student-highlight__eyebrow {
        color: var(--student-accent);
    }

    .student-highlight__title {
        font-size: clamp(1.8rem, 1.4rem + 1vw, 2.2rem);
        font-weight: 700;
        color: var(--student-primary-dark);
        margin: 0;
    }

    .student-highlight__subtitle {
        margin: 0;
        color: var(--student-muted);
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
        color: var(--student-muted);
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
        background: linear-gradient(90deg, var(--student-accent), var(--student-secondary));
    }

    .student-progress__value {
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--student-primary);
        margin-top: 0.5rem;
        display: inline-block;
    }

    .student-progress__hint {
        font-size: 0.78rem;
        color: var(--student-muted);
    }

    .student-dashboard__grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 1.5rem;
    }

    .admin-card__header {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid rgba(226, 232, 240, 0.8);
        border-radius: 16px 16px 0 0;
    }

    .admin-card__title {
        margin: 0;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.75rem;
    }

    .admin-card__body {
        padding: 1.25rem;
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
        background: rgba(30, 58, 138, 0.12);
        display: grid;
        place-items: center;
        font-weight: 700;
        font-size: 1.1rem;
        color: var(--student-primary);
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
        box-shadow: 0 18px 45px -35px rgba(30, 58, 138, 0.28);
    }

    .student-course-item__meta {
        display: flex;
        gap: 1rem;
        align-items: center;
    }

    .student-course-item__thumbnail {
        width: 56px;
        height: 56px;
        border-radius: 1rem;
        overflow: hidden;
        background: rgba(30, 58, 138, 0.12);
        display: grid;
        place-items: center;
        font-weight: 700;
        font-size: 1.1rem;
        color: var(--student-primary);
        flex-shrink: 0;
    }

    .student-course-item__thumbnail img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .student-course-item__meta h4 {
        font-size: 1rem;
        margin: 0 0 0.25rem;
        font-weight: 700;
        color: var(--student-primary-dark);
    }

    .student-course-item__meta p {
        margin: 0;
        font-size: 0.85rem;
        color: var(--student-muted);
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
        color: var(--student-primary-dark);
    }

    .student-order-item__meta {
        margin: 0.25rem 0 0;
        font-size: 0.82rem;
        color: var(--student-muted);
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
        color: var(--student-primary-dark);
    }

    .student-certificate-item__info p {
        margin: 0.3rem 0;
        font-size: 0.85rem;
        color: var(--student-muted);
    }

    .student-certificate-item__info span {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--student-muted);
        font-weight: 600;
    }

    .student-recommendations__item h4 {
        margin: 0;
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--student-primary-dark);
    }

    .student-recommendations__item p {
        margin: 0.3rem 0 0;
        font-size: 0.82rem;
        color: var(--student-muted);
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

    /* Styles responsives pour les paddings et margins */
    @media (max-width: 991.98px) {
        /* Réduire les paddings et margins sur tablette */
        .admin-panel {
            margin-bottom: 1rem;
        }
        
        /* Padding réduit pour la première section principale */
        .admin-panel--main .admin-panel__body {
            padding: 1rem 0.5rem !important;
        }
        
        .student-dashboard {
            gap: 1.25rem;
        }
        
        .admin-stats-grid {
            gap: 0.5rem !important;
        }
        
        .admin-stat-card {
            padding: 0.75rem 0.875rem !important;
        }
        
        .admin-stat-card__value {
            font-size: 1.5rem;
        }
        
        .admin-panel__header {
            padding: 0.75rem 1rem;
        }

        .admin-panel__header h3 {
            font-size: 1rem;
        }

        .admin-card__header {
            padding: 0.75rem 1rem;
        }
        
        .admin-card__body {
            padding: 1rem;
        }
        
        .admin-panel__body {
            padding: 1rem;
        }
        
        .student-course-list,
        .student-order-list,
        .student-certificate-list,
        .student-recommendations {
            gap: 0.75rem;
        }
        
        .student-course-item,
        .student-order-item,
        .student-certificate-item,
        .student-recommendations__item {
            padding: 0.875rem 1rem;
        }
        
        .student-highlight {
            gap: 1.2rem;
        }
    }

    @media (max-width: 767.98px) {
        /* Réduire encore plus les paddings et margins sur mobile */
        .admin-panel {
            margin-bottom: 0.75rem;
        }
        
        /* Padding réduit pour la première section principale */
        .admin-panel--main .admin-panel__body {
            padding: 0.75rem 0.25rem !important;
        }
        
        .student-dashboard {
            gap: 1rem;
        }
        
        .admin-stats-grid {
            gap: 0.375rem !important;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        }
        
        .admin-stat-card {
            padding: 0.5rem 0.625rem !important;
        }
        
        .admin-stat-card__value {
            font-size: 1.35rem;
        }
        
        .admin-stat-card__label {
            font-size: 0.7rem;
        }
        
        .admin-stat-card__muted {
            font-size: 0.75rem;
        }
        
        .admin-panel__header {
            padding: 0.5rem 0.75rem;
        }

        .admin-panel__header h3 {
            font-size: 0.95rem;
        }

        .admin-card__header {
            padding: 0.5rem 0.75rem;
        }
        
        .admin-card__header .admin-card__title {
            font-size: 0.95rem;
        }
        
        .admin-card__body {
            padding: 0.5rem;
        }
        
        .admin-panel__body {
            padding: 0.5rem 0.25rem;
        }
        
        .student-course-list,
        .student-order-list,
        .student-certificate-list,
        .student-recommendations {
            gap: 0.5rem;
            padding: 0;
        }
        
        .student-course-item,
        .student-order-item,
        .student-certificate-item,
        .student-recommendations__item {
            padding: 0.5rem;
            align-items: flex-start;
        }
        
        .student-course-item {
            grid-template-columns: 1fr;
            gap: 0.75rem;
        }
        
        .student-order-item {
            grid-template-columns: 1fr;
            gap: 0.75rem;
        }
        
        .student-certificate-item {
            grid-template-columns: 1fr;
            gap: 0.75rem;
        }
        
        .student-course-item__actions,
        .student-order-item__actions {
            width: 100%;
            align-items: stretch;
        }
        
        .student-order-item__status {
            justify-content: flex-start;
        }
        
        .student-highlight {
            gap: 1rem;
        }
        
        .student-highlight__title {
            font-size: 1.4rem;
        }
        
        .student-highlight__subtitle {
            font-size: 0.9rem;
        }
        
        .student-highlight__actions {
            flex-direction: column;
        }
        
        .admin-btn {
            width: 100%;
            padding: 0.5rem 0.75rem;
            font-size: 0.8rem;
        }

        .admin-btn.sm {
            padding: 0.4rem 0.7rem;
            font-size: 0.75rem;
        }

        .admin-btn.lg {
            padding: 0.6rem 1.2rem;
            font-size: 0.9rem;
        }

        .admin-panel__actions .admin-btn {
            width: auto;
            font-size: 0.75rem;
            padding: 0.4rem 0.7rem;
        }
        
        .student-course-item__meta h4 {
            font-size: 0.85rem;
        }
        
        .student-course-item__meta p {
            font-size: 0.75rem;
        }
        
        .student-course-item__stats {
            font-size: 0.7rem;
            gap: 0.4rem;
        }

        .student-course-item__thumbnail {
            width: 48px;
            height: 48px;
            font-size: 0.95rem;
        }

        .student-highlight__eyebrow {
            font-size: 0.7rem;
        }

        .student-highlight__title {
            font-size: 1.25rem;
        }

        .student-highlight__subtitle {
            font-size: 0.85rem;
        }

        .student-progress__meta {
            font-size: 0.75rem;
        }

        .student-progress__value {
            font-size: 0.75rem;
        }

        .student-progress__hint {
            font-size: 0.7rem;
        }

        .student-order-item__number {
            font-size: 0.85rem;
        }

        .student-order-item__meta {
            font-size: 0.75rem;
        }

        .student-certificate-item__info h4 {
            font-size: 0.85rem;
        }

        .student-certificate-item__info p {
            font-size: 0.75rem;
        }

        .student-certificate-item__info span {
            font-size: 0.7rem;
        }

        .student-recommendations__item h4 {
            font-size: 0.85rem;
        }

        .student-recommendations__item p {
            font-size: 0.75rem;
        }

        .student-recommendations__media {
            width: 48px;
            height: 48px;
            font-size: 0.95rem;
        }
        
        .admin-empty-state {
            padding: 1.5rem 0.75rem;
        }
        
        .admin-empty-state i {
            font-size: 1.5rem;
        }

        .admin-empty-state p {
            font-size: 0.85rem;
        }

        /* Ajuster les boutons du header sur mobile */
        .admin-header .admin-btn {
            width: 100%;
            font-size: 0.8rem;
            padding: 0.5rem 0.75rem;
        }

        .admin-header .admin-btn i {
            font-size: 0.75rem;
        }
    }
</style>
@endpush
 
