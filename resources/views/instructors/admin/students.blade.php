@extends('instructors.admin.layout')

@section('admin-title', 'Étudiants & progrès')
@section('admin-subtitle', 'Analysez l’engagement de vos apprenants, contactez-les et suivez leur progression globale.')

@section('admin-actions')
    <a href="{{ url('/instructor/courses') }}" class="btn btn-outline-primary">
        <i class="fas fa-chalkboard me-2"></i>Gérer mes cours
    </a>
@endsection

@section('admin-content')
    <section class="admin-card">
        <div class="students-summary">
            <div class="students-summary__item">
                <span>Total d’inscriptions</span>
                <strong>{{ number_format($enrollments->total()) }}</strong>
            </div>
            <div class="students-summary__item">
                <span>Progression moyenne</span>
                <strong>{{ number_format($averageProgress, 1) }}%</strong>
            </div>
            <div class="students-summary__item">
                <span>Étudiants actifs (30 j)</span>
                <strong>{{ number_format($activeStudents) }}</strong>
            </div>
        </div>

        <div class="students-table">
            <div class="students-table__head">
                <span>Étudiant</span>
                <span>Email</span>
                <span>Cours</span>
                <span>Progression</span>
                <span>Inscription</span>
                <span class="text-end">Actions</span>
            </div>
            @forelse($enrollments as $enrollment)
                <div class="students-table__row">
                    <div class="students-table__profile">
                        <div class="students-table__avatar">
                            <img src="{{ $enrollment->user?->avatar_url ?? asset('images/default-avatar.png') }}" alt="{{ $enrollment->user?->name }}">
                        </div>
                        <div>
                            <strong>{{ $enrollment->user?->name ?? 'Utilisateur inconnu' }}</strong>
                            <small>ID #{{ $enrollment->user?->id ?? '—' }}</small>
                        </div>
                    </div>
                    <div data-label="Email">
                        <a href="mailto:{{ $enrollment->user?->email }}" class="students-table__link">{{ $enrollment->user?->email }}</a>
                    </div>
                    <div data-label="Cours">{{ $enrollment->course?->title }}</div>
                    <div data-label="Progression">
                        <div class="students-progress">
                            <div class="students-progress__bar">
                                <span style="width: {{ $enrollment->progress }}%"></span>
                            </div>
                            <span class="students-progress__value">{{ $enrollment->progress }}%</span>
                        </div>
                    </div>
                    <div data-label="Inscription">{{ $enrollment->created_at->format('d/m/Y H:i') }}</div>
                    <div class="text-end">
                        <a href="{{ route('courses.show', $enrollment->course?->slug) }}" class="btn btn-outline-secondary btn-sm" target="_blank">
                            <i class="fas fa-eye"></i>
                        </a>
                    </div>
                </div>
            @empty
                <div class="students-table__empty">
                    <i class="fas fa-users fa-2x"></i>
                    <p>Aucun étudiant inscrit pour le moment. Dès qu’un étudiant rejoindra vos cours, il apparaîtra ici.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-3">
            {{ $enrollments->links() }}
        </div>
    </section>
@endsection

@push('styles')
<style>
    .students-summary {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    .students-summary__item {
        padding: 1.25rem 1.5rem;
        border-radius: 1.1rem;
        background: linear-gradient(135deg, rgba(14, 165, 233, 0.12), rgba(2, 132, 199, 0.08));
        color: #0f172a;
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
    }
    .students-summary__item span {
        font-size: 0.82rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #64748b;
        font-weight: 700;
    }
    .students-summary__item strong {
        font-size: 1.4rem;
        font-weight: 700;
    }

    .students-table {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }
    .students-table__head,
    .students-table__row {
        display: grid;
        grid-template-columns: minmax(0, 220px) repeat(4, minmax(0, 150px)) minmax(0, 120px);
        gap: 1rem;
        align-items: center;
        padding: 0.85rem 1rem;
        border-radius: 1rem;
    }
    .students-table__head {
        background: rgba(226, 232, 240, 0.55);
        font-size: 0.84rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #475569;
    }
    .students-table__row {
        background: #ffffff;
        border: 1px solid rgba(226, 232, 240, 0.6);
        box-shadow: 0 18px 35px -28px rgba(15, 23, 42, 0.18);
    }
    .students-table__profile {
        display: flex;
        align-items: center;
        gap: 0.85rem;
    }
    .students-table__avatar {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        overflow: hidden;
        border: 2px solid rgba(14, 165, 233, 0.35);
    }
    .students-table__avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .students-table__profile strong {
        display: block;
        color: #0f172a;
    }
    .students-table__profile small {
        color: #94a3b8;
        font-size: 0.78rem;
    }
    .students-table__link {
        color: var(--instructor-primary);
        text-decoration: none;
        font-weight: 600;
    }
    .students-progress {
        display: flex;
        align-items: center;
        gap: 0.65rem;
    }
    .students-progress__bar {
        flex: 1;
        height: 8px;
        border-radius: 999px;
        background: rgba(14, 165, 233, 0.18);
        overflow: hidden;
    }
    .students-progress__bar span {
        display: block;
        height: 100%;
        background: linear-gradient(90deg, #0284c7, #0ea5e9);
    }
    .students-progress__value {
        font-weight: 700;
        color: #0369a1;
        font-size: 0.85rem;
    }
    .students-table__empty {
        grid-column: 1/-1;
        text-align: center;
        padding: 2.5rem;
        border-radius: 1.25rem;
        background: rgba(226, 232, 240, 0.55);
        color: #64748b;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    @media (max-width: 1024px) {
        .students-table__head,
        .students-table__row {
            grid-template-columns: minmax(0, 220px) repeat(3, minmax(0, 140px)) minmax(0, 100px);
        }
        .students-table__row > div:nth-child(2) {
            display: none;
        }
    }
    @media (max-width: 768px) {
        .students-table__head {
            display: none;
        }
        .students-table__row {
            grid-template-columns: 1fr;
        }
        .students-table__row > div {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            align-items: center;
        }
        .students-table__row > div:first-child {
            justify-content: flex-start;
        }
        .students-table__row > div:not(:first-child):not(:last-child)::before {
            content: attr(data-label);
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #94a3b8;
        }
        .students-progress {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>
@endpush
