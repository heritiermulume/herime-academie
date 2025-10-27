@extends('layouts.app')

@section('title', 'Blog - Herime Academie')
@section('description', 'Découvrez nos articles sur l\'apprentissage en ligne, les nouvelles technologies et les tendances de la formation.')

@section('content')
<div class="container py-5">
    <!-- Header -->
    <div class="row mb-5">
        <div class="col-lg-8 mx-auto text-center">
            <h1 class="display-4 fw-bold mb-3">Blog Herime Academie</h1>
            <p class="lead text-muted">
                Découvrez nos articles sur l'apprentissage en ligne, les nouvelles technologies et les tendances de la formation
            </p>
        </div>
    </div>

    <!-- Featured Posts -->
    @if($featuredPosts->count() > 0)
    <div class="row mb-5">
        <div class="col-12">
            <h2 class="h4 fw-bold mb-4">Articles en vedette</h2>
            <div class="row g-4">
                @foreach($featuredPosts as $post)
                <div class="col-lg-4 col-md-6">
                    <div class="card border-0 shadow-sm h-100 hover-lift">
                        <div class="position-relative">
                            <img src="{{ $post->featured_image ?: 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=400&h=250&fit=crop' }}" 
                                 class="card-img-top" alt="{{ $post->title }}" style="height: 200px; object-fit: cover;">
                            <span class="badge bg-warning position-absolute top-0 start-0 m-3">En vedette</span>
                        </div>
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge bg-primary">{{ $post->category->name }}</span>
                                <small class="text-muted">
                                    <i class="fas fa-eye me-1"></i>{{ number_format($post->views) }}
                                </small>
                            </div>
                            <h5 class="card-title fw-bold mb-2">
                                <a href="{{ route('blog.show', $post->slug) }}" class="text-decoration-none text-dark">
                                    {{ Str::limit($post->title, 50) }}
                                </a>
                            </h5>
                            <p class="card-text text-muted small mb-3">
                                {{ Str::limit($post->excerpt, 100) }}
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="author-info">
                                    <small class="text-muted">
                                        <i class="fas fa-user me-1"></i>
                                        {{ $post->author->name }}
                                    </small>
                                </div>
                                <small class="text-muted">
                                    {{ $post->published_at->format('d/m/Y') }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Filters -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <form method="GET" action="{{ route('blog.index') }}" class="row g-3">
                                <div class="col-md-4">
                                    <label for="search" class="form-label">Rechercher</label>
                                    <input type="text" class="form-control" id="search" name="search" 
                                           value="{{ request('search') }}" placeholder="Titre, contenu...">
                                </div>
                                <div class="col-md-3">
                                    <label for="category" class="form-label">Catégorie</label>
                                    <select class="form-select" id="category" name="category">
                                        <option value="">Toutes les catégories</option>
                                        @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="sort" class="form-label">Trier par</label>
                                    <select class="form-select" id="sort" name="sort">
                                        <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Plus récents</option>
                                        <option value="popular" {{ request('sort') == 'popular' ? 'selected' : '' }}>Plus populaires</option>
                                        <option value="views" {{ request('sort') == 'views' ? 'selected' : '' }}>Plus vus</option>
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

            <!-- Posts List -->
            @if($posts->count() > 0)
                <div class="row g-4">
                    @foreach($posts as $post)
                    <div class="col-12">
                        <div class="card border-0 shadow-sm hover-lift">
                            <div class="row g-0">
                                <div class="col-md-4">
                                    <img src="{{ $post->featured_image ?: 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=400&h=250&fit=crop' }}" 
                                         class="img-fluid rounded-start h-100" alt="{{ $post->title }}" style="object-fit: cover;">
                                </div>
                                <div class="col-md-8">
                                    <div class="card-body p-4">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <span class="badge bg-primary">{{ $post->category->name }}</span>
                                            <small class="text-muted">
                                                <i class="fas fa-eye me-1"></i>{{ number_format($post->views) }}
                                            </small>
                                        </div>
                                        <h5 class="card-title fw-bold mb-2">
                                            <a href="{{ route('blog.show', $post->slug) }}" class="text-decoration-none text-dark">
                                                {{ $post->title }}
                                            </a>
                                        </h5>
                                        <p class="card-text text-muted mb-3">
                                            {{ Str::limit($post->excerpt, 150) }}
                                        </p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="author-info">
                                                <div class="d-flex align-items-center">
                                                    <img src="{{ $post->author->avatar ? $1->avatar : 'https://ui-avatars.com/api/?name=' . urlencode($post->author->name) . '&background=003366&color=fff' }}" 
                                                         alt="{{ $post->author->name }}" class="rounded-circle me-2" width="30" height="30">
                                                    <div>
                                                        <small class="text-muted">{{ $post->author->name }}</small>
                                                        <br>
                                                        <small class="text-muted">{{ $post->published_at->format('d/m/Y') }}</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <a href="{{ route('blog.show', $post->slug) }}" class="btn btn-outline-primary btn-sm">
                                                Lire la suite <i class="fas fa-arrow-right ms-1"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="row mt-5">
                    <div class="col-12">
                        {{ $posts->appends(request()->query())->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-search fa-4x text-muted"></i>
                    </div>
                    <h3 class="text-muted">Aucun article trouvé</h3>
                    <p class="text-muted">Essayez de modifier vos critères de recherche</p>
                    <a href="{{ route('blog.index') }}" class="btn btn-primary">
                        Voir tous les articles
                    </a>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Categories -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold">Catégories</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @foreach($categories as $category)
                        <a href="{{ route('blog.category', $category->slug) }}" 
                           class="list-group-item list-group-item-action border-0 d-flex justify-content-between align-items-center">
                            <span>{{ $category->name }}</span>
                            <span class="badge bg-primary rounded-pill">{{ $category->posts->count() }}</span>
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Popular Tags -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold">Tags populaires</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        @php
                            $allTags = collect();
                            foreach($posts as $post) {
                                if($post->tags) {
                                    $allTags = $allTags->merge($post->tags);
                                }
                            }
                            $popularTags = $allTags->countBy()->sortDesc()->take(10);
                        @endphp
                        @foreach($popularTags as $tag => $count)
                        <a href="{{ route('blog.search', ['q' => $tag]) }}" 
                           class="badge bg-light text-dark text-decoration-none">
                            {{ $tag }} ({{ $count }})
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Newsletter -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0 fw-bold">Newsletter</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Restez informé de nos derniers articles et actualités.</p>
                    <form id="newsletterForm">
                        @csrf
                        <div class="mb-3">
                            <input type="email" class="form-control" id="newsletterEmail" 
                                   placeholder="Votre adresse email" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-envelope me-2"></i>S'abonner
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Newsletter subscription
document.getElementById('newsletterForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const email = document.getElementById('newsletterEmail').value;
    
    fetch('{{ route("newsletter.subscribe") }}', {
        method: 'POST',
        body: new FormData(this),
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Inscription réussie ! Merci de votre intérêt.');
            document.getElementById('newsletterEmail').value = '';
        } else {
            alert('Erreur: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Une erreur est survenue lors de l\'inscription.');
    });
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

.list-group-item-action:hover {
    background-color: #f8f9fa;
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

.badge {
    font-size: 0.8em;
}
</style>
@endpush