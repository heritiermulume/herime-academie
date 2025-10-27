@extends('layouts.app')

@section('title', 'Détails du cours - Admin')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-graduation-cap me-2"></i>Détails du cours
                        </h4>
                        <div>
                            <a href="{{ route('admin.courses.lessons', $course) }}" class="btn btn-info me-2">
                                <i class="fas fa-list me-1"></i>Gérer les leçons
                            </a>
                            <a href="{{ route('admin.courses.edit', $course) }}" class="btn btn-warning me-2">
                                <i class="fas fa-edit me-1"></i>Modifier
                            </a>
                            <a href="{{ route('admin.courses') }}" class="btn btn-light">
                                <i class="fas fa-arrow-left me-1"></i>Retour
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Informations principales -->
                        <div class="col-md-8">
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h2 class="text-primary mb-3">{{ $course->title }}</h2>
                                    
                                    @if($course->thumbnail)
                                        <img src="{{ Storage::url($course->thumbnail) }}" 
                                             alt="{{ $course->title }}" 
                                             class="img-fluid rounded mb-3" 
                                             style="max-height: 300px; width: 100%; object-fit: cover;">
                                    @else
                                        <div class="bg-light rounded mb-3 d-flex align-items-center justify-content-center" 
                                             style="height: 200px;">
                                            <div class="text-center text-muted">
                                                <i class="fas fa-image fa-3x mb-2"></i>
                                                <p>Aucune image de couverture</p>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    <div class="mb-3">
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
                                        <div class="mb-3">
                                            @foreach($tagsList as $tag)
                                                <span class="badge bg-secondary me-1">{{ $tag }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Sections et leçons -->
                            <div class="row">
                                <div class="col-12">
                                    <h4 class="text-primary mb-3">
                                        <i class="fas fa-list me-2"></i>Contenu du cours
                                    </h4>
                                    
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
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h6 class="mb-0">Statut et actions</h6>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        @if($course->is_published)
                                            <span class="badge bg-success mb-2">Publié</span>
                                        @else
                                            <span class="badge bg-warning mb-2">Brouillon</span>
                                        @endif
                                        
                                        @if($course->is_featured)
                                            <span class="badge bg-info mb-2">En vedette</span>
                                        @endif
                                        
                                        @if($course->is_free)
                                            <span class="badge bg-success mb-2">Gratuit</span>
                                        @else
                                            <span class="badge bg-primary mb-2">Payant</span>
                                        @endif
                                        
                                        <a href="{{ route('courses.show', $course) }}" class="btn btn-outline-primary" target="_blank">
                                            <i class="fas fa-external-link-alt me-1"></i>Voir le cours
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Informations du cours -->
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h6 class="mb-0">Informations</h6>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled mb-0">
                                        <li class="mb-2">
                                            <strong>Instructeur:</strong><br>
                                            <div class="d-flex align-items-center mt-1">
                                                <img src="{{ $course->instructor->avatar ? $instructor->avatar : asset('images/default-avatar.svg') }}" 
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
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h6 class="mb-0">Prix</h6>
                                </div>
                                <div class="card-body">
                                    @if($course->is_free)
                                        <h4 class="text-success mb-0">Gratuit</h4>
                                    @else
                                        <h4 class="text-primary mb-0">{{ number_format($course->current_price) }} FCFA</h4>
                                        @if($course->sale_price && $course->sale_price < $course->price)
                                            <small class="text-muted">
                                                <s>{{ number_format($course->price) }} FCFA</s>
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
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h6 class="mb-0">Prérequis</h6>
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
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h6 class="mb-0">Ce que vous apprendrez</h6>
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
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h6 class="mb-0">SEO</h6>
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
.text-primary {
    color: #003366 !important;
}

.card-header h6 {
    color: #003366;
    font-weight: 600;
}

.accordion-button {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
}

.accordion-button:not(.collapsed) {
    background-color: #e3f2fd;
    color: #003366;
}

.list-group-item {
    border: 1px solid #dee2e6;
    border-radius: 0.375rem !important;
    margin-bottom: 0.5rem;
}

.badge {
    font-size: 0.75em;
}
</style>
@endpush
@endsection
