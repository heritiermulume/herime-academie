@extends('layouts.admin')

@section('title', 'Commissions Ambassadeurs')
@section('admin-title', 'Commissions Ambassadeurs')

@section('admin-content')
    <section class="admin-panel admin-panel--main">
        <div class="admin-panel__body">
            <div class="admin-table mt-4">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Ambassadeur</th>
                                <th>Commande</th>
                                <th>Montant</th>
                                <th>Commission</th>
                                <th>Statut</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($commissions as $commission)
                                <tr>
                                    <td>{{ $commission->ambassador->user->name }}</td>
                                    <td>{{ $commission->order->order_number }}</td>
                                    <td>{{ number_format($commission->order_total, 2) }} {{ \App\Models\Setting::getBaseCurrency() }}</td>
                                    <td>{{ number_format($commission->commission_amount, 2) }} {{ \App\Models\Setting::getBaseCurrency() }}</td>
                                    <td>
                                        <span class="badge bg-{{ $commission->getStatusBadgeClass() }}">
                                            {{ $commission->getStatusLabel() }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if($commission->status === 'pending')
                                            <form method="POST" action="{{ route('admin.ambassadors.commissions.approve', $commission) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success">Approuver</button>
                                            </form>
                                        @endif
                                        @if($commission->status === 'approved')
                                            <form method="POST" action="{{ route('admin.ambassadors.commissions.mark-paid', $commission) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-primary">Marquer payée</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">Aucune commission</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <x-admin.pagination :paginator="$commissions" />
        </div>
    </section>
@endsection



