@extends('layouts.admin')

@section('title', 'Détails du cours - Admin')
@section('admin-title', $course->title)
@section('admin-subtitle', 'Créé le ' . $course->created_at->format('d/m/Y') . ' • Dernière mise à jour le ' . $course->updated_at->format('d/m/Y'))

@section('admin-actions')
    <div class="admin-actions-grid">
        <a href="{{ route('admin.courses.edit', $course) }}" class="btn btn-primary">
            <i class="fas fa-edit me-2"></i>Modifier
        </a>
        <a href="{{ route('courses.show', $course) }}" class="btn btn-outline-secondary" target="_blank">
            <i class="fas fa-external-link-alt me-2"></i>Voir sur le site
        </a>
        <a href="{{ route('admin.courses') }}" class="btn btn-light">
            <i class="fas fa-arrow-left me-2"></i>Retour
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

    <div class="row g-4">
        <div class="col-md-8">
            <section class="admin-panel">
                <div class="admin-panel__header">
                    <h3>
                        <i class="fas fa-book me-2"></i>Informations du cours
                    </h3>
                </div>
                <div class="admin-panel__body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Titre</dt>
                        <dd class="col-sm-8">{{ $course->title }}</dd>

                        @if($course->subtitle)
                            <dt class="col-sm-4">Sous-titre</dt>
                            <dd class="col-sm-8">{{ $course->subtitle }}</dd>
                        @endif

                        <dt class="col-sm-4">Description</dt>
                        <dd class="col-sm-8">{{ $course->description ?? 'N/A' }}</dd>

                        <dt class="col-sm-4">Statut</dt>
                        <dd class="col-sm-8">
                            <span class="badge {{ $course->is_published ? 'bg-success' : 'bg-warning text-dark' }}">
                                {{ $course->is_published ? 'Publié' : 'Brouillon' }}
                            </span>
                            @if($course->is_featured)
                                <span class="badge bg-info ms-2">En vedette</span>
                            @endif
                            <span class="badge {{ $course->is_free ? 'bg-success' : 'bg-primary' }} ms-2">
                                {{ $course->is_free ? 'Gratuit' : 'Payant' }}
                            </span>
                            @if($course->is_downloadable)
                                <span class="badge bg-info ms-2">Téléchargeable</span>
                            @endif
                        </dd>

                        <dt class="col-sm-4">Prix</dt>
                        <dd class="col-sm-8">
                            {{ $course->is_free ? 'Gratuit' : \App\Helpers\CurrencyHelper::formatWithSymbol($course->current_price ?? $course->price ?? 0, $course->currency ?? 'USD') }}
                            @if(!$course->is_free && $course->is_sale_active && $course->active_sale_price !== null && $course->active_sale_price < $course->price)
                                <span class="text-muted ms-2">
                                    (Promotion : {{ \App\Helpers\CurrencyHelper::formatWithSymbol($course->active_sale_price, $course->currency ?? 'USD') }})
                                </span>
                            @endif
                        </dd>

                        <dt class="col-sm-4">Slug</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-secondary fs-6">{{ $course->slug }}</span>
                        </dd>

                        <dt class="col-sm-4">Date de création</dt>
                        <dd class="col-sm-8">
                            {{ $course->created_at->format('d/m/Y à H:i') }}
                        </dd>

                        <dt class="col-sm-4">Dernière mise à jour</dt>
                        <dd class="col-sm-8">
                            {{ $course->updated_at->format('d/m/Y à H:i') }}
                        </dd>
                    </dl>
                </div>
            </section>

            @if($course->description || !empty($tagsList))
                <section class="admin-panel">
                    <div class="admin-panel__header">
                        <h3>
                            <i class="fas fa-align-left me-2"></i>Description et tags
                        </h3>
                    </div>
                    <div class="admin-panel__body">
                        @if($course->description)
                            <div class="mb-3">{!! nl2br(e($course->description)) !!}</div>
                        @endif

                        @if(!empty($tagsList))
                            <div>
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
                </section>
            @endif

            @if($videoPreviewUrl || $downloadResource || $course->use_external_payment)
                <section class="admin-panel">
                    <div class="admin-panel__header">
                        <h3>
                            <i class="fas fa-photo-video me-2"></i>Ressources et médias
                        </h3>
                    </div>
                    <div class="admin-panel__body">
                        <dl class="row mb-0">
                            @if($videoPreviewUrl)
                                <dt class="col-sm-4">Aperçu vidéo</dt>
                                <dd class="col-sm-8">
                                    <a href="{{ $videoPreviewUrl }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-play-circle me-1"></i>Ouvrir la prévisualisation
                                    </a>
                                </dd>
                            @endif

                            @if($downloadResource)
                                <dt class="col-sm-4">Ressource téléchargeable</dt>
                                <dd class="col-sm-8">
                                    <a href="{{ $downloadResource }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-download me-1"></i>Télécharger
                                    </a>
                                </dd>
                            @endif

                            @if($course->use_external_payment && $course->external_payment_url)
                                <dt class="col-sm-4">Lien de paiement externe</dt>
                                <dd class="col-sm-8">
                                    <a href="{{ $course->external_payment_url }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-link me-1"></i>{{ $course->external_payment_text ?? 'Accéder au lien' }}
                                    </a>
                                </dd>
                            @endif
                        </dl>
                    </div>
                </section>
            @endif

            <section class="admin-panel">
                <div class="admin-panel__header">
                    <h3>
                        <i class="fas fa-list me-2"></i>Contenu pédagogique
                    </h3>
                </div>
                <div class="admin-panel__body">
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
            </section>
        </div>

        <div class="col-md-4">
            <section class="admin-panel">
                <div class="admin-panel__header">
                    <h3>
                        <i class="fas fa-chart-bar me-2"></i>Statistiques
                    </h3>
                </div>
                <div class="admin-panel__body">
                    <dl class="row mb-0">
                        <dt class="col-sm-6">Leçons</dt>
                        <dd class="col-sm-6">{{ $totalLessons }}</dd>

                        <dt class="col-sm-6">Sections</dt>
                        <dd class="col-sm-6">{{ $sectionsCollection->count() }}</dd>

                        <dt class="col-sm-6">Durée totale</dt>
                        <dd class="col-sm-6">{{ $totalDuration > 0 ? $totalDuration . ' min' : '—' }}</dd>

                        <dt class="col-sm-6">Étudiants inscrits</dt>
                        <dd class="col-sm-6">
                            {{ $totalStudents !== null ? number_format($totalStudents, 0, ',', ' ') : '—' }}
                        </dd>

                        <dt class="col-sm-6">Note moyenne</dt>
                        <dd class="col-sm-6">
                            {{ $averageRating ? number_format($averageRating, 1, ',', ' ') : '—' }}
                        </dd>

                        <dt class="col-sm-6">Avis</dt>
                        <dd class="col-sm-6">
                            {{ $totalReviews ? number_format($totalReviews, 0, ',', ' ') : 'Aucun' }}
                        </dd>
                    </dl>
                </div>
            </section>

            <section class="admin-panel">
                <div class="admin-panel__header">
                    <h3>
                        <i class="fas fa-user-tie me-2"></i>Instructeur
                    </h3>
                </div>
                <div class="admin-panel__body">
                    <div class="d-flex align-items-center gap-3">
                        <img src="{{ $course->instructor->avatar ?? asset('images/default-avatar.svg') }}" 
                             alt="{{ $course->instructor->name ?? 'Instructeur' }}" 
                             class="rounded-circle"
                             style="width: 80px; height: 80px; object-fit: cover;">
                        <div>
                            <h5 class="mb-1">{{ $course->instructor->name ?? 'Non assigné' }}</h5>
                            @if($course->instructor)
                                <a href="{{ route('admin.users.show', $course->instructor) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye me-1"></i>Voir le profil
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </section>

            <section class="admin-panel">
                <div class="admin-panel__header">
                    <h3>
                        <i class="fas fa-info-circle me-2"></i>Informations clés
                    </h3>
                </div>
                <div class="admin-panel__body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5">Catégorie</dt>
                        <dd class="col-sm-7">{{ $course->category->name ?? 'Non définie' }}</dd>

                        <dt class="col-sm-5">Langue</dt>
                        <dd class="col-sm-7">{{ $languageLabel }}</dd>

                        <dt class="col-sm-5">Niveau</dt>
                        <dd class="col-sm-7">
                            <span class="badge bg-info">{{ ucfirst($course->level ?? 'N/A') }}</span>
                        </dd>
                    </dl>
                </div>
            </section>

            <section class="admin-panel">
                <div class="admin-panel__header">
                    <h3>
                        <i class="fas fa-toggle-on me-2"></i>Options du cours
                    </h3>
                </div>
                <div class="admin-panel__body">
                    <dl class="row mb-0">
                        <dt class="col-sm-6">Gratuit</dt>
                        <dd class="col-sm-6">{{ $course->is_free ? 'Oui' : 'Non' }}</dd>

                        <dt class="col-sm-6">Téléchargeable</dt>
                        <dd class="col-sm-6">{{ $course->is_downloadable ? 'Oui' : 'Non' }}</dd>

                        <dt class="col-sm-6">Paiement externe</dt>
                        <dd class="col-sm-6">{{ $course->use_external_payment ? 'Activé' : 'Désactivé' }}</dd>

                        <dt class="col-sm-6">Vidéo non listée</dt>
                        <dd class="col-sm-6">{{ $course->video_preview_is_unlisted ? 'Oui' : 'Non' }}</dd>
                    </dl>
                </div>
            </section>

            @if(!empty($requirements))
                <section class="admin-panel">
                    <div class="admin-panel__header">
                        <h3>
                            <i class="fas fa-clipboard-list me-2"></i>Prérequis
                        </h3>
                    </div>
                    <div class="admin-panel__body">
                        <ul class="list-unstyled mb-0">
                            @foreach($requirements as $requirement)
                                <li class="mb-2">
                                    <i class="fas fa-check-circle text-success me-2"></i>{{ $requirement }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </section>
            @endif

            @if(!empty($learnings))
                <section class="admin-panel">
                    <div class="admin-panel__header">
                        <h3>
                            <i class="fas fa-lightbulb me-2"></i>Objectifs pédagogiques
                        </h3>
                    </div>
                    <div class="admin-panel__body">
                        <ul class="list-unstyled mb-0">
                            @foreach($learnings as $learning)
                                <li class="mb-2">
                                    <i class="fas fa-check-circle text-success me-2"></i>{{ $learning }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </section>
            @endif

            @if($course->meta_description || $course->meta_keywords)
                <section class="admin-panel">
                    <div class="admin-panel__header">
                        <h3>
                            <i class="fas fa-search me-2"></i>SEO
                        </h3>
                    </div>
                    <div class="admin-panel__body">
                        @if($course->meta_description)
                            <div class="mb-3">
                                <dt class="col-sm-12 mb-1"><strong>Meta description</strong></dt>
                                <dd class="col-sm-12 text-muted">{{ $course->meta_description }}</dd>
                            </div>
                        @endif
                        @if($course->meta_keywords)
                            <div>
                                <dt class="col-sm-12 mb-1"><strong>Mots-clés</strong></dt>
                                <dd class="col-sm-12 text-muted">{{ $course->meta_keywords }}</dd>
                            </div>
                        @endif
                    </div>
                </section>
            @endif
        </div>
    </div>
@endsection

@push('styles')
<style>
/* Styles identiques à analytics */
/* Grid pour les boutons admin-actions en colonnes */
.admin-actions-grid {
    display: grid !important;
    grid-template-columns: repeat(3, auto) !important;
    gap: 0.5rem !important;
    justify-content: center !important;
    width: 100% !important;
}

/* Réduire la taille des boutons admin-actions et ajouter bordure */
.admin-content__actions .btn,
.admin-actions-grid .btn {
    font-size: 0.9rem !important;
    padding: 0.4rem 0.5rem !important;
    white-space: nowrap !important;
    width: auto !important;
    min-width: fit-content !important;
    text-align: center !important;
}

/* Ajouter une bordure visible sur le bouton "Voir sur le site" */
.admin-content__actions .btn-outline-secondary,
.admin-actions-grid .btn-outline-secondary {
    border: 1px solid #6c757d !important;
    border-width: 1px !important;
}

/* Ajouter une bordure visible sur le bouton "Retour à la liste" */
.admin-content__actions .btn-light,
.admin-actions-grid .btn-light {
    border: 1px solid #dee2e6 !important;
    border-width: 1px !important;
}

/* Taille des icônes dans les boutons */
.admin-content__actions .btn i,
.admin-actions-grid .btn i {
    font-size: 0.8rem !important;
}

.admin-card {
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid rgba(226, 232, 240, 0.8);
}

.admin-card__header {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid rgba(226, 232, 240, 0.8);
    border-radius: 16px 16px 0 0;
}

/* Réduire l'espace au-dessus du contenu sur desktop */
@media (min-width: 992px) {
    .admin-card__header .admin-card__title.mb-1 {
        margin-bottom: 0.5rem !important;
    }
    
    .admin-card__header {
        padding-top: 0.75rem !important;
        padding-bottom: 0.75rem !important;
    }
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

/* Styles pour admin-panel - identiques à analytics */
.admin-panel {
    margin-bottom: 2rem;
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid rgba(226, 232, 240, 0.8);
}

.admin-panel__header {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid rgba(226, 232, 240, 0.8);
}

.admin-panel__header h3 {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.admin-panel__body {
    padding: 1rem;
}

/* Padding légèrement réduit sur desktop */
@media (min-width: 992px) {
    .admin-panel__body {
        padding: 0.875rem 1rem;
    }
}

/* Corriger le chevauchement des boutons dans la carte Informations du certificat */
.admin-panel__body dl.row dd {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    align-items: center;
}

.admin-panel__body dl.row dd .badge {
    flex-shrink: 0;
}

.admin-panel__body dl.row dd .btn,
.admin-panel__body dl.row dd button {
    flex-shrink: 0;
    white-space: nowrap;
}

/* Styles responsives pour les paddings et margins - identiques à analytics */
@media (max-width: 991.98px) {
    /* Réduire les paddings et margins sur tablette */
    .admin-panel {
        margin-bottom: 1rem;
    }
    
    .admin-panel__body {
        padding: 0 !important;
    }
    
    .admin-panel__header {
        padding: 0.5rem 0.75rem;
    }
    
    .admin-panel__header h3 {
        font-size: 1rem;
        margin-bottom: 0.25rem;
    }
    
    .admin-panel__body .row.g-4 {
        --bs-gutter-x: 0.5rem;
        --bs-gutter-y: 0.5rem;
    }
    
    .admin-card {
        margin-bottom: 0.5rem !important;
    }
    
    .admin-card__header {
        padding: 0.5rem 0.75rem;
    }
    
    .admin-card__body {
        padding: 0.5rem;
    }
}

@media (max-width: 767.98px) {
    /* Réduire encore plus les paddings et margins sur mobile */
    .admin-panel {
        margin-bottom: 0.75rem;
    }
    
    .admin-panel__body {
        padding: 1.25rem !important;
    }
    
    .admin-panel__header {
        padding: 0.375rem 0.5rem;
    }
    
    .admin-panel__header h3 {
        font-size: 0.95rem;
        margin-bottom: 0.125rem;
    }
    
    .admin-panel__body .row.g-4 {
        --bs-gutter-x: 0.375rem;
        --bs-gutter-y: 0.375rem;
    }
    
    .admin-card {
        margin-bottom: 0.5rem !important;
    }
    
    /* Garder le même design de carte que sur desktop - mêmes tailles */
    .admin-card__header {
        padding: 1rem 1.25rem !important;
    }
    
    .admin-card__body {
        padding: 1.25rem !important;
    }
    
    /* Empiler les boutons sur mobile dans la carte Informations du certificat */
    .admin-panel__body dl.row dd .btn,
    .admin-panel__body dl.row dd button {
        flex: 1 1 auto;
        min-width: 120px;
        margin-left: 0 !important;
        margin-right: 0 !important;
    }
    
    /* Centrer les boutons admin-actions sur mobile et les garder sur la même ligne */
    .admin-content__header {
        flex-direction: column !important;
        align-items: center !important;
        text-align: center !important;
    }
    
    .admin-content__header > div:first-child {
        width: 100% !important;
        text-align: center !important;
        margin-bottom: 1rem !important;
    }
    
    .admin-content__actions,
    .admin-actions-grid {
        display: grid !important;
        grid-template-columns: repeat(3, auto) !important;
        gap: 0.4rem !important;
        justify-content: center !important;
        width: 100% !important;
    }
    
    .admin-content__actions .btn,
    .admin-actions-grid .btn {
        width: auto !important;
        min-width: fit-content !important;
        white-space: nowrap !important;
        font-size: 0.85rem !important;
        padding: 0.35rem 0.4rem !important;
        text-align: center !important;
    }
    
    .admin-content__actions .btn i,
    .admin-actions-grid .btn i {
        margin-right: 0.3rem !important;
        font-size: 0.75rem !important;
    }
}

/* Styles pour le curriculum */
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

