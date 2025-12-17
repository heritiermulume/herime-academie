@extends('ambassadors.admin.layout')

@section('admin-title', 'Détails du retrait')
@section('admin-subtitle', 'Informations complètes sur votre retrait')

@section('admin-content')
<div class="payout-detail-page">
    <div class="back-button">
        <a href="{{ route('wallet.payouts') }}" class="btn btn-outline-custom">
            <i class="fas fa-arrow-left me-2"></i>Retour aux retraits
        </a>
    </div>

    <div class="payout-detail-card">
        {{-- En-tête avec statut --}}
        <div class="payout-header">
            <div>
                <h2 class="payout-amount-title">{{ number_format($payout->amount, 2) }} {{ $payout->currency }}</h2>
                <p class="payout-provider">{{ $payout->provider_name }}</p>
            </div>
            <div class="payout-status-badge">
                {!! $payout->status_badge !!}
            </div>
        </div>

        <hr class="my-4">

        {{-- Informations principales --}}
        <div class="info-section">
            <h4 class="section-title"><i class="fas fa-info-circle me-2"></i>Informations générales</h4>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Date de demande</span>
                    <span class="info-value">{{ $payout->created_at->translatedFormat('d M Y à H:i') }}</span>
                </div>

                @if($payout->moneroo_id)
                <div class="info-item">
                    <span class="info-label">ID Moneroo</span>
                    <span class="info-value"><code>{{ $payout->moneroo_id }}</code></span>
                </div>
                @endif

                <div class="info-item">
                    <span class="info-label">Méthode</span>
                    <span class="info-value">{{ $payout->method }}</span>
                </div>

                <div class="info-item">
                    <span class="info-label">Pays</span>
                    <span class="info-value">{{ $payout->country }}</span>
                </div>

                <div class="info-item">
                    <span class="info-label">Numéro de téléphone</span>
                    <span class="info-value">{{ $payout->phone }}</span>
                </div>

                <div class="info-item">
                    <span class="info-label">Devise</span>
                    <span class="info-value">{{ $payout->currency }}</span>
                </div>
            </div>
        </div>

        @if($payout->description)
        <div class="info-section">
            <h4 class="section-title"><i class="fas fa-comment me-2"></i>Description</h4>
            <p class="description-text">{{ $payout->description }}</p>
        </div>
        @endif

        {{-- Informations financières --}}
        <div class="info-section">
            <h4 class="section-title"><i class="fas fa-money-bill-wave me-2"></i>Détails financiers</h4>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Montant demandé</span>
                    <span class="info-value strong">{{ number_format($payout->amount, 2) }} {{ $payout->currency }}</span>
                </div>

                @if($payout->fee)
                <div class="info-item">
                    <span class="info-label">Frais de transaction</span>
                    <span class="info-value">{{ number_format($payout->fee, 2) }} {{ $payout->currency }}</span>
                </div>
                @endif

                @if($payout->net_amount)
                <div class="info-item">
                    <span class="info-label">Montant net reçu</span>
                    <span class="info-value strong success">{{ number_format($payout->net_amount, 2) }} {{ $payout->currency }}</span>
                </div>
                @endif
            </div>
        </div>

        {{-- Informations de statut --}}
        <div class="info-section">
            <h4 class="section-title"><i class="fas fa-clock me-2"></i>Timeline</h4>
            <div class="timeline">
                <div class="timeline-item {{ $payout->initiated_at ? 'completed' : '' }}">
                    <div class="timeline-marker"></div>
                    <div class="timeline-content">
                        <span class="timeline-label">Initié</span>
                        <span class="timeline-time">
                            {{ $payout->initiated_at ? $payout->initiated_at->translatedFormat('d M Y à H:i') : 'En attente' }}
                        </span>
                    </div>
                </div>

                <div class="timeline-item {{ $payout->completed_at || $payout->failed_at ? 'completed' : '' }}">
                    <div class="timeline-marker"></div>
                    <div class="timeline-content">
                        <span class="timeline-label">
                            {{ $payout->status === 'completed' ? 'Complété' : ($payout->status === 'failed' ? 'Échoué' : 'En cours') }}
                        </span>
                        <span class="timeline-time">
                            @if($payout->completed_at)
                                {{ $payout->completed_at->translatedFormat('d M Y à H:i') }}
                            @elseif($payout->failed_at)
                                {{ $payout->failed_at->translatedFormat('d M Y à H:i') }}
                            @else
                                En attente
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Message d'erreur si échec --}}
        @if($payout->failure_reason)
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle me-2"></i>
            <strong>Raison de l'échec :</strong><br>
            {{ $payout->failure_reason }}
        </div>
        @endif

        {{-- Informations techniques --}}
        @if($payout->moneroo_data)
        <div class="info-section">
            <h4 class="section-title"><i class="fas fa-code me-2"></i>Données techniques</h4>
            <details class="technical-details">
                <summary>Afficher les données Moneroo (JSON)</summary>
                <pre>{{ json_encode($payout->moneroo_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </details>
        </div>
        @endif

        {{-- Actions --}}
        <div class="payout-actions">
            @if($payout->canBeCancelled())
            <form action="{{ route('wallet.cancel-payout', $payout) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir annuler ce retrait ?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-times me-2"></i>Annuler ce retrait
                </button>
            </form>
            @endif

            @if($payout->moneroo_id && in_array($payout->status, ['pending', 'processing']))
            <form action="{{ route('wallet.check-payout-status', $payout) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-sync me-2"></i>Vérifier le statut
                </button>
            </form>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.payout-detail-page {
    padding: 0;
}

.back-button {
    margin-bottom: 1.5rem;
}

.btn-outline-custom {
    background: white;
    color: #374151;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    border: 2px solid #d1d5db;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s;
}

.btn-outline-custom:hover {
    background: #f9fafb;
    border-color: #9ca3af;
}

.payout-detail-card {
    background: white;
    border-radius: 16px;
    padding: 2rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    border: 1px solid rgba(0, 0, 0, 0.05);
}

.payout-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}

.payout-amount-title {
    font-size: 2rem;
    font-weight: 700;
    color: #111827;
    margin-bottom: 0.5rem;
}

.payout-provider {
    font-size: 1.125rem;
    color: #6b7280;
}

.info-section {
    margin-bottom: 2rem;
}

.section-title {
    font-size: 1.125rem;
    font-weight: 700;
    color: #111827;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.info-label {
    font-size: 0.875rem;
    color: #6b7280;
    font-weight: 500;
}

.info-value {
    font-size: 1rem;
    color: #111827;
}

.info-value.strong {
    font-weight: 700;
    font-size: 1.125rem;
}

.info-value.success {
    color: #10b981;
}

.info-value code {
    background: #f3f4f6;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.875rem;
    color: #6b7280;
}

.description-text {
    padding: 1rem;
    background: #f9fafb;
    border-radius: 8px;
    color: #374151;
    margin: 0;
}

.timeline {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.timeline-item {
    display: flex;
    gap: 1rem;
    align-items: flex-start;
}

.timeline-marker {
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: #e5e7eb;
    border: 3px solid white;
    box-shadow: 0 0 0 2px #e5e7eb;
    margin-top: 4px;
    flex-shrink: 0;
}

.timeline-item.completed .timeline-marker {
    background: #10b981;
    box-shadow: 0 0 0 2px #10b981;
}

.timeline-content {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.timeline-label {
    font-weight: 600;
    color: #111827;
}

.timeline-time {
    font-size: 0.875rem;
    color: #6b7280;
}

.technical-details {
    margin-top: 1rem;
}

.technical-details summary {
    cursor: pointer;
    padding: 0.75rem;
    background: #f3f4f6;
    border-radius: 8px;
    font-weight: 600;
    color: #374151;
}

.technical-details pre {
    margin-top: 1rem;
    padding: 1rem;
    background: #1f2937;
    color: #f9fafb;
    border-radius: 8px;
    font-size: 0.875rem;
    overflow-x: auto;
}

.payout-actions {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid #e5e7eb;
}

.alert {
    padding: 1rem 1.25rem;
    border-radius: 12px;
    margin-bottom: 1.5rem;
}

.alert-danger {
    background: #fee2e2;
    border: 1px solid #fecaca;
    color: #dc2626;
}

@media (max-width: 768px) {
    .payout-detail-card {
        padding: 1.5rem;
    }

    .payout-header {
        flex-direction: column;
        gap: 1rem;
    }

    .payout-amount-title {
        font-size: 1.5rem;
    }

    .info-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }

    .payout-actions {
        flex-direction: column;
    }

    .payout-actions button {
        width: 100%;
    }
}
</style>
@endpush

