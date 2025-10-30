@extends('layouts.app')

@section('title', 'Détails du cours - Admin')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-lg">
                <div class="card-header text-white" style="background: linear-gradient(135deg, #003366 0%, #004080 100%);">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div>
                            <h4 class="mb-1">
                                <i class="fas fa-graduation-cap me-2"></i>{{ $course->title }}
                            </h4>
                            <small class="opacity-75">
                                <i class="fas fa-clock me-1"></i>Créé le {{ $course->created_at->format('d/m/Y') }}
                            </small>
                        </div>
                        <div class="d-flex gap-2 mt-2 mt-md-0">
                            <a href="{{ route('admin.courses.lessons', $course) }}" class="btn btn-outline-light btn-sm">
                                <i class="fas fa-list me-1"></i>Leçons
                            </a>
                            <a href="{{ route('admin.courses.edit', $course) }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit me-1"></i>Modifier
                            </a>
                            <a href="{{ route('admin.courses') }}" class="btn btn-light btn-sm">
                                <i class="fas fa-arrow-left me-1"></i>Retour
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Informations principales -->
                        <div class="col-md-8">
                            <!-- Image de couverture -->
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-gradient-primary text-white">
                                    <h5 class="mb-0"><i class="fas fa-image me-2"></i>Image de couverture</h5>
                                </div>
                                <div class="card-body">
                                    
                                    @if($course->thumbnail)
                                        <div class="text-center">
                                            <img src="{{ $course->thumbnail }}" 
                                                 alt="{{ $course->title }}" 
                                                 class="img-fluid rounded course-thumbnail" 
                                                 style="max-height: 400px; width: 100%; object-fit: cover; border: 3px solid #28a745;">
                                        </div>
                                    @else
                                        <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                             style="height: 250px;">
                                            <div class="text-center text-muted">
                                                <i class="fas fa-image fa-4x mb-3 opacity-50"></i>
                                                <p class="mb-0">Aucune image de couverture</p>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-gradient-info text-white">
                                    <h5 class="mb-0"><i class="fas fa-align-left me-2"></i>Description</h5>
                                </div>
                                <div class="card-body">
                                    <div class="course-description">
                                        {!! nl2br(e($course->description)) !!}
                                    </div>
                                    
                                    <!-- Tags -->
                                    @php
                                        $tagsRaw = $course->tags;
                                        $tagsList = [];
                                        if (is_array($tagsRaw)) {
                                            $tagsList = $tagsRaw;
                                        } elseif (is_string($tagsRaw)) {
                                            // Essayer JSON d'abord
                                            $decoded = null;
                                            if (str_contains($tagsRaw, '[') || str_contains($tagsRaw, '"')) {
                                                $decoded = json_decode($tagsRaw, true);
                                            }
                                            if (is_array($decoded)) {
                                                $tagsList = $decoded;
                                            } else {
                                                // Fallback: CSV "tag1, tag2"
                                                $parts = array_map('trim', explode(',', $tagsRaw));
                                                $tagsList = array_values(array_filter($parts, fn($v) => $v !== ''));
                                            }
                                        }
                                    @endphp
                                    @if(!empty($tagsList))
                                        <div class="mt-3">
                                            <strong class="d-block mb-2"><i class="fas fa-tags me-2 text-primary"></i>Tags :</strong>
                                            @foreach($tagsList as $tag)
                                                <span class="badge bg-secondary me-1 mb-1 px-3 py-2">{{ $tag }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Sections et leçons -->
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-gradient-success text-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-list me-2"></i>Contenu du cours
                                    </h5>
                                </div>
                                <div class="card-body">
                                    
                                    @if($course->sections->count() > 0)
                                        <div class="accordion" id="courseAccordion">
                                            @foreach($course->sections as $sectionIndex => $section)
                                                <div class="accordion-item">
                                                    <h2 class="accordion-header" id="heading{{ $sectionIndex }}">
                                                        <button class="accordion-button {{ $sectionIndex > 0 ? 'collapsed' : '' }}" 
                                                                type="button" 
                                                                data-bs-toggle="collapse" 
                                                                data-bs-target="#collapse{{ $sectionIndex }}" 
                                                                aria-expanded="{{ $sectionIndex === 0 ? 'true' : 'false' }}" 
                                                                aria-controls="collapse{{ $sectionIndex }}">
                                                            <div class="d-flex justify-content-between w-100 me-3">
                                                                <span>{{ $section->title }}</span>
                                                                <span class="badge bg-primary">{{ $section->lessons->count() }} leçons</span>
                                                            </div>
                                                        </button>
                                                    </h2>
                                                    <div id="collapse{{ $sectionIndex }}" 
                                                         class="accordion-collapse collapse {{ $sectionIndex === 0 ? 'show' : '' }}" 
                                                         aria-labelledby="heading{{ $sectionIndex }}" 
                                                         data-bs-parent="#courseAccordion">
                                                        <div class="accordion-body">
                                                            @if($section->description)
                                                                <p class="text-muted mb-3">{{ $section->description }}</p>
                                                            @endif
                                                            
                                                            @if($section->lessons->count() > 0)
                                                                <div class="list-group">
                                                                    @foreach($section->lessons as $lesson)
                                                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                                                            <div class="d-flex align-items-center">
                                                                                <i class="fas fa-{{ $lesson->type === 'video' ? 'play-circle' : ($lesson->type === 'quiz' ? 'question-circle' : 'file-text') }} me-3 text-primary"></i>
                                                                                <div>
                                                                                    <h6 class="mb-1">{{ $lesson->title }}</h6>
                                                                                    @if($lesson->description)
                                                                                        <small class="text-muted">{{ $lesson->description }}</small>
                                                                                    @endif
                                                                                </div>
                                                                            </div>
                                                                            <div class="d-flex align-items-center">
                                                                                @if($lesson->is_preview)
                                                                                    <span class="badge bg-success me-2">Aperçu</span>
                                                                                @endif
                                                                                @if($lesson->duration > 0)
                                                                                    <small class="text-muted me-2">{{ $lesson->duration }} min</small>
                                                                                @endif
                                                                                <span class="badge bg-{{ $lesson->type === 'video' ? 'primary' : ($lesson->type === 'quiz' ? 'warning' : 'info') }}">
                                                                                    {{ ucfirst($lesson->type) }}
                                                                                </span>
                                                                            </div>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            @else
                                                                <p class="text-muted">Aucune leçon dans cette section.</p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle me-2"></i>
                                            Aucune section définie pour ce cours.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Informations secondaires -->
                        <div class="col-md-4">
                            <!-- Statut et actions -->
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-gradient-warning text-white">
                                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Statut et actions</h6>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex flex-column gap-2">
                                        @if($course->is_published)
                                            <span class="badge bg-success py-2 px-3">
                                                <i class="fas fa-check-circle me-1"></i>Publié
                                            </span>
                                        @else
                                            <span class="badge bg-warning py-2 px-3">
                                                <i class="fas fa-clock me-1"></i>Brouillon
                                            </span>
                                        @endif
                                        
                                        @if($course->is_featured)
                                            <span class="badge bg-info py-2 px-3">
                                                <i class="fas fa-star me-1"></i>En vedette
                                            </span>
                                        @endif
                                        
                                        @if($course->is_free)
                                            <span class="badge bg-success py-2 px-3">
                                                <i class="fas fa-gift me-1"></i>Gratuit
                                            </span>
                                        @else
                                            <span class="badge bg-primary py-2 px-3">
                                                <i class="fas fa-dollar-sign me-1"></i>Payant
                                            </span>
                                        @endif
                                        
                                        <a href="{{ route('courses.show', $course) }}" class="btn btn-primary mt-2" target="_blank">
                                            <i class="fas fa-external-link-alt me-1"></i>Voir le cours
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Informations du cours -->
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-gradient-info text-white">
                                    <h6 class="mb-0"><i class="fas fa-graduation-cap me-2"></i>Informations</h6>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled mb-0">
                                        <li class="mb-2">
                                            <strong>Instructeur:</strong><br>
                                            <div class="d-flex align-items-center mt-1">
                                                <img src="{{ $course->instructor->avatar ? $course->instructor->avatar : asset('images/default-avatar.svg') }}" 
                                                     alt="{{ $course->instructor->name }}" 
                                                     class="rounded-circle me-2" 
                                                     width="30" 
                                                     height="30">
                                                <span>{{ $course->instructor->name }}</span>
                                            </div>
                                        </li>
                                        <li class="mb-2">
                                            <strong>Catégorie:</strong><br>
                                            <span class="badge bg-{{ $course->category->color ?? 'primary' }} mt-1">
                                                {{ $course->category->name }}
                                            </span>
                                        </li>
                                        <li class="mb-2">
                                            <strong>Niveau:</strong><br>
                                            <span class="text-capitalize">{{ $course->level }}</span>
                                        </li>
                                        <li class="mb-2">
                                            <strong>Langue:</strong><br>
                                            {{ $course->language === 'fr' ? 'Français' : 'English' }}
                                        </li>
                                        <li class="mb-2">
                                            <strong>Durée totale:</strong><br>
                                            {{ $course->stats['total_duration'] ?? 0 }} minutes
                                        </li>
                                        <li class="mb-2">
                                            <strong>Nombre de leçons:</strong><br>
                                            {{ $course->stats['total_lessons'] ?? 0 }}
                                        </li>
                                        <li class="mb-2">
                                            <strong>Étudiants inscrits:</strong><br>
                                            {{ $course->stats['total_students'] ?? 0 }}
                                        </li>
                                        @if(($course->stats['average_rating'] ?? 0) > 0)
                                        <li class="mb-2">
                                            <strong>Note moyenne:</strong><br>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-star text-warning me-1"></i>
                                                <span>{{ number_format($course->stats['average_rating'] ?? 0, 1) }}</span>
                                                <small class="text-muted ms-1">({{ $course->stats['total_reviews'] ?? 0 }} avis)</small>
                                            </div>
                                        </li>
                                        @endif
                                    </ul>
                                </div>
                            </div>

                            <!-- Prix -->
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-gradient-success text-white">
                                    <h6 class="mb-0"><i class="fas fa-dollar-sign me-2"></i>Prix</h6>
                                </div>
                                <div class="card-body">
                                    @if($course->is_free)
                                        <h4 class="text-success mb-0">Gratuit</h4>
                                    @else
                                        <h4 class="text-primary mb-0">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($course->current_price) }}</h4>
                                        @if($course->sale_price && $course->sale_price < $course->price)
                                            <small class="text-muted">
                                                <s>{{ \App\Helpers\CurrencyHelper::formatWithSymbol($course->price) }}</s>
                                            </small>
                                            <br>
                                            <span class="badge bg-danger">
                                                -{{ round((($course->price - $course->sale_price) / $course->price) * 100) }}%
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </div>

                            <!-- Prérequis -->
                            @php $requirements = $course->getRequirementsArray(); @endphp
                            @if(count($requirements) > 0)
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-gradient-warning text-white">
                                    <h6 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>Prérequis</h6>
                                </div>
                                <div class="card-body">
                                    <ul class="mb-0">
                                        @foreach($requirements as $requirement)
                                            <li>{{ $requirement }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                            @endif

                            <!-- Objectifs -->
                            @php $learnings = $course->getWhatYouWillLearnArray(); @endphp
                            @if(count($learnings) > 0)
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-gradient-primary text-white">
                                    <h6 class="mb-0"><i class="fas fa-lightbulb me-2"></i>Ce que vous apprendrez</h6>
                                </div>
                                <div class="card-body">
                                    <ul class="mb-0">
                                        @foreach($learnings as $learning)
                                            <li>{{ $learning }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                            @endif

                            <!-- SEO -->
                            @if($course->meta_description || $course->meta_keywords)
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-gradient-secondary text-white">
                                    <h6 class="mb-0"><i class="fas fa-search me-2"></i>SEO</h6>
                                </div>
                                <div class="card-body">
                                    @if($course->meta_description)
                                        <p class="mb-2">
                                            <strong>Description:</strong><br>
                                            <small class="text-muted">{{ $course->meta_description }}</small>
                                        </p>
                                    @endif
                                    @if($course->meta_keywords)
                                        <p class="mb-0">
                                            <strong>Mots-clés:</strong><br>
                                            <small class="text-muted">{{ $course->meta_keywords }}</small>
                                        </p>
                                    @endif
                                </div>
                            </div>
                            @endif

                            <!-- Dates -->
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Dates</h6>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled mb-0">
                                        <li class="mb-2">
                                            <strong>Créé le:</strong><br>
                                            <small class="text-muted">{{ $course->created_at->format('d/m/Y H:i') }}</small>
                                        </li>
                                        <li class="mb-2">
                                            <strong>Modifié le:</strong><br>
                                            <small class="text-muted">{{ $course->updated_at->format('d/m/Y H:i') }}</small>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
/* Couleurs principales */
.text-primary {
    color: #003366 !important;
}

/* Headers avec gradient */
.bg-gradient-primary {
    background: linear-gradient(135deg, #003366 0%, #004080 100%) !important;
}

.bg-gradient-info {
    background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%) !important;
}

.bg-gradient-success {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%) !important;
}

.bg-gradient-warning {
    background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%) !important;
}

.bg-gradient-secondary {
    background: linear-gradient(135deg, #6c757d 0%, #495057 100%) !important;
}

/* Cards */
.card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.15) !important;
}

.card-header {
    font-weight: 600;
    border-bottom: none;
}

.card-header h5, .card-header h6 {
    font-weight: 600;
}

/* Thumbnail du cours */
.course-thumbnail {
    transition: transform 0.3s ease;
}

.course-thumbnail:hover {
    transform: scale(1.02);
}

/* Description */
.course-description {
    line-height: 1.8;
    color: #495057;
}

/* Badges */
.badge {
    font-size: 0.85rem;
    font-weight: 500;
    padding: 0.5em 0.75em;
    transition: transform 0.2s ease;
}

.badge:hover {
    transform: scale(1.05);
}

/* Accordion */
.accordion-button {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    font-weight: 500;
    transition: all 0.2s ease;
}

.accordion-button:not(.collapsed) {
    background: linear-gradient(135deg, #e3f2fd 0%, #f0f9ff 100%);
    color: #003366;
}

.accordion-button:hover {
    background-color: #e9ecef;
}

.accordion-item {
    border: 1px solid #dee2e6;
    border-radius: 0.375rem !important;
    margin-bottom: 0.5rem;
    overflow: hidden;
}

/* List group */
.list-group-item {
    border: 1px solid #dee2e6;
    border-radius: 0.375rem !important;
    margin-bottom: 0.5rem;
    transition: all 0.2s ease;
}

.list-group-item:hover {
    background-color: #f8f9fa;
    transform: translateX(5px);
}

/* Buttons */
.btn {
    transition: all 0.2s ease;
    font-weight: 500;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

/* Responsive */
@media (max-width: 768px) {
    .card-header h4 {
        font-size: 1.1rem;
    }
    
    .card-header small {
        font-size: 0.8rem;
    }
    
    .btn-sm {
        font-size: 0.8rem;
        padding: 0.25rem 0.5rem;
    }
}
</style>
@endpush
@endsection
