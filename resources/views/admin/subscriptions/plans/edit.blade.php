@extends('layouts.admin')

@section('admin-title', 'Modifier le plan')

@section('admin-content')
<section class="admin-panel">
    <div class="admin-panel__body">
        <form method="POST" action="{{ route('admin.subscriptions.plans.update', $plan) }}">
            @method('PUT')
            @include('admin.subscriptions.plans._form')
        </form>
    </div>
</section>
@endsection

