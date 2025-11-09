@extends('layouts.admin')

@section('title', 'Détails du cours - Admin')
@section('admin-title', $course->title)
@section('admin-subtitle', 'Créé le ' . $course->created_at->format('d/m/Y') . ' • Dernière mise à jour le ' . $course->updated_at->format('d/m/Y'))

@section('admin-actions')
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('admin.courses.edit', $course) }}" class="btn btn-primary">
            <i class="fas fa-edit me-2"></i>Modifier
        </a>
        <a href="{{ route('courses.show', $course) }}" class="btn btn-outline-secondary" target="_blank">
            <i class="fas fa-external-link-alt me-2"></i>Voir sur le site
        </a>
        <a href="{{ route('admin.courses') }}" class="btn btn-light">
            <i class="fas fa-arrow-left me-2"></i>Retour à la liste
        </a>
    </div>
@endsection

@section('admin-content')
@php
    use Illuminate\Support\Str;

    $stats = $course->stats ?? [];
    $sectionsCollection = collect($course->sections ?? []);
    $lessonsCollection = $sectionsCollection->flatMap(fn ($section) => collect($section->lessons ?? []));

    $totalLessons = $stats['total_lessons'] ?? $lessonsCollection->count();
    $totalDuration = $stats['total_duration'] ?? $lessonsCollection->sum(fn ($lesson) => (int) ($lesson->duration ?? 0));
    $totalStudents = $stats['total_students'] ?? ($course->enrollments_count ?? null);
    $averageRating = $stats['average_rating'] ?? null;
    $totalReviews = $stats['total_reviews'] ?? null;

    $requirements = $course->getRequirementsArray();
    $learnings = $course->getWhatYouWillLearnArray();

    $tagsRaw = $course->tags;
    $tagsList = [];
    if (is_array($tagsRaw)) {
        $tagsList = array_values(array_filter($tagsRaw));
    } elseif (is_string($tagsRaw) && trim($tagsRaw) !== '') {
        $decoded = json_decode($tagsRaw, true);
        if (is_array($decoded)) {
            $tagsList = array_values(array_filter($decoded));
        } else {
            $tagsList = array_values(array_filter(array_map('trim', explode(',', $tagsRaw))));
        }
    }

    $languageMap = [
        'fr' => 'Français',
        'en' => 'Anglais',
    ];
    $languageLabel = $languageMap[$course->language] ?? ucfirst($course->language ?? 'Inconnu');

    $videoPreviewUrl = $course->video_preview_url ?: null;
    if (!$videoPreviewUrl && !empty($course->video_preview_youtube_id)) {
        $videoPreviewUrl = 'https://www.youtube.com/watch?v=' . $course->video_preview_youtube_id;
    }

    $downloadResource = $course->download_file_url ?: null;
@endphp

<section class="admin-panel admin-panel--flush">
    <div class="admin-panel__body">
        <div class="admin-course-hero shadow-sm rounded-4 overflow-hidden">
            <div class="row g-0 align-items-stretch">
                <div class="col-12 col-lg-4">
                    <div class="admin-course-hero__media {{ $course->thumbnail_url ? '' : 'admin-course-hero__media--empty' }}"
                         @if($course->thumbnail_url)
                             style="background-image: url('{{ $course->thumbnail_url }}');"
                         @endif>
                        @unless($course->thumbnail_url)
                            <div class="admin-course-hero__placeholder text-center">
                                <i class="fas fa-image fa-2x mb-2"></i>
                                <span>Miniature non disponible</span>
                            </div>
                        @endunless
                    </div>
                </div>
                <div class="col-12 col-lg-8">
                    <div class="admin-course-hero__content">
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                            @if($course->category)
                                <span class="admin-chip admin-chip--info">
                                    <i class="fas fa-folder-open me-1"></i>{{ $course->category->name }}
                                </span>
                            @endif
                            <span class="admin-chip admin-chip--neutral">
                                <i class="fas fa-layer-group me-1"></i>{{ ucfirst($course->level) }}
                            </span>
                            <span class="admin-chip admin-chip--neutral">
                                <i class="fas fa-language me-1"></i>{{ $languageLabel }}
                            </span>
                            <span class="admin-chip admin-chip--neutral">
                                <i class="fas fa-hashtag me-1"></i>{{ $course->slug }}
                            </span>
                        </div>

                        @if($course->subtitle)
                            <p class="admin-course-hero__subtitle mb-3">{{ $course->subtitle }}</p>
                        @endif

                        <div class="d-flex flex-wrap gap-2">
                            <span class="admin-status-badge {{ $course->is_published ? 'admin-status-badge--success' : 'admin-status-badge--warning' }}">
                                <i class="fas {{ $course->is_published ? 'fa-check-circle' : 'fa-clock' }} me-2"></i>
                                {{ $course->is_published ? 'Publié' : 'Brouillon' }}
                            </span>
                            @if($course->is_featured)
                                <span class="admin-status-badge admin-status-badge--info">
                                    <i class="fas fa-star me-2"></i>En vedette
                                </span>
                            @endif
                            <span class="admin-status-badge {{ $course->is_free ? 'admin-status-badge--success' : 'admin-status-badge--primary' }}">
                                <i class="fas {{ $course->is_free ? 'fa-gift' : 'fa-dollar-sign' }} me-2"></i>
                                {{ $course->is_free ? 'Gratuit' : 'Payant' }}
                            </span>
                            @if($course->is_downloadable)
                                <span class="admin-status-badge admin-status-badge--info">
                                    <i class="fas fa-download me-2"></i>Téléchargeable
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="admin-panel">
    <div class="admin-panel__body">
        <div class="admin-stats-grid admin-stats-grid--compact">
            <div class="admin-stat-card">
                <p class="admin-stat-card__label">Prix actuel</p>
                <p class="admin-stat-card__value">
                    {{ $course->is_free ? 'Gratuit' : \App\Helpers\CurrencyHelper::formatWithSymbol($course->current_price ?? $course->price ?? 0, $course->currency ?? 'USD') }}
                </p>
                @if(!$course->is_free && $course->sale_price && $course->sale_price < $course->price)
                    <p class="admin-stat-card__muted">
                        Promotion : {{ \App\Helpers\CurrencyHelper::formatWithSymbol($course->sale_price, $course->currency ?? 'USD') }}
                    </p>
                @endif
            </div>
            <div class="admin-stat-card">
                <p class="admin-stat-card__label">Leçons</p>
                <p class="admin-stat-card__value">{{ $totalLessons }}</p>
                <p class="admin-stat-card__muted">Sections : {{ $sectionsCollection->count() }}</p>
            </div>
            <div class="admin-stat-card">
                <p class="admin-stat-card__label">Durée totale</p>
                <p class="admin-stat-card__value">{{ $totalDuration > 0 ? $totalDuration . ' min' : '—' }}</p>
                <p class="admin-stat-card__muted">Cours complet</p>
            </div>
            <div class="admin-stat-card">
                <p class="admin-stat-card__label">Étudiants inscrits</p>
                <p class="admin-stat-card__value">
                    {{ $totalStudents !== null ? number_format($totalStudents, 0, ',', ' ') : '—' }}
                </p>
                <p class="admin-stat-card__muted">Apprenants</p>
            </div>
            <div class="admin-stat-card">
                <p class="admin-stat-card__label">Note moyenne</p>
                <p class="admin-stat-card__value">
                    {{ $averageRating ? number_format($averageRating, 1, ',', ' ') : '—' }}
                </p>
                <p class="admin-stat-card__muted">
                    {{ $totalReviews ? number_format($totalReviews, 0, ',', ' ') . ' avis' : 'Aucun avis' }}
                </p>
            </div>
            <div class="admin-stat-card">
                <p class="admin-stat-card__label">Dernière activité</p>
                <p class="admin-stat-card__value">{{ $course->updated_at->diffForHumans() }}</p>
                <p class="admin-stat-card__muted">ID #{{ $course->id }}</p>
            </div>
        </div>
    </div>
</section>

<section class="admin-panel">
    <div class="admin-panel__body">
        <div class="row g-4">
            <div class="col-12 col-xl-8 d-flex flex-column gap-4">
                @if($course->description || !empty($tagsList))
                    <div class="card shadow-sm border-0 admin-detail-card">
                        <div class="card-header bg-transparent border-0 pb-0">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-align-left me-2 text-primary"></i>Description du cours
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($course->description)
                                <div class="admin-rich-text">{!! nl2br(e($course->description)) !!}</div>
                            @endif

                            @if(!empty($tagsList))
                                <div class="mt-3">
                                    <h6 class="text-uppercase text-muted mb-2">
                                        <i class="fas fa-tags me-2"></i>Tags
                                    </h6>
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($tagsList as $tag)
                                            <span class="badge bg-secondary-subtle text-secondary-emphasis px-3 py-2">
                                                {{ $tag }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

            @if($videoPreviewUrl || $downloadResource || $course->use_external_payment)
                <div class="card shadow-sm border-0 admin-detail-card">
                    <div class="card-header bg-transparent border-0 pb-0">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-photo-video me-2 text-primary"></i>Ressources et médias
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-column gap-3">
                            @if($videoPreviewUrl)
                                <div>
                                    <h6 class="text-muted text-uppercase mb-2">Aperçu vidéo</h6>
                                    <a href="{{ $videoPreviewUrl }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-play-circle me-2"></i>Ouvrir la prévisualisation
                                    </a>
                                </div>
                            @endif

                            @if($downloadResource)
                                <div>
                                    <h6 class="text-muted text-uppercase mb-2">Ressource téléchargeable</h6>
                                    <a href="{{ $downloadResource }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-download me-2"></i>Télécharger
                                    </a>
                                </div>
                            @endif

                            @if($course->use_external_payment && $course->external_payment_url)
                                <div>
                                    <h6 class="text-muted text-uppercase mb-2">Lien de paiement externe</h6>
                                    <a href="{{ $course->external_payment_url }}" target="_blank" rel="noopener" class="btn btn-outline-info btn-sm">
                                        <i class="fas fa-link me-2"></i>{{ $course->external_payment_text ?? 'Accéder au lien' }}
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

                <div class="card shadow-sm border-0 admin-detail-card">
                    <div class="card-header bg-transparent border-0 pb-0">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-list me-2 text-primary"></i>Contenu pédagogique
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($sectionsCollection->isEmpty())
                            <p class="text-muted mb-0">Aucune section définie pour ce cours.</p>
                        @else
                            <div class="admin-curriculum">
                                @foreach($sectionsCollection as $index => $section)
                                    @php
                                        $sectionLessons = collect($section->lessons ?? [])->sortBy('sort_order');
                                        $sectionDuration = $sectionLessons->sum(fn ($lesson) => (int) ($lesson->duration ?? 0));
                                    @endphp
                                    <div class="curriculum-section">
                                        <button type="button"
                                                class="curriculum-section-header"
                                                data-section-target="section{{ $section->id }}"
                                                aria-expanded="{{ $index === 0 ? 'true' : 'false' }}">
                                            <div class="d-flex align-items-center gap-3">
                                                <i class="fas fa-chevron-down"></i>
                                                <i class="fas fa-chevron-right"></i>
                                                <span class="fw-semibold">{{ $section->title }}</span>
                                            </div>
                                            <div class="curriculum-section-stats">
                                                <span><i class="fas fa-play-circle me-1"></i>{{ $sectionLessons->count() }}</span>
                                                <span><i class="fas fa-clock me-1"></i>{{ $sectionDuration }} min</span>
                                            </div>
                                        </button>
                                        <div class="curriculum-section-content {{ $index === 0 ? 'is-open' : '' }}"
                                             id="section{{ $section->id }}">
                                            @if($section->description)
                                                <p class="text-muted mb-3">{{ $section->description }}</p>
                                            @endif
                                            @if($sectionLessons->isEmpty())
                                                <p class="text-muted mb-0">Pas de leçons dans cette section.</p>
                                            @else
                                                <ul class="curriculum-lessons list-unstyled mb-0">
                                                    @foreach($sectionLessons as $lesson)
                                                        <li class="lesson-item">
                                                            <div class="lesson-icon lesson-icon--{{ $lesson->type ?? 'video' }}">
                                                                <i class="fas fa-{{ match($lesson->type) {
                                                                    'video' => 'play',
                                                                    'text' => 'file-alt',
                                                                    'quiz' => 'question-circle',
                                                                    'pdf', 'document' => 'file-pdf',
                                                                    default => 'file-alt'
                                                                } }}"></i>
                                                            </div>
                                                            <div class="lesson-content">
                                                                <div class="lesson-title">{{ $lesson->title }}</div>
                                                                <div class="lesson-meta">
                                                                    <span class="badge bg-primary-subtle text-primary-emphasis text-capitalize">
                                                                        {{ $lesson->type ?? 'contenu' }}
                                                                    </span>
                                                                    @if($lesson->duration)
                                                                        <span class="text-muted ms-3">
                                                                            <i class="far fa-clock me-1"></i>{{ $lesson->duration }} min
                                                                        </span>
                                                                    @endif
                                                                    @if($lesson->is_preview)
                                                                        <span class="lesson-preview-badge ms-3">
                                                                            <i class="fas fa-eye me-1"></i>Aperçu
                                                                        </span>
                                                                    @endif
                                                                </div>
                                                                @if($lesson->description)
                                                                    <p class="lesson-description text-muted mb-0">
                                                                        {{ Str::limit($lesson->description, 180) }}
                                                                    </p>
                                                                @endif
                                                            </div>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-4 d-flex flex-column gap-4">
                <div class="card shadow-sm border-0 admin-detail-card">
                    <div class="card-header bg-transparent border-0 pb-0">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-info-circle me-2 text-primary"></i>Informations clés
                        </h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled admin-detail-list mb-0">
                            <li>
                                <span class="admin-detail-list__label">Instructeur</span>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $course->instructor->avatar ?? asset('images/default-avatar.svg') }}"
                                         alt="{{ $course->instructor->name ?? 'Instructeur' }}" class="rounded-circle"
                                         width="36" height="36">
                                    <span>{{ $course->instructor->name ?? 'Non assigné' }}</span>
                                </div>
                            </li>
                            <li>
                                <span class="admin-detail-list__label">Catégorie</span>
                                <span>{{ $course->category->name ?? 'Non définie' }}</span>
                            </li>
                            <li>
                                <span class="admin-detail-list__label">Langue</span>
                                <span>{{ $languageLabel }}</span>
                            </li>
                            <li>
                                <span class="admin-detail-list__label">Niveau</span>
                                <span class="text-capitalize">{{ $course->level }}</span>
                            </li>
                            <li>
                                <span class="admin-detail-list__label">Slug</span>
                                <span class="text-monospace">{{ $course->slug }}</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="card shadow-sm border-0 admin-detail-card">
                    <div class="card-header bg-transparent border-0 pb-0">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-toggle-on me-2 text-primary"></i>Options du cours
                        </h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled admin-detail-list mb-0">
                            <li>
                                <span class="admin-detail-list__label">Gratuit</span>
                                <span>{{ $course->is_free ? 'Oui' : 'Non' }}</span>
                            </li>
                            <li>
                                <span class="admin-detail-list__label">Téléchargeable</span>
                                <span>{{ $course->is_downloadable ? 'Oui' : 'Non' }}</span>
                            </li>
                            <li>
                                <span class="admin-detail-list__label">Paiement externe</span>
                                <span>{{ $course->use_external_payment ? 'Activé' : 'Désactivé' }}</span>
                            </li>
                            <li>
                                <span class="admin-detail-list__label">Vidéo non listée</span>
                                <span>{{ $course->video_preview_is_unlisted ? 'Oui' : 'Non' }}</span>
                            </li>
                        </ul>
                    </div>
                </div>

                @if(!empty($requirements))
                    <div class="card shadow-sm border-0 admin-detail-card">
                        <div class="card-header bg-transparent border-0 pb-0">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-clipboard-list me-2 text-primary"></i>Prérequis
                            </h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled admin-bullet-list mb-0">
                                @foreach($requirements as $requirement)
                                    <li>{{ $requirement }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                @if(!empty($learnings))
                    <div class="card shadow-sm border-0 admin-detail-card">
                        <div class="card-header bg-transparent border-0 pb-0">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-lightbulb me-2 text-primary"></i>Objectifs pédagogiques
                            </h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled admin-bullet-list mb-0">
                                @foreach($learnings as $learning)
                                    <li>{{ $learning }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                @if($course->meta_description || $course->meta_keywords)
                    <div class="card shadow-sm border-0 admin-detail-card">
                        <div class="card-header bg-transparent border-0 pb-0">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-search me-2 text-primary"></i>SEO
                            </h5>
                        </div>
                        <div class="card-body d-flex flex-column gap-3">
                            @if($course->meta_description)
                                <div>
                                    <h6 class="text-muted text-uppercase mb-1">Meta description</h6>
                                    <p class="mb-0 text-muted">{{ $course->meta_description }}</p>
                                </div>
                            @endif
                            @if($course->meta_keywords)
                                <div>
                                    <h6 class="text-muted text-uppercase mb-1">Mots-clés</h6>
                                    <p class="mb-0 text-muted">{{ $course->meta_keywords }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <div class="card shadow-sm border-0 admin-detail-card">
                    <div class="card-header bg-transparent border-0 pb-0">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-calendar-alt me-2 text-primary"></i>Historique
                        </h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled admin-detail-list mb-0">
                            <li>
                                <span class="admin-detail-list__label">Création</span>
                                <span>{{ $course->created_at->format('d/m/Y H:i') }}</span>
                            </li>
                            <li>
                                <span class="admin-detail-list__label">Dernière mise à jour</span>
                                <span>{{ $course->updated_at->format('d/m/Y H:i') }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
    .admin-course-hero {
        background: #ffffff;
    }
    .admin-course-hero__media {
        min-height: 220px;
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
    }
    .admin-course-hero__media--empty {
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #e2e8f0 0%, #f8fafc 100%);
        color: #64748b;
        padding: 2.5rem 1.5rem;
    }
    .admin-course-hero__placeholder span {
        display: block;
        font-size: 0.95rem;
        font-weight: 500;
    }
    .admin-course-hero__content {
        padding: 2rem 2.5rem;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        background: #ffffff;
    }
    .admin-course-hero__subtitle {
        font-size: 1.05rem;
        color: #334155;
        line-height: 1.6;
    }
    .admin-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        border-radius: 999px;
        font-weight: 600;
        padding: 0.45rem 0.95rem;
        font-size: 0.85rem;
        background: rgba(15, 23, 42, 0.08);
        color: #0f172a;
    }
    .admin-status-badge--success {
        background: rgba(34, 197, 94, 0.12);
        color: #15803d;
    }
    .admin-status-badge--warning {
        background: rgba(250, 204, 21, 0.12);
        color: #b45309;
    }
    .admin-status-badge--info {
        background: rgba(59, 130, 246, 0.12);
        color: #1d4ed8;
    }
    .admin-status-badge--primary {
        background: rgba(14, 116, 144, 0.12);
        color: #0f4c75;
    }
    .admin-detail-card .card-header {
        border-bottom: none;
        display: flex;
        justify-content: flex-start;
        align-items: center;
        text-align: left;
        padding-bottom: 0;
    }
    .admin-detail-card .card-title {
        font-size: 1rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.6rem;
        margin-bottom: 0;
    }
    .admin-rich-text {
        line-height: 1.75;
        color: #475569;
        font-size: 0.98rem;
    }
    .admin-rich-text p {
        margin-bottom: 1rem;
    }
    .admin-lessons-list {
        margin: 0;
        padding: 0;
    }
    .admin-lessons-list__item {
        display: flex;
        gap: 1rem;
        padding: 0.9rem 0;
        border-bottom: 1px solid rgba(226, 232, 240, 0.7);
    }
    .admin-lessons-list__item:last-child {
        border-bottom: none;
    }
    .admin-lessons-list__icon {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(59, 130, 246, 0.12);
        color: #2563eb;
        flex-shrink: 0;
    }
    .admin-lessons-list__content h6 {
        font-weight: 600;
    }
    .admin-detail-list {
        display: grid;
        gap: 1rem;
    }
    .admin-detail-list__label {
        display: block;
        font-size: 0.75rem;
        text-transform: uppercase;
        color: #94a3b8;
        letter-spacing: 0.08em;
        margin-bottom: 0.35rem;
        font-weight: 600;
    }
    .admin-bullet-list {
        display: grid;
        gap: 0.75rem;
        padding-left: 0;
        margin: 0;
    }
    .admin-bullet-list li {
        list-style: none;
        position: relative;
        padding-left: 1.5rem;
        color: #475569;
    }
    .admin-bullet-list li::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0.6rem;
        width: 8px;
        height: 8px;
        border-radius: 999px;
        background: #2563eb;
    }
    .admin-accordion-item {
        border: 1px solid rgba(226, 232, 240, 0.9);
        border-radius: 16px;
        overflow: hidden;
        margin-bottom: 0.85rem;
    }
    .admin-accordion-item .accordion-button {
        font-weight: 600;
        padding: 0.95rem 1.15rem;
        background: #f8fafc;
    }
    .admin-accordion-item .accordion-button:not(.collapsed) {
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.12) 0%, rgba(59, 130, 246, 0.05) 100%);
        color: #1d4ed8;
    }
    .admin-accordion-item .accordion-body {
        padding: 1.25rem;
        background: #ffffff;
    }
    .admin-curriculum .curriculum-section {
        border: 1px solid rgba(226, 232, 240, 0.8);
        border-radius: 1rem;
        overflow: hidden;
        margin-bottom: 1rem;
        background: #ffffff;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .admin-curriculum .curriculum-section:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 32px rgba(15, 23, 42, 0.08);
    }
    .admin-curriculum .curriculum-section-header {
        width: 100%;
        background: #003366;
        color: #ffffff;
        border: none;
        padding: 1.05rem 1.25rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        text-align: left;
        cursor: pointer;
        gap: 1rem;
        font-weight: 600;
        transition: background 0.2s ease;
    }
    .admin-curriculum .curriculum-section-header:hover {
        background: #002147;
    }
    .admin-curriculum .curriculum-section-header:focus-visible {
        outline: 2px solid rgba(59, 130, 246, 0.6);
        outline-offset: 2px;
    }
    .admin-curriculum .curriculum-section-header i {
        color: #facc15;
    }
    .admin-curriculum .curriculum-section-header[aria-expanded="false"] .fa-chevron-down {
        display: none;
    }
    .admin-curriculum .curriculum-section-header[aria-expanded="true"] .fa-chevron-right {
        display: none;
    }
    .admin-curriculum .curriculum-section-content {
        display: none;
        padding: 1.25rem;
        background: #f8fafc;
    }
    .admin-curriculum .curriculum-section-content.is-open {
        display: block;
    }
    .curriculum-section-stats {
        display: flex;
        gap: 1rem;
        font-size: 0.9rem;
    }
    .curriculum-section-stats span {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-weight: 500;
    }
    .curriculum-lessons .lesson-item {
        display: flex;
        gap: 1rem;
        padding: 0.9rem 1rem;
        background: #ffffff;
        border-radius: 12px;
        border: 1px solid rgba(226, 232, 240, 0.9);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        margin-bottom: 0.75rem;
    }
    .curriculum-lessons .lesson-item:last-child {
        margin-bottom: 0;
    }
    .curriculum-lessons .lesson-item:hover {
        transform: translateX(4px);
        box-shadow: 0 10px 20px rgba(15, 23, 42, 0.08);
    }
    .lesson-icon {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        flex-shrink: 0;
    }
    .lesson-icon--video { background: #2563eb; }
    .lesson-icon--text { background: #16a34a; }
    .lesson-icon--quiz { background: #f97316; }
    .lesson-icon--pdf { background: #dc2626; }
    .lesson-icon i { font-size: 1rem; }
    .lesson-content .lesson-title {
        font-weight: 600;
        margin-bottom: 0.35rem;
    }
    .lesson-content .lesson-meta {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.85rem;
        color: #64748b;
        margin-bottom: 0.35rem;
    }
    .lesson-preview-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.2rem 0.65rem;
        border-radius: 999px;
        background: rgba(34, 197, 94, 0.12);
        color: #15803d;
        font-weight: 600;
        font-size: 0.78rem;
    }
    .lesson-description {
        font-size: 0.88rem;
        line-height: 1.5;
    }
    .admin-stats-grid--compact .admin-stat-card__label {
        font-size: 0.75rem;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }
    @media (max-width: 991px) {
        .admin-course-hero__content {
            padding: 1.75rem;
        }
        .admin-course-hero__media {
            min-height: 200px;
        }
    }
    @media (max-width: 575px) {
        .admin-course-hero__content {
            padding: 1.5rem;
        }
        .admin-status-badge {
            font-size: 0.8rem;
            padding: 0.4rem 0.75rem;
        }
        .admin-lessons-list__item {
            flex-direction: column;
        }
        .admin-lessons-list__icon {
            width: 36px;
            height: 36px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const headers = document.querySelectorAll('.admin-curriculum .curriculum-section-header');

        const toggleSection = (header) => {
            const targetId = header.getAttribute('data-section-target');
            const content = document.getElementById(targetId);
            const isExpanded = header.getAttribute('aria-expanded') === 'true';

            if (isExpanded) {
                header.setAttribute('aria-expanded', 'false');
                content?.classList.remove('is-open');
            } else {
                header.setAttribute('aria-expanded', 'true');
                content?.classList.add('is-open');
            }
        };

        headers.forEach((header) => {
            header.addEventListener('click', () => toggleSection(header));
            header.addEventListener('keydown', (event) => {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    toggleSection(header);
                }
            });
        });
    });
</script>
@endpush

