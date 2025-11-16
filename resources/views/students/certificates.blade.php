@extends('students.admin.layout')

@section('admin-title', 'Mes certificats')
@section('admin-subtitle', 'Téléchargez vos attestations et gardez une trace de vos accomplissements.')

@section('admin-actions')
    <a href="{{ route('student.courses') }}" class="admin-btn primary">
        <i class="fas fa-graduation-cap me-2"></i>Retourner à mes cours
    </a>
@endsection

@section('admin-content')
<div class="student-certificates">
    <div class="student-certificates__summary">
        <div class="certificate-summary-card">
            <span class="certificate-summary-card__label">Certificats obtenus</span>
            <strong class="certificate-summary-card__value">{{ number_format($certificateSummary['total'] ?? 0) }}</strong>
            <small class="certificate-summary-card__hint">Depuis votre inscription</small>
        </div>
        <div class="certificate-summary-card">
            <span class="certificate-summary-card__label">Cette année</span>
            <strong class="certificate-summary-card__value">{{ number_format($certificateSummary['issued_this_year'] ?? 0) }}</strong>
            <small class="certificate-summary-card__hint text-success">Bravo pour vos progrès récents</small>
        </div>
        <div class="certificate-summary-card">
            <span class="certificate-summary-card__label">Dernier certificat</span>
            @if(!empty($certificateSummary['recent']))
                <strong class="certificate-summary-card__value">
                    {{ optional($certificateSummary['recent']->issued_at)->format('d/m/Y') }}
                </strong>
                <small class="certificate-summary-card__hint">
                    {{ \Illuminate\Support\Str::limit($certificateSummary['recent']->course->title ?? $certificateSummary['recent']->title, 40) }}
                </small>
            @else
                <strong class="certificate-summary-card__value">-</strong>
                <small class="certificate-summary-card__hint">Pas encore de certificat</small>
            @endif
        </div>
    </div>

    <div class="admin-card">
        <div class="student-certificates__header">
            <div>
                <h3 class="admin-card__title">Tous mes certificats</h3>
                <p class="admin-card__subtitle">
                    @if(($certificateSummary['total'] ?? 0) > 0)
                        Téléchargez et partagez vos attestations officielles.
                    @else
                        Terminez un cours pour débloquer vos premières attestations.
                    @endif
                </p>
            </div>
            <a href="{{ route('student.dashboard') }}" class="admin-btn soft">
                <i class="fas fa-arrow-left me-2"></i>Retour au tableau de bord
            </a>
        </div>

        @if($certificates->isEmpty())
            <div class="admin-empty-state">
                <i class="fas fa-certificate"></i>
                <p>Vous n’avez pas encore obtenu de certificat.</p>
                <a href="{{ route('student.courses') }}" class="admin-btn primary sm mt-3">
                    Continuer mes cours
                </a>
            </div>
        @else
            <div class="student-certificates__grid">
                @foreach($certificates as $certificate)
                    <div class="student-certificate-card">
                        <div class="student-certificate-card__badge">
                            <i class="fas fa-award"></i>
                        </div>
                        <div class="student-certificate-card__body">
                            <span class="student-certificate-card__number">#{{ $certificate->certificate_number }}</span>
                            <h4>{{ $certificate->course->title ?? $certificate->title }}</h4>
                            <p>
                                {{ $certificate->course->instructor->name ?? 'Herime Académie' }}
                                · {{ optional($certificate->issued_at)->format('d/m/Y') }}
                            </p>
                            @if($certificate->description)
                                <small>{{ \Illuminate\Support\Str::limit($certificate->description, 120) }}</small>
                            @endif
                        </div>
                        <div class="student-certificate-card__actions">
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

            <div class="student-certificates__pagination">
                {{ $certificates->links() }}
            </div>
        @endif
    </div>
</div>
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
        background: linear-gradient(90deg, #2563eb, #4f46e5);
        color: #ffffff;
        box-shadow: 0 22px 38px -28px rgba(37, 99, 235, 0.55);
    }

    .admin-btn.primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 26px 44px -28px rgba(37, 99, 235, 0.45);
    }

    .admin-btn.ghost {
        border-color: rgba(37, 99, 235, 0.18);
        color: #2563eb;
        background: transparent;
    }

    .admin-btn.soft {
        border-color: rgba(148, 163, 184, 0.4);
        background: rgba(148, 163, 184, 0.12);
        color: #0f172a;
        padding: 0.55rem 1rem;
        font-size: 0.85rem;
    }

    .admin-btn.sm {
        padding: 0.5rem 0.9rem;
        border-radius: 0.75rem;
        font-size: 0.85rem;
    }

    .student-certificates {
        display: flex;
        flex-direction: column;
        gap: 1.75rem;
    }

    .student-certificates__summary {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.35rem;
    }

    .certificate-summary-card {
        padding: 1.35rem 1.45rem;
        border-radius: 1.15rem;
        border: 1px solid rgba(226, 232, 240, 0.7);
        background: #ffffff;
        box-shadow: 0 18px 45px -35px rgba(15, 23, 42, 0.18);
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
    }

    .certificate-summary-card__label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #64748b;
        font-weight: 600;
    }

    .certificate-summary-card__value {
        font-size: 1.65rem;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.2;
    }

    .certificate-summary-card__hint {
        font-size: 0.82rem;
        color: #94a3b8;
    }

    .student-certificates__header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .student-certificates__grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 1.35rem;
    }

    .student-certificate-card {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        padding: 1.5rem;
        border-radius: 1.25rem;
        border: 1px solid rgba(226, 232, 240, 0.7);
        background: rgba(255, 255, 255, 0.95);
        box-shadow: 0 22px 55px -45px rgba(15, 23, 42, 0.28);
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }

    .student-certificate-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 28px 60px -40px rgba(37, 99, 235, 0.35);
    }

    .student-certificate-card__badge {
        width: 58px;
        height: 58px;
        border-radius: 999px;
        background: rgba(250, 204, 21, 0.18);
        color: #d97706;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.6rem;
    }

    .student-certificate-card__body {
        display: flex;
        flex-direction: column;
        gap: 0.4rem;
    }

    .student-certificate-card__number {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: #94a3b8;
        font-weight: 600;
    }

    .student-certificate-card__body h4 {
        margin: 0;
        font-size: 1rem;
        font-weight: 700;
        color: #0f172a;
    }

    .student-certificate-card__body p {
        margin: 0;
        font-size: 0.88rem;
        color: #475569;
    }

    .student-certificate-card__body small {
        font-size: 0.78rem;
        color: #94a3b8;
    }

    .student-certificate-card__actions {
        display: flex;
        gap: 0.6rem;
        flex-wrap: wrap;
    }

    .student-certificates__pagination {
        margin-top: 1.75rem;
        display: flex;
        justify-content: center;
    }

    @media (max-width: 640px) {
        .admin-btn {
            width: 100%;
        }

        .student-certificates__header {
            flex-direction: column;
            align-items: stretch;
        }
    }
</style>
@endpush









