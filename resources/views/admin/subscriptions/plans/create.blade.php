@extends('layouts.admin')

@section('admin-title', 'Nouveau plan d\'abonnement')
@section('admin-actions')
    <a href="{{ route('admin.subscriptions.plans.index') }}" class="btn btn-light">
        <i class="fas fa-arrow-left me-2"></i>Retour à la liste
    </a>
@endsection

@section('admin-content')
<section class="admin-panel">
    <div class="admin-panel__body">
        <form method="POST" action="{{ route('admin.subscriptions.plans.store') }}">
            @include('admin.subscriptions.plans._form')
        </form>
    </div>
</section>
@endsection

