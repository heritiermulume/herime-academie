@extends('layouts.app')

@section('title', 'Candidatures Formateur - Admin')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-light btn-sm" title="Tableau de bord">
                                <i class="fas fa-tachometer-alt"></i>
                            </a>
                            <h4 class="mb-0">
                                <i class="fas fa-user-graduate me-2"></i>Candidatures Formateur
                            </h4>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Statistiques -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h5 class="card-title">{{ $stats['total'] }}</h5>
                                    <p class="card-text small">Total</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h5 class="card-title">{{ $stats['pending'] }}</h5>
                                    <p class="card-text small">En attente</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h5 class="card-title">{{ $stats['under_review'] }}</h5>
                                    <p class="card-text small">En examen</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h5 class="card-title">{{ $stats['approved'] }}</h5>
                                    <p class="card-text small">Approuvées</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filtres -->
                    <form method="GET" action="{{ route('admin.instructor-applications') }}" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <input type="text" 
                                       class="form-control" 
                                       name="search" 
                                       placeholder="Rechercher par nom ou email..." 
                                       value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="status">
                                    <option value="">Tous les statuts</option>
                                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                                    <option value="under_review" {{ request('status') === 'under_review' ? 'selected' : '' }}>En examen</option>
                                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approuvée</option>
                                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejetée</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search me-2"></i>Filtrer
                                </button>
                            </div>
                            <div class="col-md-2">
                                <a href="{{ route('admin.instructor-applications') }}" class="btn btn-outline-secondary w-100">
                                    <i class="fas fa-times me-2"></i>Réinitialiser
                                </a>
                            </div>
                        </div>
                    </form>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Utilisateur</th>
                                    <th>Date de soumission</th>
                                    <th>Statut</th>
                                    <th>Révisé par</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($applications as $application)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div style="width: 40px; height: 40px; border-radius: 50%; overflow: hidden; flex-shrink: 0; margin-right: 12px;">
                                                    <img src="{{ $application->user->avatar_url }}" 
                                                         alt="{{ $application->user->name }}" 
                                                         style="width: 100%; height: 100%; object-fit: cover; display: block;">
                                                </div>
                                                <div>
                                                    <div class="fw-bold">{{ $application->user->name }}</div>
                                                    <small class="text-muted">{{ $application->user->email }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <small>{{ $application->created_at->format('d/m/Y H:i') }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $application->getStatusBadgeClass() }}">
                                                {{ $application->getStatusLabel() }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($application->reviewer)
                                                <small>{{ $application->reviewer->name }}</small>
                                            @else
                                                <small class="text-muted">-</small>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.instructor-applications.show', $application) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i> Voir
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5">
                                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">Aucune candidature trouvée</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4">
                        {{ $applications->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

