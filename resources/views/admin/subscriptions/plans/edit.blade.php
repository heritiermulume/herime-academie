@extends('layouts.admin')

@section('admin-title', 'Membre Herime — périodes et prix')
@section('admin-subtitle', 'Paramètres des trois formules : trimestre, semestre et année.')
@section('admin-actions')
    <a href="{{ route('admin.subscriptions.plans.index') }}" class="btn btn-light">
        <i class="fas fa-arrow-left me-2"></i>Retour à la liste
    </a>
@endsection

@section('admin-content')
<section class="admin-panel">
    <div class="admin-panel__body">
        <form method="POST" action="{{ route('admin.subscriptions.plans.membre.update') }}">
            @method('PUT')
            @include('admin.subscriptions.plans._form_membre_bundle', ['memberBundlePlans' => $memberBundlePlans])
        </form>
    </div>
</section>
@endsection
