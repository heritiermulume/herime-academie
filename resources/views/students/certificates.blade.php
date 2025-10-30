@extends('layouts.app')

@section('title', 'Mes Certificats - Herime Académie')

@section('content')
<div class="container py-5">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 text-white rounded-3 p-3" style="background-color:#003366;">
        <div class="d-flex align-items-center">
            <a href="{{ route('student.dashboard') }}" class="btn btn-outline-light me-3" title="Tableau de bord">
                <i class="fas fa-tachometer-alt"></i>
            </a>
            <div>
                <h1 class="h3 mb-1 text-white">
                    <i class="fas fa-certificate text-warning me-2"></i>
                    Mes Certificats
                </h1>
                <p class="mb-0 text-white-50">Tous les certificats que vous avez obtenus</p>
            </div>
        </div>
    </div>

    @if($certificates->count() > 0)
        <!-- Certificates Grid -->
        <div class="row g-4">
            @foreach($certificates as $certificate)
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm border-0 hover-shadow">
                        <div class="card-body text-center d-flex flex-column">
                            <div class="mb-3">
                                <i class="fas fa-certificate fa-4x text-warning"></i>
                            </div>
                            
                            <h5 class="card-title">{{ $certificate->course->title }}</h5>
                            <p class="text-muted small mb-2">
                                <i class="fas fa-user-tie me-1"></i>{{ $certificate->course->instructor->name }}
                            </p>
                            
                            <div class="mt-auto">
                                <p class="text-muted small mb-2">
                                    <i class="fas fa-calendar me-1"></i>
                                    Obtenu le {{ $certificate->created_at->format('d/m/Y') }}
                                </p>
                                
                                <button class="btn btn-outline-primary w-100" onclick="window.open('{{ route('certificates.download', $certificate->id) }}', '_blank')">
                                    <i class="fas fa-download me-2"></i>Télécharger
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $certificates->links() }}
        </div>
    @else
        <!-- Empty State -->
        <div class="text-center py-5">
            <i class="fas fa-certificate fa-4x text-muted mb-4"></i>
            <h3 class="text-muted mb-3">Aucun certificat obtenu</h3>
            <p class="text-muted mb-4">Terminez vos cours pour obtenir vos certificats !</p>
            <a href="{{ route('student.dashboard') }}" class="btn btn-primary btn-lg">
                <i class="fas fa-book-open me-2"></i>Voir mes cours
            </a>
        </div>
    @endif
</div>

@push('styles')
<style>
.hover-shadow {
    transition: all 0.3s ease;
}

.hover-shadow:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15) !important;
}

.card-title {
    font-size: 1.1rem;
    font-weight: 600;
    line-height: 1.4;
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}

.btn-outline-primary {
    color: #003366;
    border-color: #003366;
    border-radius: 8px;
    font-weight: 600;
}

.btn-outline-primary:hover {
    background-color: #003366;
    color: white;
}

.btn-primary {
    background-color: #003366;
    border-color: #003366;
    border-radius: 8px;
    font-weight: 600;
}

.btn-primary:hover {
    background-color: #004080;
    border-color: #004080;
}

.text-warning {
    color: #ffcc33 !important;
}
</style>
@endpush
@endsection

