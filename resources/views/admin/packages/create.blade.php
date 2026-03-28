@extends('layouts.admin')

@section('title', 'Nouveau pack')
@section('admin-title', 'Créer un pack')
@section('admin-subtitle', 'Associez plusieurs contenus et définissez prix, médias et textes marketing')
@section('admin-actions')
    <a href="{{ route('admin.packages.index') }}" class="btn btn-light" data-temp-upload-cancel><i class="fas fa-arrow-left me-2"></i>Retour</a>
@endsection

@include('partials.upload-progress-modal')

@section('admin-content')
    @if ($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif
    <div class="admin-panel">
        <div class="admin-panel__body admin-panel__body--padded">
            <form id="packageForm" action="{{ route('admin.packages.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @include('admin.packages._form', ['package' => null, 'courses' => $courses])
                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Créer le pack</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@include('admin.packages.partials.thumbnail-chunk-upload')
