@props(['categories' => [], 'providers' => [], 'levels' => [], 'languages' => [], 'stats' => []])

<div class="advanced-filters bg-light p-4 rounded-3 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0 fw-bold">Filtres avancés</h5>
        <button class="btn btn-outline-secondary btn-sm" onclick="resetFilters()">
            <i class="fas fa-undo me-1"></i>Réinitialiser
        </button>
    </div>

    <form id="filterForm" class="row g-3">
        <!-- Recherche -->
        <div class="col-md-6">
            <label for="search" class="form-label">Rechercher</label>
            <div class="input-group">
                <input type="text" class="form-control" id="search" name="q" placeholder="Titre, description, prestataire...">
                <button class="btn btn-outline-primary" type="button" onclick="searchCourses()">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>

        <!-- Catégorie -->
        <div class="col-md-3">
            <label for="category" class="form-label">Catégorie</label>
            <select class="form-select" id="category" name="category_id">
                <option value="">Toutes les catégories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }} ({{ $category->contents_count }})</option>
                @endforeach
            </select>
        </div>

        <!-- Niveau -->
        <div class="col-md-3">
            <label for="level" class="form-label">Niveau</label>
            <select class="form-select" id="level" name="level">
                <option value="">Tous les niveaux</option>
                @foreach($levels as $level)
                    <option value="{{ $level }}">{{ ucfirst($level) }}</option>
                @endforeach
            </select>
        </div>

        <!-- Prestataire -->
        <div class="col-md-4">
            <label for="provider" class="form-label">Prestataire</label>
            <select class="form-select" id="provider" name="provider_id">
                <option value="">Tous les prestataires</option>
                @foreach($providers as $provider)
                    <option value="{{ $provider->id }}">{{ $provider->name }} ({{ $provider->contents_count }})</option>
                @endforeach
            </select>
        </div>

        <!-- Langue -->
        <div class="col-md-4">
            <label for="language" class="form-label">Langue</label>
            <select class="form-select" id="language" name="language">
                <option value="">Toutes les langues</option>
                @foreach($languages as $language)
                    <option value="{{ $language }}">{{ $language === 'fr' ? 'Français' : 'English' }}</option>
                @endforeach
            </select>
        </div>

        <!-- Type de cours -->
        <div class="col-md-4">
            <label class="form-label">Type de cours</label>
            <div class="d-flex gap-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="is_free" name="is_free" value="1">
                    <label class="form-check-label" for="is_free">Gratuit</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1">
                    <label class="form-check-label" for="is_featured">En vedette</label>
                </div>
            </div>
        </div>

        <!-- Note minimale -->
        <div class="col-md-6">
            <label for="min_rating" class="form-label">Note minimale</label>
            <div class="d-flex align-items-center gap-2">
                <input type="range" class="form-range" id="min_rating" name="min_rating" 
                       min="{{ $stats['min_rating'] ?? 0 }}" max="{{ $stats['max_rating'] ?? 5 }}" 
                       step="0.1" value="{{ $stats['min_rating'] ?? 0 }}">
                <span class="badge bg-primary" id="min_rating_value">{{ $stats['min_rating'] ?? 0 }}</span>
            </div>
        </div>

        <!-- Nombre de clients minimum -->
        <div class="col-md-6">
            <label for="min_customers" class="form-label">Clients minimum</label>
            <div class="d-flex align-items-center gap-2">
                <input type="range" class="form-range" id="min_customers" name="min_customers" 
                       min="{{ $stats['min_customers'] ?? 0 }}" max="{{ $stats['max_customers'] ?? 1000 }}" 
                       step="10" value="{{ $stats['min_customers'] ?? 0 }}">
                <span class="badge bg-primary" id="min_customers_value">{{ $stats['min_customers'] ?? 0 }}</span>
            </div>
        </div>

        <!-- Durée -->
        <div class="col-md-6">
            <label for="min_duration" class="form-label">Durée minimale (minutes)</label>
            <div class="d-flex align-items-center gap-2">
                <input type="range" class="form-range" id="min_duration" name="min_duration" 
                       min="{{ $stats['min_duration'] ?? 0 }}" max="{{ $stats['max_duration'] ?? 1000 }}" 
                       step="30" value="{{ $stats['min_duration'] ?? 0 }}">
                <span class="badge bg-primary" id="min_duration_value">{{ $stats['min_duration'] ?? 0 }} min</span>
            </div>
        </div>

        <!-- Nombre de leçons minimum -->
        <div class="col-md-6">
            <label for="min_lessons" class="form-label">Leçons minimum</label>
            <input type="number" class="form-control" id="min_lessons" name="min_lessons" 
                   min="1" placeholder="Nombre minimum de leçons">
        </div>

        <!-- Tri -->
        <div class="col-md-6">
            <label for="sort_by" class="form-label">Trier par</label>
            <select class="form-select" id="sort_by" name="sort_by">
                <option value="created_at">Date de création</option>
                <option value="popularity">Popularité</option>
                <option value="rating">Note</option>
                <option value="price">Prix</option>
                <option value="duration">Durée</option>
                <option value="lessons">Nombre de leçons</option>
            </select>
        </div>

        <!-- Ordre de tri -->
        <div class="col-md-6">
            <label for="sort_order" class="form-label">Ordre</label>
            <select class="form-select" id="sort_order" name="sort_order">
                <option value="desc">Décroissant</option>
                <option value="asc">Croissant</option>
            </select>
        </div>

        <!-- Boutons d'action -->
        <div class="col-12">
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-primary" onclick="applyFilters()">
                    <i class="fas fa-filter me-1"></i>Appliquer les filtres
                </button>
                <button type="button" class="btn btn-outline-secondary" onclick="resetFilters()">
                    <i class="fas fa-undo me-1"></i>Réinitialiser
                </button>
            </div>
        </div>
    </form>
</div>

<script>
// Mise à jour des valeurs des sliders
document.getElementById('min_rating').addEventListener('input', function() {
    document.getElementById('min_rating_value').textContent = this.value;
});

document.getElementById('min_customers').addEventListener('input', function() {
    document.getElementById('min_customers_value').textContent = this.value;
});

document.getElementById('min_duration').addEventListener('input', function() {
    document.getElementById('min_duration_value').textContent = this.value + ' min';
});

function applyFilters() {
    const form = document.getElementById('filterForm');
    const formData = new FormData(form);
    
    // Convertir les checkboxes en valeurs booléennes
    formData.set('is_free', document.getElementById('is_free').checked ? '1' : '0');
    formData.set('is_featured', document.getElementById('is_featured').checked ? '1' : '0');
    
    // Envoyer la requête de filtrage
    fetch('{{ route('contents.filter") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Mettre à jour l'affichage des cours
            updateCoursesDisplay(data.courses);
        } else {
            console.error('Erreur lors du filtrage:', data.message);
        }
    })
    .catch(error => {
        console.error('Erreur de connexion:', error);
    });
}

function searchCourses() {
    const searchTerm = document.getElementById('search').value;
    
    fetch(`{{ route('contents.search") }}?q=${encodeURIComponent(searchTerm)}`, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCoursesDisplay(data.courses);
        } else {
            console.error('Erreur lors de la recherche:', data.message);
        }
    })
    .catch(error => {
        console.error('Erreur de connexion:', error);
    });
}

function resetFilters() {
    document.getElementById('filterForm').reset();
    // Réinitialiser les valeurs des sliders
    document.getElementById('min_rating_value').textContent = '{{ $stats["min_rating"] ?? 0 }}';
    document.getElementById('min_customers_value').textContent = '{{ $stats["min_customers"] ?? 0 }}';
    document.getElementById('min_duration_value').textContent = '{{ $stats["min_duration"] ?? 0 }} min';
}

function updateCoursesDisplay(courses) {
    // Cette fonction sera implémentée selon la structure de votre page
    // Elle doit mettre à jour l'affichage des cours avec les nouvelles données
    console.log('Cours filtrés:', courses);
}
</script>
