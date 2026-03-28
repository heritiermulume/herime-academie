@extends('layouts.admin')

@section('title', 'Packs de contenus')
@section('admin-title', 'Packs de contenus')
@section('admin-subtitle', 'Regroupez plusieurs contenus avec prix et visuels marketing')
@section('admin-actions')
    <a href="{{ route('admin.packages.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Nouveau pack
    </a>
@endsection

@section('admin-content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <section class="admin-panel admin-panel--main">
        <div class="admin-panel__body admin-panel__body--padded">
            <div class="table-responsive admin-table">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Pack</th>
                            <th>Contenus</th>
                            <th>Prix</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($packages as $package)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $package->title }}</div>
                                    <div class="small text-muted">{{ $package->slug }}</div>
                                </td>
                                <td>{{ $package->contents_count }}</td>
                                <td>
                                    @if($package->is_sale_active)
                                        <span class="text-decoration-line-through text-muted me-1">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($package->price) }}</span>
                                        <span class="fw-semibold text-success">{{ \App\Helpers\CurrencyHelper::formatWithSymbol($package->effective_price) }}</span>
                                    @else
                                        {{ \App\Helpers\CurrencyHelper::formatWithSymbol($package->effective_price) }}
                                    @endif
                                </td>
                                <td>
                                    @if($package->is_published)
                                        <span class="badge bg-success">Publié</span>
                                    @else
                                        <span class="badge bg-secondary">Brouillon</span>
                                    @endif
                                    @if($package->is_featured)
                                        <span class="badge bg-warning text-dark">À la une</span>
                                    @endif
                                </td>
                                <td class="text-end text-nowrap">
                                    @if($package->is_published)
                                        <a href="{{ route('packs.show', $package) }}" class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener" title="Voir sur le site">
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                    @endif
                                    <a href="{{ route('admin.packages.edit', $package) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.packages.destroy', $package) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce pack ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-5">Aucun pack pour le moment.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $packages->links() }}</div>
        </div>
    </section>
@endsection
