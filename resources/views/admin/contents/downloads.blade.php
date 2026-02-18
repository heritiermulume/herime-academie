@extends('layouts.admin')

@section('title', 'Téléchargements - ' . $course->title)
@section('admin-title', 'Historique des téléchargements')
@section('admin-subtitle', 'Contenu : ' . Str::limit($course->title, 60))

@section('admin-actions')
    <div class="admin-actions-grid">
        <a href="{{ route('admin.contents.show', $course) }}" class="btn btn-light">
            <i class="fas fa-arrow-left me-2"></i>Retour au contenu
        </a>
        <a href="{{ route('admin.contents.enrollments', $course) }}" class="btn btn-outline-primary">
            <i class="fas fa-users me-2"></i>Inscrits
        </a>
        @if(!$course->is_free)
        <a href="{{ route('admin.contents.purchases', $course) }}" class="btn btn-outline-secondary">
            <i class="fas fa-shopping-cart me-2"></i>Achats
        </a>
        @endif
    </div>
@endsection

@section('admin-content')
    <section class="admin-panel admin-panel--main">
        <div class="admin-panel__body">
            <x-admin.search-panel
                :action="route('admin.contents.downloads', $course)"
                formId="downloadsFilterForm"
                filtersId="downloadsFilters"
                :hasFilters="false"
                :searchValue="request('search')"
                placeholder="Rechercher par nom ou email..."
            />

            <div class="admin-table">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Utilisateur</th>
                                <th>Type</th>
                                <th>
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'direction' => request('sort') == 'created_at' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark">
                                        Date
                                        @if(request('sort') == 'created_at')
                                            <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                        @else
                                            <i class="fas fa-sort ms-1 text-muted"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>IP / Localisation</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($downloads as $download)
                                @php
                                    $user = $download->user;
                                @endphp
                                <tr>
                                    <td style="min-width: 220px;">
                                        @if($user)
                                            <div class="d-flex align-items-center gap-3">
                                                <img src="{{ $user->avatar ?? asset('images/default-avatar.svg') }}"
                                                     alt="{{ $user->name }}"
                                                     class="rounded-circle flex-shrink-0"
                                                     style="width: 40px; height: 40px; object-fit: cover;">
                                                <div class="flex-grow-1 min-w-0">
                                                    <div class="fw-semibold text-truncate">{{ $user->name }}</div>
                                                    <div class="text-muted small text-truncate">{{ $user->email }}</div>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted">Utilisateur inconnu / supprimé</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary text-capitalize">
                                            {{ $download->download_type ?: 'inconnu' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="text-muted small">
                                            <div>
                                                <i class="far fa-calendar-alt me-1"></i>
                                                {{ optional($download->created_at)->format('d/m/Y') ?? 'N/A' }}
                                            </div>
                                            <div>
                                                <i class="far fa-clock me-1"></i>
                                                {{ optional($download->created_at)->format('H:i') ?? 'N/A' }}
                                            </div>
                                        </div>
                                    </td>
                                    <td style="min-width: 200px;">
                                        <div class="text-muted small">
                                            @if($download->ip_address)
                                                <div><strong>IP :</strong> {{ $download->ip_address }}</div>
                                            @endif
                                            @if($download->country_name || $download->city || $download->region)
                                                <div>
                                                    <strong>Localisation :</strong>
                                                    {{ $download->city ? $download->city . ', ' : '' }}
                                                    {{ $download->region ? $download->region . ', ' : '' }}
                                                    {{ $download->country_name ?? $download->country ?? '' }}
                                                </div>
                                            @endif
                                            @if(!$download->ip_address && !$download->country_name && !$download->city && !$download->region)
                                                <span class="text-muted">Non disponible</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        @if($user)
                                            <a href="{{ route('admin.users.show', $user) }}" class="btn btn-light btn-sm" title="Voir le profil">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <i class="fas fa-download fa-3x text-muted mb-3 d-block"></i>
                                        <p class="text-muted mb-0">Aucun téléchargement enregistré pour ce contenu.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <x-admin.pagination :paginator="$downloads" :showInfo="true" itemName="téléchargements" />
            </div>
        </div>
    </section>
@endsection
