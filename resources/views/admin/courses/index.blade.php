@extends('layouts.admin')

@section('title', 'Gestion des cours')
@section('admin-title', 'Gestion des cours')
@section('admin-subtitle', 'Pilotez l’ensemble des formations disponibles sur la plateforme')
@section('admin-actions')
    <a href="{{ route('admin.courses.create') }}" class="btn btn-primary">
        <i class="fas fa-plus-circle me-2"></i>Nouveau cours
    </a>
@endsection

@section('admin-content')
    <section class="admin-panel">
        <div class="admin-panel__body">
            <div class="admin-stats-grid mb-4">
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">Total</p>
                    <p class="admin-stat-card__value">{{ $stats['total'] }}</p>
                    <p class="admin-stat-card__muted">Cours enregistrés</p>
                </div>
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">Publiés</p>
                    <p class="admin-stat-card__value">{{ $stats['published'] }}</p>
                    <p class="admin-stat-card__muted">Visibles côté étudiant</p>
                </div>
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">Brouillons</p>
                    <p class="admin-stat-card__value">{{ $stats['draft'] }}</p>
                    <p class="admin-stat-card__muted">À finaliser</p>
                </div>
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">Gratuits</p>
                    <p class="admin-stat-card__value">{{ $stats['free'] }}</p>
                    <p class="admin-stat-card__muted">Accès libre</p>
                </div>
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">Payants</p>
                    <p class="admin-stat-card__value">{{ $stats['paid'] }}</p>
                    <p class="admin-stat-card__muted">Cours premium</p>
                </div>
                <div class="admin-stat-card">
                    <p class="admin-stat-card__label">Résultats</p>
                    <p class="admin-stat-card__value">{{ $courses->total() }}</p>
                    <p class="admin-stat-card__muted">Correspondant à vos filtres</p>
                </div>
            </div>

            <x-admin.search-panel
                :action="route('admin.courses')"
                formId="coursesFilterForm"
                filtersId="coursesFilters"
                :hasFilters="true"
                searchName="search"
                :searchValue="request('search')"
                placeholder="Rechercher un cours..."
            >
                <x-slot:filters>
                    <div class="admin-form-grid admin-form-grid--two mb-3">
                        <div>
                            <label class="form-label fw-semibold">Catégorie</label>
                            <select class="form-select" name="category">
                                <option value="">Toutes les catégories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label fw-semibold">Statut</label>
                            <select class="form-select" name="status">
                                <option value="">Tous les statuts</option>
                                <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Publié</option>
                                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Brouillon</option>
                                <option value="free" {{ request('status') == 'free' ? 'selected' : '' }}>Gratuit</option>
                                <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Payant</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label fw-semibold">Formateur</label>
                            <select class="form-select" name="instructor">
                                <option value="">Tous les formateurs</option>
                                @foreach($instructors as $instructor)
                                    <option value="{{ $instructor->id }}" {{ request('instructor') == $instructor->id ? 'selected' : '' }}>
                                        {{ $instructor->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <span class="text-muted small">Ajustez les filtres puis appliquez-les.</span>
                        <a href="{{ route('admin.courses') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-undo me-2"></i>Réinitialiser
                        </a>
                    </div>
                </x-slot:filters>
            </x-admin.search-panel>

            @if(request()->hasAny(['search', 'category', 'status', 'instructor']))
                <div class="alert alert-info d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <i class="fas fa-filter me-2"></i>
                        <strong>Filtres actifs</strong>
                        @if(request('search'))
                            | Recherche : <span class="fw-semibold">{{ request('search') }}</span>
                        @endif
                        @if(request('category'))
                            | Catégorie : <span class="fw-semibold">{{ $categories->firstWhere('id', request('category'))->name ?? 'Inconnue' }}</span>
                        @endif
                        @if(request('status'))
                            | Statut : <span class="fw-semibold">{{ ucfirst(request('status')) }}</span>
                        @endif
                        @if(request('instructor'))
                            | Formateur : <span class="fw-semibold">{{ $instructors->firstWhere('id', request('instructor'))->name ?? 'Inconnu' }}</span>
                        @endif
                    </div>
                    <a href="{{ route('admin.courses') }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-times me-1"></i>Effacer
                    </a>
                </div>
            @endif
        </div>
    </section>

    <section class="admin-panel">
        <div class="admin-panel__body">
            <div class="admin-table">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 48px;">
                                    <input type="checkbox" id="selectAll" class="form-check-input">
                                </th>
                                <th>
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'title', 'direction' => request('sort') == 'title' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark">
                                        Cours
                                        @if(request('sort') == 'title')
                                            <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                        @else
                                            <i class="fas fa-sort ms-1 text-muted"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>Formateur</th>
                                <th>Catégorie</th>
                                <th>
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'price', 'direction' => request('sort') == 'price' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark">
                                        Prix
                                        @if(request('sort') == 'price')
                                            <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                        @else
                                            <i class="fas fa-sort ms-1 text-muted"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>Statut</th>
                                <th class="text-center">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'direction' => request('sort') == 'created_at' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark">
                                        Créé le
                                        @if(request('sort') == 'created_at')
                                            <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                        @else
                                            <i class="fas fa-sort ms-1 text-muted"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="text-center" style="width: 120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($courses as $course)
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" class="form-check-input">
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <img src="{{ $course->thumbnail ?? 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=120&q=80' }}" alt="{{ $course->title }}" class="rounded" style="width: 64px; height: 48px; object-fit: cover;">
                                            <div>
                                                <a href="{{ route('admin.courses.show', $course) }}" class="fw-semibold text-decoration-none text-dark">
                                                    {{ $course->title }}
                                                </a>
                                                <div class="text-muted small">{{ Str::limit($course->subtitle, 60) }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="admin-chip">
                                            <i class="fas fa-user"></i>{{ $course->instructor->name ?? 'Non assigné' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="admin-chip admin-chip--info">
                                            {{ $course->category->name ?? 'Aucune' }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($course->is_free)
                                            <span class="admin-chip admin-chip--success">Gratuit</span>
                                        @else
                                            {{ \App\Helpers\CurrencyHelper::formatWithSymbol($course->price ?? 0, $course->currency ?? 'USD') }}
                                        @endif
                                    </td>
                                    <td>
                                        @if($course->is_published)
                                            <span class="admin-chip admin-chip--success">Publié</span>
                                        @else
                                            <span class="admin-chip admin-chip--warning">Brouillon</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="admin-chip admin-chip--neutral">{{ $course->created_at->format('d/m/Y') }}</span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('admin.courses.edit', $course) }}" class="btn btn-light" title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="{{ route('admin.courses.show', $course) }}" class="btn btn-light" title="Voir">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <form action="{{ route('admin.courses.destroy', $course) }}" method="POST" onsubmit="return confirm('Supprimer ce cours ?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-light text-danger" title="Supprimer">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="admin-table__empty">
                                        <i class="fas fa-inbox mb-2 d-block"></i>
                                        Aucun cours trouvé avec ces critères.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="admin-pagination">
                {{ $courses->withQueryString()->links() }}
            </div>
        </div>
    </section>
@endsection