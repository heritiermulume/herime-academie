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
    $purchasesCount = $stats['purchases_count'] ?? ($course->purchases_count ?? null);
    $averageRating = $stats['average_rating'] ?? null;
    $totalReviews = $stats['total_reviews'] ?? null;
    $totalDownloads = $stats['total_downloads'] ?? null;
    $uniqueDownloads = $stats['unique_downloads'] ?? null;
    $totalRevenue = $stats['total_revenue'] ?? null;

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
    $thumbnailUrl = $course->thumbnail_url ?: 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=1200&q=80';
@endphp

    <!-- Image du cours -->
    <section class="admin-panel mb-4">
        <div class="admin-panel__header">
            <h3>
                <i class="fas fa-image me-2"></i>Image du cours
            </h3>
        </div>
        <div class="admin-panel__body p-0">
            <div class="course-thumbnail-container">
                <img src="{{ $thumbnailUrl }}" alt="{{ $course->title }}" class="course-thumbnail-image">
            </div>
        </div>
    </section>

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
                            <span class="badge {{ ($course->is_sale_enabled ?? true) ? 'bg-success' : 'bg-secondary' }} ms-2">
                                {{ ($course->is_sale_enabled ?? true) ? 'Vente activée' : 'Vente désactivée' }}
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
                                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#previewVideoModal">
                                        <i class="fas fa-play-circle me-1"></i>Voir la prévisualisation
                                    </button>
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
                                            <div class="admin-table">
                                                <div class="table-responsive">
                                                    <table class="table align-middle mb-0">
                                                        <thead>
                                                            <tr>
                                                                <th>Leçon</th>
                                                                <th>Type</th>
                                                                <th>Durée</th>
                                                                <th>Statut</th>
                                                                <th class="text-center">Actions</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($sectionLessons as $lesson)
                                                                @php
                                                                    $lessonContentUrl = $lesson->content_url ?? null;
                                                                    $lessonContentFileUrl = $lesson->content_file_url ?? null;
                                                                    $lessonContentText = $lesson->content_text ?? null;
                                                                    $hasContent = $lessonContentUrl || $lessonContentFileUrl || $lessonContentText;
                                                                @endphp
                                                                <tr>
                                                                    <td style="min-width: 250px;">
                                                                        <div class="d-flex align-items-center gap-3">
                                                                            <div class="lesson-icon lesson-icon--{{ $lesson->type ?? 'video' }}" style="width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #ffffff; flex-shrink: 0;">
                                                                                <i class="fas fa-{{ match($lesson->type) {
                                                                                    'video' => 'play',
                                                                                    'text' => 'file-alt',
                                                                                    'quiz' => 'question-circle',
                                                                                    'pdf', 'document' => 'file-pdf',
                                                                                    default => 'file-alt'
                                                                                } }}" style="font-size: 0.85rem;"></i>
                                                                            </div>
                                                                            <div style="min-width: 0; flex: 1;">
                                                                                <div class="fw-semibold text-truncate d-block" title="{{ $lesson->title }}">{{ $lesson->title }}</div>
                                                                                @if($lesson->description)
                                                                                    <div class="text-muted small text-truncate d-block" title="{{ $lesson->description }}">
                                                                                        {{ Str::limit($lesson->description, 60) }}
                                                                                    </div>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                    <td>
                                                                        <span class="admin-chip admin-chip--info text-capitalize">
                                                                            {{ $lesson->type ?? 'contenu' }}
                                                                        </span>
                                                                    </td>
                                                                    <td>
                                                                        @if($lesson->duration)
                                                                            <span class="text-muted">
                                                                                <i class="far fa-clock me-1"></i>{{ $lesson->duration }} min
                                                                            </span>
                                                                        @else
                                                                            <span class="text-muted">—</span>
                                                                        @endif
                                                                    </td>
                                                                    <td>
                                                                        @if($lesson->is_preview)
                                                                            <span class="admin-chip admin-chip--success">
                                                                                <i class="fas fa-eye me-1"></i>Aperçu
                                                                            </span>
                                                                        @else
                                                                            <span class="admin-chip admin-chip--secondary">
                                                                                <i class="fas fa-lock me-1"></i>Verrouillé
                                                                            </span>
                                                                        @endif
                                                                    </td>
                                                                    <td class="text-center">
                                                                        @if($hasContent)
                                                                            <button type="button" class="btn btn-primary btn-sm" onclick="viewLesson({{ $lesson->id }}, '{{ $lesson->type }}', {{ json_encode($lessonContentUrl) }}, {{ json_encode($lessonContentFileUrl) }}, {{ json_encode($lessonContentText) }}, '{{ addslashes($lesson->title) }}')" title="Visualiser la leçon">
                                                                                <i class="fas fa-eye"></i>
                                                                            </button>
                                                                        @else
                                                                            <span class="text-muted small">—</span>
                                                                        @endif
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
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

                        <dt class="col-sm-6">Nombre d'achats</dt>
                        <dd class="col-sm-6">
                            {{ $purchasesCount !== null ? number_format($purchasesCount, 0, ',', ' ') : '—' }}
                        </dd>

                        @if($course->is_downloadable)
                        <dt class="col-sm-6">Téléchargements totaux</dt>
                        <dd class="col-sm-6">
                            {{ $totalDownloads !== null ? number_format($totalDownloads, 0, ',', ' ') : '—' }}
                        </dd>

                        <dt class="col-sm-6">Téléchargements uniques</dt>
                        <dd class="col-sm-6">
                            {{ $uniqueDownloads !== null ? number_format($uniqueDownloads, 0, ',', ' ') : '—' }}
                        </dd>

                        <dt class="col-sm-6">Revenus totaux</dt>
                        <dd class="col-sm-6">
                            {{ $totalRevenue !== null ? \App\Helpers\CurrencyHelper::formatWithSymbol($totalRevenue) : '—' }}
                        </dd>
                        @endif

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
                    <div class="d-flex align-items-start gap-3">
                        <img src="{{ $course->instructor->avatar ?? asset('images/default-avatar.svg') }}" 
                             alt="{{ $course->instructor->name ?? 'Instructeur' }}" 
                             class="rounded-circle flex-shrink-0"
                             style="width: 80px; height: 80px; object-fit: cover;">
                        <div class="flex-grow-1" style="min-width: 0;">
                            <h5 class="mb-2">{{ $course->instructor->name ?? 'Non assigné' }}</h5>
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

                        <dt class="col-sm-6">Vente et inscription</dt>
                        <dd class="col-sm-6">
                            @if($course->is_sale_enabled ?? true)
                                <span class="badge bg-success">Activée</span>
                            @else
                                <span class="badge bg-secondary">Désactivée</span>
                            @endif
                        </dd>

                        <dt class="col-sm-6">Téléchargeable</dt>
                        <dd class="col-sm-6">{{ $course->is_downloadable ? 'Oui' : 'Non' }}</dd>

                        <dt class="col-sm-6">Paiement externe</dt>
                        <dd class="col-sm-6">{{ $course->use_external_payment ? 'Activé' : 'Désactivé' }}</dd>

                        <dt class="col-sm-6">Vidéo non listée</dt>
                        <dd class="col-sm-6">{{ $course->video_preview_is_unlisted ? 'Oui' : 'Non' }}</dd>
                    </dl>
                </div>
            </section>

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

    <!-- Modal pour la prévisualisation vidéo du cours -->
    @if($videoPreviewUrl)
    <div class="modal fade" id="previewVideoModal" tabindex="-1" aria-labelledby="previewVideoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="previewVideoModalLabel">Prévisualisation du cours</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    @php
                        $isYoutube = str_contains($videoPreviewUrl, 'youtube.com') || str_contains($videoPreviewUrl, 'youtu.be');
                        $isVimeo = str_contains($videoPreviewUrl, 'vimeo.com');
                        $isDirectVideo = false;
                        $youtubeId = null;
                        $vimeoId = null;
                        
                        // Vérifier si c'est une URL directe vers un fichier vidéo
                        $videoExtensions = ['.mp4', '.webm', '.ogg', '.mov', '.avi', '.m3u8'];
                        $lowerUrl = strtolower($videoPreviewUrl);
                        foreach ($videoExtensions as $ext) {
                            if (str_contains($lowerUrl, $ext)) {
                                $isDirectVideo = true;
                                break;
                            }
                        }
                        
                        // Vérifier aussi si c'est une URL YouTube embed (doit être fait en premier)
                        if (str_contains($videoPreviewUrl, 'youtube.com/embed/')) {
                            $isYoutube = true;
                            $path = parse_url($videoPreviewUrl, PHP_URL_PATH);
                            $youtubeId = basename($path);
                            // Nettoyer l'ID si nécessaire
                            if (str_contains($youtubeId, '?')) {
                                $youtubeId = explode('?', $youtubeId)[0];
                            }
                        } elseif ($isYoutube) {
                            if (str_contains($videoPreviewUrl, 'youtube.com/watch')) {
                                parse_str(parse_url($videoPreviewUrl, PHP_URL_QUERY), $query);
                                $youtubeId = $query['v'] ?? null;
                            } elseif (str_contains($videoPreviewUrl, 'youtu.be/')) {
                                $youtubeId = basename(parse_url($videoPreviewUrl, PHP_URL_PATH));
                            } elseif (str_contains($videoPreviewUrl, 'youtube.com/v/')) {
                                $youtubeId = basename(parse_url($videoPreviewUrl, PHP_URL_PATH));
                            }
                        } elseif ($isVimeo) {
                            // Extraire l'ID Vimeo de différentes formats d'URL
                            if (str_contains($videoPreviewUrl, 'vimeo.com/')) {
                                $path = parse_url($videoPreviewUrl, PHP_URL_PATH);
                                $vimeoId = trim($path, '/');
                                // Si l'URL contient des paramètres, prendre seulement l'ID
                                if (str_contains($vimeoId, '/')) {
                                    $parts = explode('/', $vimeoId);
                                    $vimeoId = end($parts);
                                }
                            }
                        }
                    @endphp
                    <div class="ratio ratio-16x9">
                        @if($isYoutube && $youtubeId)
                            <iframe src="https://www.youtube.com/embed/{{ $youtubeId }}" 
                                    title="Prévisualisation du cours" 
                                    allowfullscreen></iframe>
                        @elseif($isVimeo && $vimeoId)
                            <iframe src="https://player.vimeo.com/video/{{ $vimeoId }}" 
                                    title="Prévisualisation du cours" 
                                    allowfullscreen></iframe>
                        @elseif($isDirectVideo)
                            <video controls class="w-100 h-100" style="object-fit: contain;">
                                <source src="{{ $videoPreviewUrl }}" type="video/mp4">
                                <source src="{{ $videoPreviewUrl }}" type="video/webm">
                                <source src="{{ $videoPreviewUrl }}" type="video/ogg">
                                Votre navigateur ne supporte pas la lecture vidéo.
                                <a href="{{ $videoPreviewUrl }}" target="_blank">Télécharger la vidéo</a>
                            </video>
                        @else
                            <div class="d-flex align-items-center justify-content-center bg-dark text-white">
                                <div class="text-center">
                                    <i class="fas fa-video fa-3x mb-3"></i>
                                    <p>Format vidéo non reconnu</p>
                                    <a href="{{ $videoPreviewUrl }}" target="_blank" class="btn btn-primary">
                                        Ouvrir dans un nouvel onglet
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Modal pour visualiser une leçon -->
    <div class="modal fade" id="lessonViewModal" tabindex="-1" aria-labelledby="lessonViewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="lessonViewModalTitle">Visualiser la leçon</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="lessonViewModalBody">
                    <!-- Le contenu sera injecté dynamiquement -->
                </div>
            </div>
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
    overflow: hidden;
}

/* S'assurer que le contenu de la section Instructeur reste dans les limites */
.admin-panel__body .d-flex {
    flex-wrap: wrap;
}

.admin-panel__body .d-flex > div {
    max-width: 100%;
}

/* Padding légèrement réduit sur desktop */
@media (min-width: 992px) {
    .admin-panel__body {
        padding: 0.875rem 1rem;
    }
}

/* S'assurer que les dt et dd s'affichent correctement */
.admin-panel__body dl.row {
    margin: 0;
}

.admin-panel__body dl.row dt {
    padding: 0.5rem 0.75rem 0.5rem 0;
    font-weight: 600;
    text-align: left;
}

.admin-panel__body dl.row dd {
    padding: 0.5rem 0;
    margin: 0;
}

/* Appliquer flex seulement pour les dd qui contiennent plusieurs éléments (badges, boutons) */
.admin-panel__body dl.row dd .badge + .badge,
.admin-panel__body dl.row dd .btn {
    margin-left: 0.5rem;
}

.admin-panel__body dl.row dd .badge:first-child {
    margin-left: 0;
}

.admin-panel__body dl.row dd .badge {
    display: inline-block;
    flex-shrink: 0;
}

.admin-panel__body dl.row dd .btn,
.admin-panel__body dl.row dd button {
    display: inline-block;
    flex-shrink: 0;
    white-space: nowrap;
    margin-left: 0;
}

/* Styles responsives pour les paddings et margins - identiques à analytics */
@media (max-width: 991.98px) {
    /* Supprimer les scrollbars des conteneurs, garder seulement celle de table-responsive */
    .admin-curriculum .admin-table {
        overflow: visible !important;
    }
    
    .admin-curriculum .curriculum-section-content {
        overflow: visible !important;
    }
    
    .admin-curriculum .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

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
        display: inline-block;
        margin-left: 0 !important;
        margin-right: 0.5rem !important;
    }
    
    /* S'assurer que les dt et dd s'affichent correctement sur mobile */
    .admin-panel__body dl.row dt {
        display: block;
        width: 100%;
        padding-bottom: 0.25rem;
    }
    
    .admin-panel__body dl.row dd {
        display: block;
        width: 100%;
        padding-bottom: 0.75rem;
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

.lesson-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
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

/* Styles pour le tableau des leçons dans les sections */
.admin-curriculum .admin-table {
    margin-top: 1rem;
}

.admin-curriculum .admin-table table {
    margin-bottom: 0;
}

/* Styles pour l'image du cours */
.course-thumbnail-container {
    position: relative;
    width: 100%;
    border-radius: 0 0 16px 16px;
    overflow: hidden;
    background: #f8fafc;
    aspect-ratio: 16 / 9;
    display: flex;
    align-items: center;
    justify-content: center;
}

.course-thumbnail-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}


/* Styles pour le modal de prévisualisation vidéo */
#previewVideoModal .modal-body {
    padding: 0;
}

#previewVideoModal .modal-body .ratio {
    border-radius: 0.5rem;
    overflow: hidden;
}

/* Styles pour le modal de visualisation de leçon */
#lessonViewModal .modal-body {
    max-height: 80vh;
    overflow-y: auto;
}

#lessonViewModal .lesson-video-container {
    position: relative;
    width: 100%;
    padding-bottom: 56.25%; /* 16:9 */
    height: 0;
    overflow: hidden;
    border-radius: 0.5rem;
    margin-bottom: 1rem;
}

#lessonViewModal .lesson-video-container iframe,
#lessonViewModal .lesson-video-container video {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    border: none;
}

#lessonViewModal .lesson-text-content {
    padding: 1.5rem;
    background: #f8fafc;
    border-radius: 0.5rem;
    white-space: pre-wrap;
    word-wrap: break-word;
}

#lessonViewModal .lesson-pdf-container {
    width: 100%;
    height: 80vh;
    border: none;
    border-radius: 0.5rem;
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

    // Fonction pour visualiser une leçon
    function viewLesson(lessonId, type, contentUrl, contentFileUrl, contentText, title) {
        const modal = new bootstrap.Modal(document.getElementById('lessonViewModal'));
        const modalTitle = document.getElementById('lessonViewModalTitle');
        const modalBody = document.getElementById('lessonViewModalBody');
        
        modalTitle.textContent = title;
        modalBody.innerHTML = '';
        
        let contentHtml = '';
        
        if (type === 'video') {
            if (contentUrl) {
                // Vérifier si c'est YouTube
                if (contentUrl.includes('youtube.com') || contentUrl.includes('youtu.be')) {
                    let videoId = '';
                    if (contentUrl.includes('youtube.com/watch')) {
                        const url = new URL(contentUrl);
                        videoId = url.searchParams.get('v');
                    } else if (contentUrl.includes('youtu.be/')) {
                        videoId = contentUrl.split('youtu.be/')[1].split('?')[0];
                    }
                    if (videoId) {
                        contentHtml = `
                            <div class="lesson-video-container">
                                <iframe src="https://www.youtube.com/embed/${videoId}" 
                                        title="${title}" 
                                        allowfullscreen></iframe>
                            </div>
                        `;
                    }
                } else if (contentUrl.includes('vimeo.com')) {
                    const videoId = contentUrl.split('vimeo.com/')[1].split('?')[0];
                    if (videoId) {
                        contentHtml = `
                            <div class="lesson-video-container">
                                <iframe src="https://player.vimeo.com/video/${videoId}" 
                                        title="${title}" 
                                        allowfullscreen></iframe>
                            </div>
                        `;
                    }
                } else if (contentFileUrl) {
                    contentHtml = `
                        <div class="lesson-video-container">
                            <video controls class="w-100 h-100" style="object-fit: contain;">
                                <source src="${contentFileUrl}" type="video/mp4">
                                Votre navigateur ne supporte pas la lecture vidéo.
                            </video>
                        </div>
                    `;
                }
            } else if (contentFileUrl) {
                contentHtml = `
                    <div class="lesson-video-container">
                        <video controls class="w-100 h-100" style="object-fit: contain;">
                            <source src="${contentFileUrl}" type="video/mp4">
                            Votre navigateur ne supporte pas la lecture vidéo.
                        </video>
                    </div>
                `;
            }
        } else if (type === 'text' && contentText) {
            contentHtml = `
                <div class="lesson-text-content">
                    ${contentText.replace(/\n/g, '<br>')}
                </div>
            `;
        } else if ((type === 'pdf' || type === 'document') && (contentFileUrl || contentUrl)) {
            const pdfUrl = contentFileUrl || contentUrl;
            contentHtml = `
                <iframe src="${pdfUrl}" class="lesson-pdf-container" title="${title}">
                    <p>Votre navigateur ne supporte pas l'affichage de PDF. 
                    <a href="${pdfUrl}" target="_blank">Télécharger le PDF</a></p>
                </iframe>
            `;
        } else {
            contentHtml = `
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Aucun contenu disponible pour cette leçon.
                </div>
            `;
        }
        
        modalBody.innerHTML = contentHtml;
        modal.show();
    }
</script>
@endpush

