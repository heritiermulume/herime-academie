@extends('layouts.admin')

@section('admin-title', 'Membre Herime — nouvelle offre')
@section('admin-subtitle', 'Création des trois périodes (trimestre, semestre, année) en une fois.')
@section('admin-actions')
    <a href="{{ route('admin.subscriptions.plans.index') }}" class="btn btn-light">
        <i class="fas fa-arrow-left me-2"></i>Retour à la liste
    </a>
@endsection

@section('admin-content')
<section class="admin-panel">
    <div class="admin-panel__body">
        <form method="POST" action="{{ route('admin.subscriptions.plans.store') }}">
            @include('admin.subscriptions.plans._form_membre_bundle', ['memberBundlePlans' => collect()])
        </form>
    </div>
</section>
@endsection
