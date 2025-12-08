@extends('layouts.admin')

@section('title', 'Candidatures Ambassadeur')
@section('admin-title', 'Candidatures Ambassadeur')
@section('admin-subtitle', 'Gérez les candidatures au programme ambassadeur')

@section('admin-content')
    <section class="admin-panel admin-panel--main">
        <div class="admin-panel__body">
            <x-admin.search-panel
                :action="route('admin.ambassadors.applications')"
                formId="applicationsFilterForm"
                filtersId="applicationsFilters"
                :hasFilters="true"
                :searchValue="request('search')"
                placeholder="Rechercher par nom ou email..."
            >
                <x-slot:filters>
                    <div class="admin-form-grid admin-form-grid--two mb-3">
                        <div>
                            <label class="form-label fw-semibold">Statut</label>
                            <select class="form-select" name="status">
                                <option value="all">Tous les statuts</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                                <option value="under_review" {{ request('status') === 'under_review' ? 'selected' : '' }}>En examen</option>
                                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approuvée</option>
                                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejetée</option>
                            </select>
                        </div>
                    </div>
                </x-slot:filters>
            </x-admin.search-panel>

            <div class="admin-table mt-4">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Utilisateur</th>
                                <th>Date</th>
                                <th>Statut</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($applications as $application)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <img src="{{ $application->user->avatar_url }}" alt="{{ $application->user->name }}" class="admin-user-avatar">
                                            <div>
                                                <div class="fw-semibold">{{ $application->user->name }}</div>
                                                <div class="text-muted small">{{ $application->user->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $application->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <span class="badge bg-{{ $application->getStatusBadgeClass() }}">
                                            {{ $application->getStatusLabel() }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.ambassadors.applications.show', $application) }}" class="btn btn-light btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4">Aucune candidature</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <x-admin.pagination :paginator="$applications" />
        </div>
    </section>
@endsection



