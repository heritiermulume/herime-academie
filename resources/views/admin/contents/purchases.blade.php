@extends('layouts.admin')

@section('title', 'Achats - ' . $course->title)
@section('admin-title', 'Utilisateurs ayant acheté')
@section('admin-subtitle', 'Contenu : ' . Str::limit($course->title, 60))

@section('admin-actions')
    <div class="admin-actions-grid">
        <a href="{{ route('admin.contents.show', $course) }}" class="btn btn-light">
            <i class="fas fa-arrow-left me-2"></i>Retour au contenu
        </a>
        <a href="{{ route('admin.contents.enrollments', $course) }}" class="btn btn-outline-primary">
            <i class="fas fa-users me-2"></i>Inscrits
        </a>
        @if($course->is_downloadable)
        <a href="{{ route('admin.contents.downloads', $course) }}" class="btn btn-outline-secondary">
            <i class="fas fa-download me-2"></i>Téléchargements
        </a>
        @endif
    </div>
@endsection

@section('admin-content')
    <section class="admin-panel admin-panel--main">
        <div class="admin-panel__body">
            <x-admin.search-panel
                :action="route('admin.contents.purchases', $course)"
                formId="purchasesFilterForm"
                filtersId="purchasesFilters"
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
                                <th>Commande</th>
                                <th>
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'total', 'direction' => request('sort') == 'total' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark">
                                        Montant
                                        @if(request('sort') == 'total')
                                            <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                        @else
                                            <i class="fas fa-sort ms-1 text-muted"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'direction' => request('sort') == 'created_at' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark">
                                        Date d'achat
                                        @if(request('sort') == 'created_at')
                                            <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                        @else
                                            <i class="fas fa-sort ms-1 text-muted"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>Statut</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($purchases as $orderItem)
                                @php
                                    $order = $orderItem->order;
                                    $user = $order->user ?? null;
                                    $paidAt = $order->paid_at ?? $order->created_at;
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
                                        <a href="{{ route('admin.orders.show', $order) }}" class="fw-semibold text-decoration-none">
                                            #{{ $order->order_number ?: $order->id }}
                                        </a>
                                    </td>
                                    <td>
                                        {{ \App\Helpers\CurrencyHelper::formatWithSymbol($orderItem->total ?? 0, $order->currency ?? 'USD') }}
                                    </td>
                                    <td>
                                        <div class="text-muted small">
                                            @if($paidAt)
                                                <i class="far fa-calendar-alt me-1"></i>{{ $paidAt->format('d/m/Y') }}
                                                <br>
                                                <i class="far fa-clock me-1"></i>{{ $paidAt->format('H:i') }}
                                            @else
                                                —
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge {{ $order->status === 'completed' ? 'bg-success' : 'bg-primary' }}">
                                            {{ ucfirst($order->status ?? 'N/A') }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-light btn-sm" title="Voir la commande">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($user)
                                            <a href="{{ route('admin.users.show', $user) }}" class="btn btn-primary btn-sm" title="Voir l'utilisateur">
                                                <i class="fas fa-user"></i>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <i class="fas fa-shopping-cart fa-3x text-muted mb-3 d-block"></i>
                                        <p class="text-muted mb-0">Aucun achat enregistré pour ce contenu.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <x-admin.pagination :paginator="$purchases" :showInfo="true" itemName="achats" />
            </div>
        </div>
    </section>
@endsection
