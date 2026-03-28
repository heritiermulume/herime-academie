@extends('layouts.admin')

@section('title', 'Modifier le pack')
@section('admin-title', 'Modifier le pack')
@section('admin-subtitle', $package->title)
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
            <form action="{{ route('admin.packages.update', $package) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('admin.packages._form', ['package' => $package, 'courses' => $courses])
                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@include('admin.packages.partials.thumbnail-chunk-upload')
