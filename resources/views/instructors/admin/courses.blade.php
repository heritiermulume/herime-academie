@extends('instructors.admin.layout')

@section('admin-title', 'Gestion des cours')
@section('admin-subtitle', 'Visualisez, filtrez et optimisez l’ensemble de vos formations en un seul endroit.')

@section('admin-actions')
    @if(Route::has('instructor.courses.create'))
        <a href="{{ route('instructor.courses.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Nouveau cours
        </a>
    @endif
@endsection

@section('admin-content')
    <form method="GET" class="admin-card courses-filter">
        <div class="courses-filter__group">
            <label class="courses-filter__label" for="status">Statut du cours</label>
            <select name="status" id="status" class="form-select" onchange="this.form.submit()">
                <option value="" {{ $status === null ? 'selected' : '' }}>Tous les cours</option>
                <option value="published" {{ $status === 'published' ? 'selected' : '' }}>Publiés</option>
                <option value="draft" {{ $status === 'draft' ? 'selected' : '' }}>Brouillons</option>
            </select>
        </div>
        <div class="courses-filter__group">
            <span class="courses-filter__label">Total</span>
            <strong class="courses-filter__value">{{ $courses->total() }} cours</strong>
        </div>
    </form>

    <section class="admin-card">
        <div class="courses-table">
            <div class="courses-table__head">
                <span>Cours</span>
                <span>Catégorie</span>
                <span>Étudiants</span>
                <span>Note</span>
                <span>Créé le</span>
                <span class="text-end">Actions</span>
            </div>
            @forelse($courses as $course)
                <div class="courses-table__row">
                    <div class="courses-table__course">
                        <div class="courses-table__thumb">
                            @if($course->thumbnail_url)
                                <img src="{{ $course->thumbnail_url }}" alt="{{ $course->title }}">
                            @else
                                <span>{{ mb_substr($course->title, 0, 1) }}</span>
                            @endif
                        </div>
                        <div>
                            <strong>{{ $course->title }}</strong>
                            <span class="courses-table__status {{ $course->is_published ? 'is-published' : 'is-draft' }}">
                                {{ $course->is_published ? 'Publié' : 'Brouillon' }}
                            </span>
                        </div>
                    </div>
                    <div data-label="Catégorie">{{ $course->category?->name ?? '—' }}</div>
                    <div data-label="Étudiants">{{ number_format($course->enrollments_count) }}</div>
                    <div data-label="Note">
                        <span class="courses-table__rating">
                            <i class="fas fa-star"></i>
                            {{ number_format($course->stats['average_rating'] ?? 0, 1) }}
                        </span>
                        <small class="text-muted">({{ number_format($course->reviews_count) }})</small>
                    </div>
                    <div data-label="Créé le">{{ $course->created_at->format('d/m/Y') }}</div>
                    <div class="text-end">
                        <div class="btn-group">
                            <a href="{{ route('courses.show', $course->slug) }}" class="btn btn-outline-secondary btn-sm" target="_blank">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('instructor.courses.edit', $course) }}" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-pen"></i>
                            </a>
                            <a href="{{ route('instructor.courses.lessons', $course) }}" class="btn btn-outline-info btn-sm">
                                <i class="fas fa-list"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="courses-table__empty">
                    <i class="fas fa-chalkboard fa-2x"></i>
                    <p>Aucun cours créé pour le moment. Commencez par publier votre première formation.</p>
                    @if(Route::has('instructor.courses.create'))
                        <a href="{{ route('instructor.courses.create') }}" class="btn btn-primary">Créer un cours</a>
                    @endif
                </div>
            @endforelse
        </div>
        <div class="mt-3">
            {{ $courses->links() }}
        </div>
    </section>
@endsection

@push('styles')
<style>
    .courses-filter {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 1.25rem;
    }
    .courses-filter__group {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }
    .courses-filter__label {
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #64748b;
        font-weight: 700;
    }
    .courses-filter__value {
        font-size: 1.1rem;
        color: #0f172a;
    }
    .courses-table {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }
    .courses-table__head,
    .courses-table__row {
        display: grid;
        grid-template-columns: minmax(0, 240px) repeat(4, minmax(0, 120px)) minmax(0, 160px);
        gap: 1rem;
        align-items: center;
        padding: 0.75rem 1rem;
        border-radius: 1rem;
    }
    .courses-table__head {
        background: rgba(226, 232, 240, 0.55);
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #475569;
        font-weight: 700;
    }
    .courses-table__row {
        background: rgba(255, 255, 255, 0.95);
        border: 1px solid rgba(226, 232, 240, 0.7);
        box-shadow: 0 18px 35px -28px rgba(15, 23, 42, 0.2);
    }
    .courses-table__course {
        display: flex;
        align-items: center;
        gap: 0.85rem;
    }
    .courses-table__thumb {
        width: 52px;
        height: 52px;
        border-radius: 16px;
        overflow: hidden;
        background: rgba(15, 23, 42, 0.1);
        display: grid;
        place-items: center;
        color: #0f172a;
        font-weight: 700;
    }
    .courses-table__thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .courses-table__status {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.25rem 0.6rem;
        border-radius: 999px;
        font-size: 0.75rem;
        text-transform: uppercase;
        font-weight: 700;
        margin-top: 0.35rem;
    }
    .courses-table__status.is-published {
        background: rgba(34, 197, 94, 0.15);
        color: #15803d;
    }
    .courses-table__status.is-draft {
        background: rgba(234, 179, 8, 0.15);
        color: #b45309;
    }
    .courses-table__rating {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        color: #f59e0b;
        font-weight: 700;
    }
    .courses-table__empty {
        grid-column: 1/-1;
        text-align: center;
        padding: 2.5rem;
        border-radius: 1.25rem;
        background: rgba(226, 232, 240, 0.5);
        display: flex;
        flex-direction: column;
        gap: 1rem;
        color: #64748b;
    }
    @media (max-width: 1024px) {
        .courses-table__head,
        .courses-table__row {
            grid-template-columns: minmax(0, 220px) repeat(3, minmax(0, 120px)) minmax(0, 140px);
        }
        .courses-table__row > :nth-last-child(2) {
            display: none;
        }
    }
    @media (max-width: 768px) {
        .courses-table__head {
            display: none;
        }
        .courses-table__row {
            grid-template-columns: repeat(1, minmax(0, 1fr));
        }
        .courses-table__row > div {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
        }
        .courses-table__row > div:first-child {
            flex-direction: column;
            align-items: flex-start;
        }
        .courses-table__row > div:not(:first-child):not(:last-child)::before {
            content: attr(data-label);
            text-transform: uppercase;
            font-size: 0.7rem;
            letter-spacing: 0.08em;
            color: #94a3b8;
        }
    }
</style>
@endpush
