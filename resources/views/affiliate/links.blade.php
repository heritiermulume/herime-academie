@extends('layouts.app')

@section('title', 'Générer des liens d\'affiliation - Herime Academie')

@section('content')
<div class="container py-5">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('affiliate.dashboard') }}">Affiliation</a></li>
                    <li class="breadcrumb-item active">Générer des liens</li>
                </ol>
            </nav>
            
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 fw-bold mb-1">Générer des liens d'affiliation</h1>
                    <p class="text-muted mb-0">Créez des liens personnalisés pour promouvoir des cours et gagner des commissions</p>
                </div>
                <div>
                    <a href="{{ route('affiliate.dashboard') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Retour au tableau de bord
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Affiliate Code -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-code me-2"></i>Votre code d'affiliation
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="input-group">
                                <input type="text" class="form-control form-control-lg" value="{{ $affiliate->code }}" readonly>
                                <button class="btn btn-outline-secondary" onclick="copyToClipboard('{{ $affiliate->code }}')">
                                    <i class="fas fa-copy me-2"></i>Copier
                                </button>
                            </div>
                            <small class="text-muted mt-2 d-block">
                                Utilisez ce code pour créer des liens d'affiliation personnalisés
                            </small>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <div class="text-center">
                                <h6 class="fw-bold text-primary">{{ $affiliate->commission_rate }}%</h6>
                                <small class="text-muted">Taux de commission</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form method="GET" action="{{ route('affiliate.links') }}" class="row g-3">
                        <div class="col-md-4">
                            <label for="search" class="form-label">Rechercher un cours</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="{{ request('search') }}" placeholder="Nom du cours, formateur...">
                        </div>
                        <div class="col-md-3">
                            <label for="category" class="form-label">Catégorie</label>
                            <select class="form-select" id="category" name="category">
                                <option value="">Toutes les catégories</option>
                                @foreach(\App\Models\Category::active()->get() as $category)
                                <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="level" class="form-label">Niveau</label>
                            <select class="form-select" id="level" name="level">
                                <option value="">Tous les niveaux</option>
                                <option value="beginner" {{ request('level') == 'beginner' ? 'selected' : '' }}>Débutant</option>
                                <option value="intermediate" {{ request('level') == 'intermediate' ? 'selected' : '' }}>Intermédiaire</option>
                                <option value="advanced" {{ request('level') == 'advanced' ? 'selected' : '' }}>Avancé</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Courses List -->
    <div class="row">
        @if($courses->count() > 0)
            @foreach($courses as $course)
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card border-0 shadow-sm h-100 hover-lift">
                    <div class="position-relative">
                        <img src="{{ $course->thumbnail_url ?: 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=400&h=250&fit=crop' }}" 
                             class="card-img-top" alt="{{ $course->title }}" style="height: 200px; object-fit: cover;">
                        @if($course->is_featured)
                        <span class="badge bg-warning position-absolute top-0 start-0 m-3">En vedette</span>
                        @endif
                        @if($course->is_free)
                        <span class="badge bg-success position-absolute top-0 end-0 m-3">Gratuit</span>
                        @endif
                    </div>
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge bg-primary">{{ $course->category->name }}</span>
                            <div class="rating">
                                <i class="fas fa-star text-warning"></i>
                                <span class="ms-1">{{ number_format($course->rating, 1) }}</span>
                                <span class="text-muted">({{ $course->reviews_count }})</span>
                            </div>
                        </div>
                        <h5 class="card-title fw-bold mb-2">
                            <a href="{{ route('contents.show', $course->slug) }}" class="text-decoration-none text-dark">
                                {{ Str::limit($course->title, 50) }}
                            </a>
                        </h5>
                        <p class="card-text text-muted small mb-3">
                            {{ Str::limit($course->short_description, 100) }}
                        </p>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="instructor-info">
                                <small class="text-muted">
                                    <i class="fas fa-user me-1"></i>
                                    {{ $course->provider->name }}
                                </small>
                            </div>
                            <div class="course-meta">
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>
                                    {{ $course->duration }} min
                                </small>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="customers-count">
                                <small class="text-muted">
                                    <i class="fas fa-users me-1"></i>
                                    {{ number_format($course->customers_count) }} étudiants
                                </small>
                            </div>
                            <div class="level">
                                <span class="badge bg-light text-dark">
                                    @switch($course->level)
                                        @case('beginner') Débutant @break
                                        @case('intermediate') Intermédiaire @break
                                        @case('advanced') Avancé @break
                                    @endswitch
                                </span>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="price">
                                @if($course->is_free)
                                    <span class="h6 text-success fw-bold">Gratuit</span>
                                @else
                                    <span class="h6 text-primary fw-bold">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($course->current_price) }}</span>
                                @endif
                            </div>
                            <div class="commission">
                                <small class="text-success fw-bold">
                                    Commission: {{ \App\Helpers\CurrencyHelper::formatWithSymbol(($course->current_price * ($affiliate->commission_rate ?? 0)) / 100) }}
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-0 p-4 pt-0">
                        <div class="d-grid gap-2">
                            <button class="btn btn-primary" onclick="generateLink({{ $course->id }})">
                                <i class="fas fa-link me-2"></i>Générer un lien
                            </button>
                            <a href="{{ route('contents.show', $course->slug) }}" class="btn btn-outline-primary">
                                <i class="fas fa-eye me-2"></i>Voir le cours
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        @else
            <div class="col-12">
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-search fa-4x text-muted"></i>
                    </div>
                    <h3 class="text-muted">Aucun cours trouvé</h3>
                    <p class="text-muted">Essayez de modifier vos critères de recherche</p>
                    <a href="{{ route('affiliate.links') }}" class="btn btn-primary">
                        Voir tous les cours
                    </a>
                </div>
            </div>
        @endif
    </div>

    <!-- Pagination -->
    @if($courses->hasPages())
    <div class="row mt-5">
        <div class="col-12">
            {{ $courses->appends(request()->query())->links() }}
        </div>
    </div>
    @endif
</div>

<!-- Generated Link Modal -->
<div class="modal fade" id="generatedLinkModal" tabindex="-1" aria-labelledby="generatedLinkModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="generatedLinkModalLabel">Lien d'affiliation généré</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-3">Informations du cours</h6>
                        <div class="course-info">
                            <h5 id="modalCourseTitle" class="fw-bold mb-2"></h5>
                            <p id="modalCoursePrice" class="text-muted mb-2"></p>
                            <p id="modalCourseInstructor" class="text-muted mb-3"></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-3">Votre commission</h6>
                        <div class="commission-info">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Taux de commission:</span>
                                <span class="fw-bold">{{ $affiliate->commission_rate }}%</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Commission par vente:</span>
                                <span class="fw-bold text-success" id="modalCommission"></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <hr>
                
                <div class="mb-3">
                    <label for="generatedUrl" class="form-label fw-bold">Lien d'affiliation</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="generatedUrl" readonly>
                        <button class="btn btn-outline-secondary" onclick="copyToClipboard(document.getElementById('generatedUrl').value)">
                            <i class="fas fa-copy me-2"></i>Copier
                        </button>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="customText" class="form-label">Texte personnalisé (optionnel)</label>
                    <textarea class="form-control" id="customText" rows="3" 
                              placeholder="Ajoutez un message personnalisé pour promouvoir ce cours..."></textarea>
                </div>
                
                <div class="social-share">
                    <h6 class="fw-bold mb-3">Partager sur les réseaux sociaux</h6>
                    <div class="d-flex gap-2">
                        <a href="#" id="facebookShare" target="_blank" class="btn btn-outline-primary btn-sm">
                            <i class="fab fa-facebook-f me-1"></i>Facebook
                        </a>
                        <a href="#" id="twitterShare" target="_blank" class="btn btn-outline-info btn-sm">
                            <i class="fab fa-twitter me-1"></i>Twitter
                        </a>
                        <a href="#" id="linkedinShare" target="_blank" class="btn btn-outline-secondary btn-sm">
                            <i class="fab fa-linkedin-in me-1"></i>LinkedIn
                        </a>
                        <a href="#" id="whatsappShare" target="_blank" class="btn btn-outline-success btn-sm">
                            <i class="fab fa-whatsapp me-1"></i>WhatsApp
                        </a>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                <button type="button" class="btn btn-primary" onclick="copyToClipboard(document.getElementById('generatedUrl').value)">
                    <i class="fas fa-copy me-2"></i>Copier le lien
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert('Lien copié dans le presse-papiers !');
    });
}

function generateLink(courseId) {
    fetch('/affiliate/generate-link', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ content_id: courseId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update modal content
            document.getElementById('modalCourseTitle').textContent = data.course_title;
            document.getElementById('modalCoursePrice').textContent = 'Prix: $' + data.course_price;
            document.getElementById('modalCourseInstructor').textContent = 'Prestataire: ' + data.course_instructor;
            document.getElementById('modalCommission').textContent = '$' + data.estimated_commission;
            document.getElementById('generatedUrl').value = data.url;
            
            // Update social share links
            const encodedUrl = encodeURIComponent(data.url);
            const encodedTitle = encodeURIComponent(data.course_title);
            const encodedText = encodeURIComponent('Découvrez ce cours incroyable: ' + data.course_title);
            
            document.getElementById('facebookShare').href = `https://www.facebook.com/sharer/sharer.php?u=${encodedUrl}`;
            document.getElementById('twitterShare').href = `https://twitter.com/intent/tweet?url=${encodedUrl}&text=${encodedText}`;
            document.getElementById('linkedinShare').href = `https://www.linkedin.com/sharing/share-offsite/?url=${encodedUrl}`;
            document.getElementById('whatsappShare').href = `https://wa.me/?text=${encodedText}%20${encodedUrl}`;
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('generatedLinkModal'));
            modal.show();
        } else {
            alert('Erreur: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Une erreur est survenue lors de la génération du lien.');
    });
}

// Auto-update social share links when custom text changes
document.getElementById('customText').addEventListener('input', function() {
    const customText = this.value;
    const url = document.getElementById('generatedUrl').value;
    
    if (url && customText) {
        const encodedUrl = encodeURIComponent(url);
        const encodedText = encodeURIComponent(customText);
        
        document.getElementById('facebookShare').href = `https://www.facebook.com/sharer/sharer.php?u=${encodedUrl}`;
        document.getElementById('twitterShare').href = `https://twitter.com/intent/tweet?url=${encodedUrl}&text=${encodedText}`;
        document.getElementById('linkedinShare').href = `https://www.linkedin.com/sharing/share-offsite/?url=${encodedUrl}`;
        document.getElementById('whatsappShare').href = `https://wa.me/?text=${encodedText}%20${encodedUrl}`;
    }
});
</script>
@endpush

@push('styles')
<style>
.hover-lift {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.hover-lift:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
}

.breadcrumb {
    background-color: transparent;
    padding: 0;
}

.breadcrumb-item + .breadcrumb-item::before {
    content: ">";
    color: #6c757d;
}

.breadcrumb-item a {
    color: #003366;
    text-decoration: none;
}

.breadcrumb-item a:hover {
    color: #ffcc33;
}

.breadcrumb-item.active {
    color: #6c757d;
}

.btn-primary {
    background-color: #003366;
    border-color: #003366;
}

.btn-primary:hover {
    background-color: #004080;
    border-color: #004080;
}

.btn-outline-primary {
    color: #003366;
    border-color: #003366;
}

.btn-outline-primary:hover {
    background-color: #003366;
    border-color: #003366;
}

.pagination {
    justify-content: center;
}

.page-link {
    color: #003366;
    border-color: #dee2e6;
}

.page-link:hover {
    color: #ffcc33;
    background-color: #f8f9fa;
    border-color: #dee2e6;
}

.page-item.active .page-link {
    background-color: #003366;
    border-color: #003366;
}

.rating i {
    font-size: 0.9em;
}

.commission {
    background-color: #f8f9fa;
    padding: 0.5rem;
    border-radius: 0.375rem;
    border-left: 4px solid #28a745;
}
</style>
@endpush