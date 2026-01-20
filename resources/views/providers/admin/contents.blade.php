@extends('providers.admin.layout')

@section('admin-title', 'Gestion des contenus')
@section('admin-subtitle', 'Visualisez, filtrez et optimisez l’ensemble de vos formations en un seul endroit.')

@section('admin-actions')
    @if(Route::has('provider.contents.create'))
        <a href="{{ route('provider.contents.create') }}" class="admin-btn primary">
            <i class="fas fa-plus me-2"></i>Nouveau contenu
        </a>
    @endif
@endsection

@section('admin-content')
    <section class="admin-panel">
        <div class="admin-panel__header">
            <h3>
                <i class="fas fa-chalkboard-teacher me-2"></i>Mes contenus
            </h3>
        </div>
        <div class="admin-panel__body">
            <form method="GET" class="courses-filter">
                <div class="courses-filter__group">
                    <label class="courses-filter__label" for="status">Statut du contenu</label>
                    <select name="status" id="status" class="form-select" onchange="this.form.submit()">
                        <option value="" {{ $status === null ? 'selected' : '' }}>Tous les contenus</option>
                        <option value="published" {{ $status === 'published' ? 'selected' : '' }}>Publiés</option>
                        <option value="draft" {{ $status === 'draft' ? 'selected' : '' }}>Brouillons</option>
                    </select>
                </div>
                <div class="courses-filter__group">
                    <span class="courses-filter__label">Total</span>
                    <strong class="courses-filter__value">{{ $courses->total() }} contenus</strong>
                </div>
            </form>

            <div class="courses-list">
            @forelse($courses as $course)
                <div class="courses-list__item">
                    <div class="courses-list__thumbnail">
                        @if($course->thumbnail_url)
                            <img src="{{ $course->thumbnail_url }}" alt="{{ $course->title }}">
                        @else
                            <div class="courses-list__thumbnail-placeholder">
                                <i class="fas fa-book"></i>
                            </div>
                        @endif
                        <span class="courses-list__status {{ $course->is_published ? 'is-published' : 'is-draft' }}">
                            {{ $course->is_published ? 'Publié' : 'Brouillon' }}
                        </span>
                    </div>
                    <div class="courses-list__content">
                        <div class="courses-list__header">
                            <h4 class="courses-list__title">{{ $course->title }}</h4>
                            <div class="courses-list__meta">
                                <span class="courses-list__category">
                                    <i class="fas fa-folder me-1"></i>{{ $course->category?->name ?? 'Sans catégorie' }}
                                </span>
                                <span class="courses-list__date">
                                    <i class="fas fa-calendar me-1"></i>{{ $course->created_at->format('d/m/Y') }}
                                </span>
                            </div>
                        </div>
                        <div class="courses-list__stats">
                            <div class="courses-list__stat">
                                <i class="fas fa-users"></i>
                                <span class="courses-list__stat-value">{{ number_format($course->enrollments_count ?? 0) }}</span>
                                <span class="courses-list__stat-label">Clients</span>
                            </div>
                            <div class="courses-list__stat">
                                <i class="fas fa-star"></i>
                                <span class="courses-list__stat-value">{{ number_format((float)($course->reviews_avg_rating ?? 0), 1) }}</span>
                                <span class="courses-list__stat-label">Note ({{ number_format($course->reviews_count ?? 0) }})</span>
                            </div>
                            @if($course->is_free)
                                <div class="courses-list__stat">
                                    <i class="fas fa-gift"></i>
                                    <span class="courses-list__stat-value">Gratuit</span>
                                    <span class="courses-list__stat-label">Prix</span>
                                </div>
                            @elseif($course->effective_price && $course->effective_price > 0)
                                <div class="courses-list__stat">
                                    <i class="fas fa-tag"></i>
                                    <span class="courses-list__stat-value">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($course->effective_price, $baseCurrency ?? null) }}</span>
                                    <span class="courses-list__stat-label">Prix</span>
                                </div>
                            @else
                                <div class="courses-list__stat">
                                    <i class="fas fa-tag"></i>
                                    <span class="courses-list__stat-value">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($course->price ?? 0, $baseCurrency ?? null) }}</span>
                                    <span class="courses-list__stat-label">Prix</span>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="courses-list__actions">
                        <a href="{{ route('contents.show', $course->slug) }}" class="admin-btn outline sm" target="_blank" title="Voir">
                            <i class="fas fa-eye"></i>
                            <span class="courses-list__action-label">Voir</span>
                        </a>
                        <a href="{{ route('provider.contents.edit', $course) }}" class="admin-btn primary sm" title="Modifier">
                            <i class="fas fa-pen"></i>
                            <span class="courses-list__action-label">Modifier</span>
                        </a>
                        <a href="{{ route('provider.contents.lessons', $course) }}" class="admin-btn soft sm" title="Leçons">
                            <i class="fas fa-list"></i>
                            <span class="courses-list__action-label">Leçons</span>
                        </a>
                    </div>
                </div>
            @empty
                <div class="courses-list__empty">
                    <i class="fas fa-chalkboard fa-3x"></i>
                    <h3>Aucun contenu créé</h3>
                    <p>Aucun contenu créé pour le moment. Commencez par publier votre première formation.</p>
                    @if(Route::has('provider.contents.create'))
                        <a href="{{ route('provider.contents.create') }}" class="admin-btn primary">
                            <i class="fas fa-plus me-2"></i>Créer un contenu
                        </a>
                    @endif
                </div>
            @endforelse
            </div>
            <div class="mt-3">
                {{ $courses->links() }}
            </div>
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
        margin-bottom: 1.5rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid rgba(226, 232, 240, 0.7);
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
    .courses-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .courses-list__item {
        display: flex;
        gap: 1.25rem;
        background: #ffffff;
        border: 1px solid rgba(226, 232, 240, 0.7);
        border-radius: 1rem;
        padding: 1.25rem;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.08);
        transition: all 0.2s ease;
    }

    .courses-list__item:hover {
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.12);
        transform: translateY(-2px);
    }

    .courses-list__thumbnail {
        position: relative;
        width: 180px;
        height: 120px;
        border-radius: 12px;
        overflow: hidden;
        flex-shrink: 0;
        background: rgba(15, 23, 42, 0.05);
    }

    .courses-list__thumbnail img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .courses-list__thumbnail-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, rgba(0, 51, 102, 0.1), rgba(0, 51, 102, 0.05));
        color: var(--instructor-primary);
        font-size: 2rem;
    }

    .courses-list__status {
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
        padding: 0.25rem 0.6rem;
        border-radius: 999px;
        font-size: 0.7rem;
        text-transform: uppercase;
        font-weight: 700;
        letter-spacing: 0.05em;
    }

    .courses-list__status.is-published {
        background: rgba(34, 197, 94, 0.95);
        color: #ffffff;
    }

    .courses-list__status.is-draft {
        background: rgba(234, 179, 8, 0.95);
        color: #ffffff;
    }

    .courses-list__content {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 1rem;
        min-width: 0;
    }

    .courses-list__header {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .courses-list__title {
        margin: 0;
        font-size: 1.15rem;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.4;
    }

    .courses-list__meta {
        display: flex;
        gap: 1.25rem;
        flex-wrap: wrap;
        font-size: 0.875rem;
        color: var(--instructor-muted);
    }

    .courses-list__category,
    .courses-list__date {
        display: flex;
        align-items: center;
        gap: 0.35rem;
    }

    .courses-list__stats {
        display: flex;
        gap: 1.5rem;
        flex-wrap: wrap;
    }

    .courses-list__stat {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .courses-list__stat i {
        color: var(--instructor-secondary);
        font-size: 0.95rem;
    }

    .courses-list__stat-value {
        font-weight: 700;
        color: #0f172a;
        font-size: 0.95rem;
    }

    .courses-list__stat-label {
        font-size: 0.8rem;
        color: var(--instructor-muted);
    }

    .courses-list__actions {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        align-items: flex-end;
        justify-content: flex-start;
        flex-shrink: 0;
    }

    .courses-list__actions .admin-btn {
        min-width: 120px;
    }

    .courses-list__action-label {
        margin-left: 0.5rem;
    }

    .courses-list__empty {
        text-align: center;
        padding: 3rem 1.5rem;
        border-radius: 1.25rem;
        background: rgba(226, 232, 240, 0.5);
        display: flex;
        flex-direction: column;
        gap: 1rem;
        align-items: center;
        color: var(--instructor-muted);
    }

    .courses-list__empty i {
        color: var(--instructor-secondary);
        margin-bottom: 0.5rem;
    }

    .courses-list__empty h3 {
        margin: 0;
        font-size: 1.25rem;
        color: #0f172a;
    }

    .courses-list__empty p {
        margin: 0;
        font-size: 0.95rem;
    }

    @media (max-width: 1024px) {
        .courses-list__item {
            gap: 1rem;
            padding: 1rem;
        }

        .courses-list__thumbnail {
            width: 150px;
            height: 100px;
        }

        .courses-list__stats {
            gap: 1rem;
        }
    }

    @media (max-width: 768px) {
        .courses-filter {
            flex-direction: column;
            align-items: stretch;
            gap: 1rem;
            padding-bottom: 1rem;
        }

        .courses-list__item {
            flex-direction: column;
            gap: 1rem;
        }

        .courses-list__thumbnail {
            width: 100%;
            height: 180px;
        }

        .courses-list__actions {
            flex-direction: row;
            width: 100%;
            justify-content: stretch;
        }

        .courses-list__actions .admin-btn {
            flex: 1;
            min-width: 0;
        }

        .courses-list__action-label {
            display: none;
        }
    }

    @media (max-width: 640px) {
        .courses-list__item {
            padding: 0.875rem;
        }

        .courses-list__title {
            font-size: 1rem;
        }

        .courses-list__meta {
            flex-direction: column;
            gap: 0.5rem;
        }

        .courses-list__stats {
            flex-direction: column;
            gap: 0.75rem;
        }

        .courses-list__actions {
            flex-direction: column;
        }

        .courses-list__actions .admin-btn {
            width: 100%;
        }

        .courses-list__empty {
            padding: 2rem 1rem;
        }
    }
</style>
@endpush
