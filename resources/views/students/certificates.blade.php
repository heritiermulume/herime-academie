@extends('layouts.dashboard')

@php($user = auth()->user())
@php
    use Illuminate\Support\Str;
@endphp
@include('students.partials.dashboard-context', ['user' => $user])

@section('title', 'Mes certificats - Herime Académie')
@section('dashboard-title', 'Mes certificats')
@section('dashboard-subtitle', 'Téléchargez vos attestations et célébrez vos réussites')
@section('dashboard-actions')
    <a href="{{ route('student.courses') }}" class="btn btn-primary">
        <i class="fas fa-graduation-cap me-2"></i>Mes cours
    </a>
@endsection

@section('dashboard-content')
    <section class="card shadow-sm border-0">
        <div class="card-header card-header-primary d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="card-title mb-0 text-white fw-semibold">Liste des certificats</h5>
                <small class="text-white-50">{{ $certificates->total() }} certificat(s) obtenu(s)</small>
            </div>
        </div>
        <div class="card-body">
            @if($certificates->count() > 0)
                <div class="row g-4">
                    @foreach($certificates as $certificate)
                        <div class="col-md-6 col-xl-4">
                            <div class="certificate-card h-100">
                                <div class="certificate-card__badge">
                                    <i class="fas fa-certificate"></i>
                                </div>
                                <h5 class="certificate-card__title">{{ Str::limit($certificate->course->title, 65) }}</h5>
                                <p class="certificate-card__subtitle">
                                    <i class="fas fa-user-tie me-1"></i>{{ $certificate->course->instructor->name }}
                                </p>
                                <p class="certificate-card__date">
                                    <i class="fas fa-calendar me-1"></i>Obtenu le {{ $certificate->created_at->format('d/m/Y') }}
                                </p>
                                <div class="certificate-card__actions">
                                    <a href="{{ \App\Helpers\FileHelper::url($certificate->certificate_url) }}" target="_blank" class="btn btn-outline-primary w-100">
                                        <i class="fas fa-eye me-1"></i>Prévisualiser
                                    </a>
                                    <a href="{{ route('certificates.download', $certificate->id) }}" class="btn btn-primary w-100">
                                        <i class="fas fa-download me-1"></i>Télécharger
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="pt-4">
                    {{ $certificates->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-certificate fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Aucun certificat obtenu</h5>
                    <p class="text-muted">Terminez un cours pour débloquer votre premier certificat.</p>
                    <a href="{{ route('student.courses') }}" class="btn btn-primary">
                        <i class="fas fa-book-open me-2"></i>Voir mes cours
                    </a>
                </div>
            @endif
        </div>
    </section>
@endsection

@push('styles')
<style>
    .certificate-card {
        background: #ffffff;
        border-radius: 1.25rem;
        padding: 1.75rem 1.5rem;
        box-shadow: 0 15px 35px -30px rgba(0, 51, 102, 0.5);
        border: 1px solid rgba(226, 232, 240, 0.8);
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .certificate-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 45px -28px rgba(0, 51, 102, 0.55);
    }
    .certificate-card__badge {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        background: rgba(255, 204, 51, 0.15);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        color: #fbbf24;
        margin-bottom: 0.25rem;
    }
    .certificate-card__title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #0b1f3a;
        margin: 0;
    }
    .certificate-card__subtitle,
    .certificate-card__date {
        margin: 0;
        color: #6b7a8e;
        font-size: 0.9rem;
    }
    .certificate-card__actions {
        display: grid;
        gap: 0.65rem;
        margin-top: auto;
    }
</style>
@endpush

