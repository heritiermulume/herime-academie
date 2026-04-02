@extends('layouts.admin')

@section('title', 'Utilisateurs inscrits - ' . $course->title)
@section('admin-title', 'Utilisateurs inscrits')
@section('admin-subtitle', 'Contenu : ' . Str::limit($course->title, 60))

@section('admin-actions')
    <div class="admin-actions-grid">
        <a href="{{ route('admin.contents.show', $course) }}" class="btn btn-light">
            <i class="fas fa-arrow-left me-2"></i>Retour au contenu
        </a>
        @if(!$course->is_free)
        <a href="{{ route('admin.contents.purchases', $course) }}" class="btn btn-outline-primary">
            <i class="fas fa-shopping-cart me-2"></i>Achats
        </a>
        @endif
        @if($course->is_downloadable)
        <a href="{{ route('admin.contents.downloads', $course) }}" class="btn btn-outline-secondary">
            <i class="fas fa-download me-2"></i>Téléchargements
        </a>
        @endif
    </div>
@endsection

@section('admin-content')
@php
    use App\Models\LessonProgress;
    $downloadsByUser = collect($course->downloads ?? [])->groupBy('user_id');
@endphp

    <section class="admin-panel admin-panel--main admin-panel--table-no-inner-scroll">
        <div class="admin-panel__body">
            <x-admin.search-panel
                :action="route('admin.contents.enrollments', $course)"
                formId="enrollmentsFilterForm"
                filtersId="enrollmentsFilters"
                :hasFilters="false"
                :searchValue="request('search')"
                placeholder="Rechercher par nom ou email..."
            />

            <div class="admin-table admin-table--no-inner-scroll">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Utilisateur</th>
                                <th>
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'direction' => request('sort') == 'created_at' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark">
                                        Inscription
                                        @if(request('sort') == 'created_at')
                                            <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                        @else
                                            <i class="fas fa-sort ms-1 text-muted"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'progress', 'direction' => request('sort') == 'progress' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark">
                                        Progression
                                        @if(request('sort') == 'progress')
                                            <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                        @else
                                            <i class="fas fa-sort ms-1 text-muted"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>Formation terminée</th>
                                @if($course->is_downloadable)
                                    <th>Téléchargements</th>
                                @endif
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($enrollments as $enrollment)
                                @php
                                    $user = $enrollment->user;
                                    $progress = $enrollment->progress ?? 0;
                                    $isCompleted = $enrollment->status === 'completed' || (!is_null($enrollment->completed_at));
                                    $userDownloads = $downloadsByUser->get($enrollment->user_id, collect());
                                    $downloadsCount = $userDownloads->count();
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
                                            <span class="text-muted">Utilisateur supprimé</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="text-muted small">
                                            <div>
                                                <i class="far fa-calendar-alt me-1"></i>
                                                {{ optional($enrollment->created_at)->format('d/m/Y') ?? 'N/A' }}
                                            </div>
                                            @if($enrollment->order_id)
                                                <div>
                                                    <i class="fas fa-receipt me-1"></i>
                                                    Commande #{{ $enrollment->order_id }}
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td style="min-width: 180px;">
                                        <div class="d-flex flex-column gap-1">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="small text-muted">Progression</span>
                                                <span class="fw-semibold">{{ number_format($progress, 0) }}%</span>
                                            </div>
                                            <div class="progress" style="height: 6px;">
                                                <div class="progress-bar {{ $progress >= 100 ? 'bg-success' : 'bg-info' }}"
                                                     role="progressbar"
                                                     style="width: {{ min(100, max(0, (int) $progress)) }}%;"
                                                     aria-valuenow="{{ (int) $progress }}"
                                                     aria-valuemin="0"
                                                     aria-valuemax="100">
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($isCompleted)
                                            <span class="badge bg-success">
                                                <i class="fas fa-check-circle me-1"></i>Terminée
                                            </span>
                                            @if($enrollment->completed_at)
                                                <div class="small text-muted mt-1">
                                                    le {{ $enrollment->completed_at->format('d/m/Y') }}
                                                </div>
                                            @endif
                                        @else
                                            <span class="badge bg-secondary">
                                                <i class="fas fa-hourglass-half me-1"></i>En cours
                                            </span>
                                        @endif
                                    </td>
                                    @if($course->is_downloadable)
                                        <td>
                                            @if($downloadsCount > 0)
                                                <span class="fw-semibold">{{ $downloadsCount }} téléchargement{{ $downloadsCount > 1 ? 's' : '' }}</span>
                                            @else
                                                <span class="text-muted small">Aucun</span>
                                            @endif
                                        </td>
                                    @endif
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
                                    <td colspan="{{ $course->is_downloadable ? 6 : 5 }}" class="text-center py-5">
                                        <i class="fas fa-users fa-3x text-muted mb-3 d-block"></i>
                                        <p class="text-muted mb-0">Aucun utilisateur inscrit à ce contenu.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <x-admin.pagination :paginator="$enrollments" :showInfo="true" itemName="inscrits" />
            </div>
        </div>
    </section>
@endsection
