@extends('layouts.dashboard')

@php($instructor = auth()->user())
@php
    use Illuminate\Support\Str;
@endphp
@include('instructors.partials.dashboard-context', ['instructor' => $instructor])

@section('title', 'Mes cours - Formateur')
@section('dashboard-title', 'Gestion des cours')
@section('dashboard-subtitle', 'Créez, modifiez et analysez vos cours en un coup d’œil')
@section('dashboard-actions')
    <a href="{{ route('instructor.courses.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Nouveau cours
    </a>
@endsection

@section('dashboard-content')
    <section class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white border-0 py-3">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-sm-6 col-md-4 col-xl-3">
                    <label for="status" class="form-label fw-semibold">Filtrer par statut</label>
                    <select name="status" id="status" class="form-select" onchange="this.form.submit()">
                        <option value="" {{ $status === null ? 'selected' : '' }}>Tous les cours</option>
                        <option value="published" {{ $status === 'published' ? 'selected' : '' }}>Publiés</option>
                        <option value="draft" {{ $status === 'draft' ? 'selected' : '' }}>Brouillons</option>
                    </select>
                </div>
            </form>
        </div>
    </section>

    <section class="card shadow-sm border-0">
        <div class="card-header card-header-primary d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="card-title mb-0 text-white fw-semibold">Liste des cours</h5>
                <small class="text-white-50">{{ $courses->total() }} cours au total</small>
            </div>
        </div>
        <div class="card-body p-0">
            @if($courses->count() > 0)
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Cours</th>
                                <th>Catégorie</th>
                                <th>Statut</th>
                                <th>Étudiants</th>
                                <th>Note</th>
                                <th>Créé le</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($courses as $course)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <img src="{{ $course->thumbnail_url ?: 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=60&h=40&fit=crop' }}" alt="{{ $course->title }}" class="rounded" style="width: 64px; height: 48px; object-fit: cover;">
                                            <div>
                                                <a href="{{ route('instructor.courses.edit', $course) }}" class="fw-semibold text-decoration-none text-dark">
                                                    {{ Str::limit($course->title, 60) }}
                                                </a>
                                                <div class="text-muted small">Slug : {{ $course->slug }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="badge bg-primary">{{ $course->category->name }}</span></td>
                                    <td>
                                        @if($course->is_published)
                                            <span class="badge bg-success">Publié</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Brouillon</span>
                                        @endif
                                    </td>
                                    <td><strong>{{ number_format($course->enrollments_count) }}</strong></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-1">
                                            <i class="fas fa-star text-warning"></i>
                                            <span>{{ number_format($course->stats['average_rating'] ?? 0, 1) }}</span>
                                            <small class="text-muted">({{ $course->stats['total_reviews'] ?? 0 }})</small>
                                        </div>
                                    </td>
                                    <td>{{ $course->created_at->format('d/m/Y') }}</td>
                                    <td class="text-end">
                                        <div class="btn-group">
                                            <a href="{{ route('courses.show', $course->slug) }}" class="btn btn-outline-secondary btn-sm" title="Voir"><i class="fas fa-eye"></i></a>
                                            <a href="{{ route('instructor.courses.edit', $course) }}" class="btn btn-outline-primary btn-sm" title="Modifier"><i class="fas fa-edit"></i></a>
                                            <a href="{{ route('instructor.courses.lessons', $course) }}" class="btn btn-outline-info btn-sm" title="Leçons"><i class="fas fa-list"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white border-0 py-3">
                    <div class="d-flex justify-content-center">
                        {{ $courses->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-book fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Aucun cours trouvé</h5>
                    <p class="text-muted">Publiez un cours pour commencer à enseigner.</p>
                    <a href="{{ route('instructor.courses.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Créer un cours
                    </a>
                </div>
            @endif
        </div>
    </section>
@endsection
