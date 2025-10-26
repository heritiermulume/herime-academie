@extends('layouts.app')

@section('title', $post->title . ' - Blog Herime Academie')
@section('description', $post->excerpt ?: Str::limit(strip_tags($post->content), 160))

@section('content')
<div class="container py-5">
    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Article Header -->
            <div class="article-header mb-4">
                <nav aria-label="breadcrumb" class="mb-3">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('blog.index') }}">Blog</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('blog.category', $post->category->slug) }}">{{ $post->category->name }}</a></li>
                        <li class="breadcrumb-item active">{{ Str::limit($post->title, 30) }}</li>
                    </ol>
                </nav>

                <div class="d-flex flex-wrap gap-2 mb-3">
                    @if($post->is_featured)
                    <span class="badge bg-warning">En vedette</span>
                    @endif
                    <span class="badge bg-primary">{{ $post->category->name }}</span>
                </div>

                <h1 class="display-5 fw-bold mb-3">{{ $post->title }}</h1>
                
                <div class="d-flex flex-wrap align-items-center gap-4 mb-4">
                    <div class="author-info">
                        <div class="d-flex align-items-center">
                            <img src="{{ $post->author->avatar ? Storage::url($post->author->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($post->author->name) . '&background=003366&color=fff' }}" 
                                 alt="{{ $post->author->name }}" class="rounded-circle me-3" width="50" height="50">
                            <div>
                                <h6 class="mb-0 fw-bold">{{ $post->author->name }}</h6>
                                <small class="text-muted">Auteur</small>
                            </div>
                        </div>
                    </div>
                    <div class="publish-date">
                        <i class="fas fa-calendar text-muted me-2"></i>
                        <span class="text-muted">{{ $post->published_at->format('d F Y') }}</span>
                    </div>
                    <div class="reading-time">
                        <i class="fas fa-clock text-muted me-2"></i>
                        <span class="text-muted">{{ ceil(str_word_count(strip_tags($post->content)) / 200) }} min de lecture</span>
                    </div>
                    <div class="views-count">
                        <i class="fas fa-eye text-muted me-2"></i>
                        <span class="text-muted">{{ number_format($post->views) }} vues</span>
                    </div>
                </div>

                <!-- Featured Image -->
                @if($post->featured_image)
                <div class="article-image mb-4">
                    <img src="{{ $post->featured_image }}" alt="{{ $post->title }}" 
                         class="img-fluid rounded shadow">
                </div>
                @endif
            </div>

            <!-- Article Content -->
            <div class="article-content mb-5">
                <div class="content">
                    {!! $post->content !!}
                </div>
            </div>

            <!-- Tags -->
            @if($post->tags && count($post->tags) > 0)
            <div class="article-tags mb-5">
                <h6 class="fw-bold mb-3">Tags :</h6>
                <div class="d-flex flex-wrap gap-2">
                    @foreach($post->tags as $tag)
                    <a href="{{ route('blog.search', ['q' => $tag]) }}" 
                       class="badge bg-light text-dark text-decoration-none">
                        #{{ $tag }}
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Share Article -->
            <div class="article-share mb-5">
                <h6 class="fw-bold mb-3">Partager cet article :</h6>
                <div class="d-flex gap-2">
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->url()) }}" 
                       target="_blank" class="btn btn-outline-primary btn-sm">
                        <i class="fab fa-facebook-f"></i> Facebook
                    </a>
                    <a href="https://twitter.com/intent/tweet?url={{ urlencode(request()->url()) }}&text={{ urlencode($post->title) }}" 
                       target="_blank" class="btn btn-outline-info btn-sm">
                        <i class="fab fa-twitter"></i> Twitter
                    </a>
                    <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(request()->url()) }}" 
                       target="_blank" class="btn btn-outline-secondary btn-sm">
                        <i class="fab fa-linkedin-in"></i> LinkedIn
                    </a>
                    <button type="button" class="btn btn-outline-success btn-sm" onclick="copyToClipboard('{{ request()->url() }}')">
                        <i class="fas fa-link"></i> Copier le lien
                    </button>
                </div>
            </div>

            <!-- Author Bio -->
            <div class="author-bio mb-5">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-md-3 text-center mb-3 mb-md-0">
                                <img src="{{ $post->author->avatar ? Storage::url($post->author->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($post->author->name) . '&background=003366&color=fff' }}" 
                                     alt="{{ $post->author->name }}" class="rounded-circle" width="100" height="100">
                            </div>
                            <div class="col-md-9">
                                <h5 class="fw-bold mb-2">{{ $post->author->name }}</h5>
                                @if($post->author->bio)
                                <p class="text-muted mb-3">{{ $post->author->bio }}</p>
                                @endif
                                <div class="d-flex flex-wrap gap-3">
                                    @if($post->author->website)
                                    <a href="{{ $post->author->website }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-globe me-1"></i>Site web
                                    </a>
                                    @endif
                                    @if($post->author->linkedin)
                                    <a href="{{ $post->author->linkedin }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                                        <i class="fab fa-linkedin-in me-1"></i>LinkedIn
                                    </a>
                                    @endif
                                    @if($post->author->twitter)
                                    <a href="{{ $post->author->twitter }}" target="_blank" class="btn btn-outline-info btn-sm">
                                        <i class="fab fa-twitter me-1"></i>Twitter
                                    </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Related Posts -->
            @if($relatedPosts->count() > 0)
            <div class="related-posts mb-5">
                <h4 class="fw-bold mb-4">Articles similaires</h4>
                <div class="row g-4">
                    @foreach($relatedPosts as $relatedPost)
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm h-100 hover-lift">
                            <div class="position-relative">
                                <img src="{{ $relatedPost->featured_image ?: 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=400&h=250&fit=crop' }}" 
                                     class="card-img-top" alt="{{ $relatedPost->title }}" style="height: 150px; object-fit: cover;">
                            </div>
                            <div class="card-body p-3">
                                <span class="badge bg-primary mb-2">{{ $relatedPost->category->name }}</span>
                                <h6 class="card-title fw-bold mb-2">
                                    <a href="{{ route('blog.show', $relatedPost->slug) }}" class="text-decoration-none text-dark">
                                        {{ Str::limit($relatedPost->title, 50) }}
                                    </a>
                                </h6>
                                <p class="card-text text-muted small mb-3">
                                    {{ Str::limit($relatedPost->excerpt, 80) }}
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">{{ $relatedPost->published_at->format('d/m/Y') }}</small>
                                    <small class="text-muted">
                                        <i class="fas fa-eye me-1"></i>{{ number_format($relatedPost->views) }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Recent Posts -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold">Articles récents</h5>
                </div>
                <div class="card-body p-0">
                    @if($recentPosts->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($recentPosts as $recentPost)
                            <div class="list-group-item border-0 py-3">
                                <div class="d-flex align-items-start">
                                    <img src="{{ $recentPost->featured_image ?: 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=60&h=40&fit=crop' }}" 
                                         alt="{{ $recentPost->title }}" class="rounded me-3" style="width: 60px; height: 40px; object-fit: cover;">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">
                                            <a href="{{ route('blog.show', $recentPost->slug) }}" class="text-decoration-none text-dark">
                                                {{ Str::limit($recentPost->title, 40) }}
                                            </a>
                                        </h6>
                                        <small class="text-muted">{{ $recentPost->published_at->format('d/m/Y') }}</small>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-newspaper fa-2x text-muted mb-2"></i>
                            <p class="text-muted small">Aucun article récent</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Categories -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold">Catégories</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('blog.index') }}" 
                           class="list-group-item list-group-item-action border-0 d-flex justify-content-between align-items-center">
                            <span>Toutes les catégories</span>
                            <span class="badge bg-primary rounded-pill">{{ \App\Models\BlogPost::published()->count() }}</span>
                        </a>
                        @foreach(\App\Models\BlogCategory::active()->withCount('posts')->get() as $category)
                        <a href="{{ route('blog.category', $category->slug) }}" 
                           class="list-group-item list-group-item-action border-0 d-flex justify-content-between align-items-center">
                            <span>{{ $category->name }}</span>
                            <span class="badge bg-primary rounded-pill">{{ $category->posts_count }}</span>
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
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert('Lien copié dans le presse-papiers !');
    });
}

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

.article-content {
    font-size: 1.1rem;
    line-height: 1.8;
}

.article-content h1,
.article-content h2,
.article-content h3,
.article-content h4,
.article-content h5,
.article-content h6 {
    margin-top: 2rem;
    margin-bottom: 1rem;
    font-weight: 600;
}

.article-content p {
    margin-bottom: 1.5rem;
}

.article-content img {
    max-width: 100%;
    height: auto;
    border-radius: 0.5rem;
    margin: 1.5rem 0;
}

.article-content blockquote {
    border-left: 4px solid #003366;
    padding-left: 1.5rem;
    margin: 1.5rem 0;
    font-style: italic;
    color: #6c757d;
}

.article-content ul,
.article-content ol {
    margin-bottom: 1.5rem;
    padding-left: 2rem;
}

.article-content li {
    margin-bottom: 0.5rem;
}

.badge {
    font-size: 0.8em;
}

.author-bio .card {
    border-left: 4px solid #ffcc33 !important;
}
</style>
@endpush