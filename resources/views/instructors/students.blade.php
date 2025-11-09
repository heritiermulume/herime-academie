@extends('layouts.dashboard')

@php($instructor = auth()->user())
@php
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Route;
@endphp
@include('instructors.partials.dashboard-context', ['instructor' => $instructor])

@section('title', 'Mes étudiants - Formateur')
@section('dashboard-title', 'Mes étudiants')
@section('dashboard-subtitle', 'Suivez les apprenants inscrits à vos cours et leurs progrès')
@section('dashboard-actions')
    <a href="{{ route('instructor.courses.list') }}" class="btn btn-primary">
        <i class="fas fa-book me-2"></i>Mes cours
    </a>
@endsection

@section('dashboard-content')
    <section class="card shadow-sm border-0">
        <div class="card-header card-header-primary d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="card-title mb-0 text-white fw-semibold">Liste des étudiants</h5>
                <small class="text-white-50">{{ $enrollments->total() }} inscription(s)</small>
            </div>
        </div>
        <div class="card-body p-0">
            @if($enrollments->count() > 0)
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Étudiant</th>
                                <th>Email</th>
                                <th>Cours</th>
                                <th>Date d'inscription</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($enrollments as $enrollment)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <img src="{{ $enrollment->user->avatar_url }}" alt="{{ $enrollment->user->name }}" class="rounded-circle" style="width: 48px; height: 48px; object-fit: cover;">
                                            <div>
                                                <div class="fw-semibold">{{ $enrollment->user->name }}</div>
                                                <div class="text-muted small">ID #{{ $enrollment->user->id }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="mailto:{{ $enrollment->user->email }}" class="text-decoration-none">{{ $enrollment->user->email }}</a>
                                    </td>
                                    <td>{{ Str::limit($enrollment->course->title, 70) }}</td>
                                    <td>{{ $enrollment->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('courses.show', $enrollment->course->slug) }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-eye me-1"></i>Voir le cours</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white border-0 py-3">
                    <div class="d-flex justify-content-center">
                        {{ $enrollments->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Aucun étudiant pour le moment</h5>
                    <p class="text-muted">Dès qu’un étudiant s’inscrira à vos cours, il apparaîtra ici.</p>
                </div>
            @endif
        </div>
    </section>
@endsection
