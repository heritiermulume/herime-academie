@extends('students.admin.layout')

@section('admin-title', 'Mes certificats')
@section('admin-subtitle', 'Téléchargez vos attestations et gardez une trace de vos accomplissements.')

@section('admin-actions')
    <a href="{{ route('student.courses') }}" class="admin-btn primary">
        <i class="fas fa-graduation-cap me-2"></i>Retourner à mes contenus
    </a>
@endsection

@section('admin-content')
<section class="admin-panel admin-panel--main">
    <div class="admin-panel__body">
        <div class="admin-stats-grid">
            <div class="admin-stat-card">
                <p class="admin-stat-card__label">Certificats obtenus</p>
                <p class="admin-stat-card__value">{{ number_format($certificateSummary['total'] ?? 0) }}</p>
                <p class="admin-stat-card__muted">Depuis votre inscription</p>
            </div>
            <div class="admin-stat-card">
                <p class="admin-stat-card__label">Cette année</p>
                <p class="admin-stat-card__value">{{ number_format($certificateSummary['issued_this_year'] ?? 0) }}</p>
                <p class="admin-stat-card__muted">Bravo pour vos progrès récents</p>
            </div>
            <div class="admin-stat-card">
                <p class="admin-stat-card__label">Dernier certificat</p>
                @if(!empty($certificateSummary['recent']))
                    <p class="admin-stat-card__value">
                        {{ optional($certificateSummary['recent']->issued_at)->format('d/m/Y') }}
                    </p>
                    <p class="admin-stat-card__muted">
                        {{ \Illuminate\Support\Str::limit($certificateSummary['recent']->course->title ?? $certificateSummary['recent']->title, 40) }}
                    </p>
                @else
                    <p class="admin-stat-card__value">-</p>
                    <p class="admin-stat-card__muted">Pas encore de certificat</p>
                @endif
            </div>
        </div>
    </div>
</section>

<section class="admin-panel">
    <div class="admin-panel__header">
        <h3>
            <i class="fas fa-certificate me-2"></i>Tous mes certificats
        </h3>
        <div class="admin-panel__actions">
            <a href="{{ route('student.dashboard') }}" class="admin-btn soft">
                Retour au tableau de bord
            </a>
        </div>
    </div>
    <div class="admin-panel__body">

        @if($certificates->isEmpty())
            <div class="admin-empty-state">
                <i class="fas fa-certificate"></i>
                <p>Vous n'avez pas encore obtenu de certificat.</p>
                <a href="{{ route('student.courses') }}" class="admin-btn primary sm mt-3">
                    Continuer mes formations
                </a>
            </div>
        @else
            <div class="student-certificate-list">
                @foreach($certificates as $certificate)
                    <div class="student-certificate-item">
                        <div class="student-certificate-item__icon">
                            <i class="fas fa-award"></i>
                        </div>
                        <div class="student-certificate-item__info">
                            <span>#{{ $certificate->certificate_number }}</span>
                            <h4>{{ $certificate->course->title ?? $certificate->title }}</h4>
                            <p>
                                {{ $certificate->course->instructor->name ?? 'Herime Académie' }}
                                · {{ optional($certificate->issued_at)->format('d/m/Y') }}
                            </p>
                            @if($certificate->description)
                                <small>{{ \Illuminate\Support\Str::limit($certificate->description, 120) }}</small>
                            @endif
                        </div>
                        <div class="student-certificate-item__actions">
                            @if($certificate->file_path)
                                <a href="{{ asset('storage/' . $certificate->file_path) }}" target="_blank" class="admin-btn ghost sm">
                                    <i class="fas fa-eye me-1"></i>Prévisualiser
                                </a>
                            @endif
                            <a href="{{ route('certificates.download', $certificate->id) }}" class="admin-btn primary sm">
                                <i class="fas fa-download me-1"></i>Télécharger
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <x-student.pagination :paginator="$certificates" :showInfo="true" itemName="certificats" />
        @endif
    </div>
</section>
@endsection

@push('styles')
<style>
    .admin-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        border-radius: 0.85rem;
        font-weight: 600;
        text-decoration: none;
        padding: 0.65rem 1.2rem;
        border: 1px solid transparent;
        transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.2s ease;
        color: inherit;
    }

    .admin-btn.primary {
        background: linear-gradient(90deg, var(--student-primary), #0b4f99);
        color: #ffffff;
        box-shadow: 0 22px 38px -28px rgba(30, 58, 138, 0.55);
    }

    .admin-btn.primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 26px 44px -28px rgba(30, 58, 138, 0.45);
    }

    .admin-btn.success {
        background: linear-gradient(90deg, #22c55e, #16a34a);
        color: #ffffff;
        box-shadow: 0 22px 38px -28px rgba(34, 197, 94, 0.55);
    }

    .admin-btn.success:hover {
        transform: translateY(-2px);
        box-shadow: 0 26px 44px -28px rgba(34, 197, 94, 0.45);
        background: linear-gradient(90deg, #16a34a, #15803d);
    }

    .admin-btn.ghost {
        border-color: rgba(30, 58, 138, 0.3);
        color: #ffffff;
        background: var(--student-primary);
    }

    .admin-btn.ghost:hover {
        background: rgba(30, 58, 138, 0.9);
        border-color: var(--student-primary);
    }

    .admin-btn.soft {
        border-color: rgba(148, 163, 184, 0.4);
        background: rgba(148, 163, 184, 0.12);
        color: var(--student-primary-dark);
        padding: 0.55rem 1rem;
        font-size: 0.85rem;
    }

    .admin-btn.sm {
        padding: 0.5rem 0.9rem;
        border-radius: 0.75rem;
        font-size: 0.85rem;
    }

    .admin-panel {
        margin-bottom: 1.5rem;
        background: var(--student-card-bg);
        border-radius: 1.25rem;
        box-shadow: 0 22px 45px -35px rgba(15, 23, 42, 0.25);
        border: 1px solid rgba(226, 232, 240, 0.7);
    }

    .admin-panel__header {
        padding: 1.25rem 1.75rem;
        background: linear-gradient(120deg, var(--student-primary) 0%, var(--student-primary-dark) 100%);
        color: #ffffff;
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        justify-content: space-between;
        align-items: center;
        border-radius: 1.25rem 1.25rem 0 0;
    }

    .admin-panel__header h2,
    .admin-panel__header h3,
    .admin-panel__header h4 {
        margin: 0;
        font-weight: 600;
        color: #ffffff;
    }

    .admin-panel__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.65rem;
    }

    .admin-panel__actions .admin-btn.soft {
        color: #ffffff;
        background: rgba(255, 255, 255, 0.15);
        border-color: rgba(255, 255, 255, 0.3);
    }

    .admin-panel__actions .admin-btn.soft:hover {
        background: rgba(255, 255, 255, 0.25);
        border-color: rgba(255, 255, 255, 0.5);
    }
    
    .admin-panel__body {
        padding: 1.75rem;
    }

    .admin-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 1.25rem;
    }

    .admin-stat-card {
        background: linear-gradient(135deg, rgba(30, 58, 138, 0.07) 0%, rgba(30, 58, 138, 0.15) 100%);
        border-radius: 1rem;
        padding: 1rem 1.25rem;
        color: var(--student-primary-dark);
        border: 1px solid rgba(30, 58, 138, 0.1);
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }

    .admin-stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 28px 60px -45px rgba(30, 58, 138, 0.35);
    }

    .admin-stat-card__label {
        text-transform: uppercase;
        letter-spacing: 0.08em;
        font-size: 0.65rem;
        margin-bottom: 0.4rem;
        color: var(--student-primary);
        font-weight: 600;
    }

    .admin-stat-card__value {
        font-size: 1.5rem;
        font-weight: 700;
        margin: 0;
        color: var(--student-primary-dark);
        line-height: 1.2;
    }

    .admin-stat-card__muted {
        margin-top: 0.25rem;
        color: var(--student-muted);
        font-size: 0.8rem;
    }

    .student-certificate-list {
        display: flex;
        flex-direction: column;
        gap: 1.1rem;
    }

    .student-certificate-item {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr) auto;
        gap: 1rem;
        padding: 1.15rem 1.35rem;
        border-radius: 1.1rem;
        border: 1px solid rgba(226, 232, 240, 0.7);
        background: rgba(248, 250, 252, 0.6);
        transition: box-shadow 0.18s ease, transform 0.18s ease;
        align-items: center;
    }

    .student-certificate-item:hover {
        transform: translateY(-3px);
        box-shadow: 0 18px 45px -35px rgba(30, 58, 138, 0.28);
    }

    .student-certificate-item__icon {
        width: 50px;
        height: 50px;
        border-radius: 999px;
        background: rgba(250, 204, 21, 0.18);
        color: #d97706;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
    }

    .student-certificate-item__info {
        display: flex;
        flex-direction: column;
        gap: 0.4rem;
    }

    .student-certificate-item__info span {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--student-muted);
        font-weight: 600;
    }

    .student-certificate-item__info h4 {
        margin: 0;
        font-size: 1rem;
        font-weight: 700;
        color: var(--student-primary-dark);
    }

    .student-certificate-item__info p {
        margin: 0;
        font-size: 0.85rem;
        color: var(--student-muted);
    }

    .student-certificate-item__info small {
        font-size: 0.78rem;
        color: var(--student-muted);
    }

    .student-certificate-item__actions {
        display: flex;
        gap: 0.6rem;
        flex-wrap: wrap;
    }


    .admin-empty-state {
        padding: 3rem 1.5rem;
        text-align: center;
        color: var(--student-muted);
        border: 1px dashed rgba(148, 163, 184, 0.35);
        border-radius: 1.25rem;
        background: rgba(255, 255, 255, 0.65);
    }

    .admin-empty-state i {
        font-size: 2.2rem;
        margin-bottom: 1rem;
        color: rgba(30, 58, 138, 0.35);
    }

    @media (max-width: 991.98px) {
        .admin-panel {
            margin-bottom: 1rem;
        }
        
        .admin-panel--main .admin-panel__body {
            padding: 1rem 0.5rem !important;
        }
        
        .admin-stats-grid {
            gap: 0.5rem !important;
        }
        
        .admin-stat-card {
            padding: 0.75rem 0.875rem !important;
        }
        
        .admin-stat-card__value {
            font-size: 1.5rem;
        }
        
        .admin-panel__header {
            padding: 0.75rem 1rem;
        }

        .admin-panel__header h3 {
            font-size: 1rem;
        }
        
        .admin-panel__body {
            padding: 1rem;
        }
        
        .student-certificate-list {
            gap: 0.75rem;
        }
        
        .student-certificate-item {
            padding: 0.875rem 1rem;
        }
    }

    @media (max-width: 767.98px) {
        .admin-panel {
            margin-bottom: 0.75rem;
        }
        
        .admin-panel--main .admin-panel__body {
            padding: 0.75rem 0.25rem !important;
        }
        
        .admin-stats-grid {
            gap: 0.375rem !important;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        }
        
        .admin-stat-card {
            padding: 0.5rem 0.625rem !important;
        }
        
        .admin-stat-card__value {
            font-size: 1.35rem;
        }
        
        .admin-stat-card__label {
            font-size: 0.7rem;
        }
        
        .admin-stat-card__muted {
            font-size: 0.75rem;
        }
        
        .admin-panel__header {
            padding: 0.5rem 0.75rem;
        }

        .admin-panel__header h3 {
            font-size: 0.95rem;
        }
        
        .admin-panel__body {
            padding: 0.5rem 0.25rem;
        }
        
        .student-certificate-list {
            gap: 0.5rem;
            padding: 0;
        }
        
        .student-certificate-item {
            grid-template-columns: 1fr;
            gap: 0.75rem;
            padding: 0.5rem;
            align-items: flex-start;
        }

        .student-certificate-item__actions {
            width: 100%;
            flex-direction: column;
        }

        .admin-btn {
            width: 100%;
            padding: 0.5rem 0.75rem;
            font-size: 0.8rem;
        }

        .admin-btn.sm {
            padding: 0.4rem 0.7rem;
            font-size: 0.75rem;
        }

        .admin-panel__actions .admin-btn {
            width: auto;
            font-size: 0.75rem;
            padding: 0.4rem 0.7rem;
        }

        .student-certificate-item__info h4 {
            font-size: 0.85rem;
        }

        .student-certificate-item__info p {
            font-size: 0.75rem;
        }

        .student-certificate-item__info span {
            font-size: 0.7rem;
        }

        .admin-empty-state {
            padding: 1.5rem 0.75rem;
        }
        
        .admin-empty-state i {
            font-size: 1.5rem;
        }

        .admin-empty-state p {
            font-size: 0.85rem;
        }
    }
</style>
@endpush










