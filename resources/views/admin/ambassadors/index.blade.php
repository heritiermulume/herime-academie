@extends('layouts.admin')

@section('title', 'Ambassadeurs')
@section('admin-title', 'Gestion des Ambassadeurs')

@section('admin-content')
    <section class="admin-panel admin-panel--main">
        <div class="admin-panel__body">
            <div class="admin-table mt-4">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Ambassadeur</th>
                                <th>Code Promo</th>
                                <th>Gains totaux</th>
                                <th>Statut</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ambassadors as $ambassador)
                                <tr>
                                    <td>{{ $ambassador->user->name }}</td>
                                    <td>
                                        @if($ambassador->activePromoCode())
                                            <code>{{ $ambassador->activePromoCode()->code }}</code>
                                        @else
                                            <span class="text-muted">Aucun code</span>
                                        @endif
                                    </td>
                                    <td>{{ number_format($ambassador->total_earnings, 2) }} {{ \App\Models\Setting::getBaseCurrency() }}</td>
                                    <td>
                                        <span class="badge bg-{{ $ambassador->is_active ? 'success' : 'secondary' }}">
                                            {{ $ambassador->is_active ? 'Actif' : 'Inactif' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.ambassadors.show', $ambassador) }}" class="btn btn-light btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">Aucun ambassadeur</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <x-admin.pagination :paginator="$ambassadors" />
        </div>
    </section>
@endsection

