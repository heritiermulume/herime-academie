@extends('layouts.app')

@section('title', 'Détails de l\'utilisateur')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Administration</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.users') }}">Utilisateurs</a></li>
                        <li class="breadcrumb-item active">Détails</li>
                    </ol>
                </div>
                <h4 class="page-title">Détails de l'utilisateur</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <img src="{{ $user->avatar ? Storage::url($user->avatar) : asset('images/default-avatar.svg') }}" alt="Avatar" class="rounded-circle mb-3" width="120" height="120">
                    
                    <h5 class="card-title">{{ $user->name }}</h5>
                    <p class="text-muted">{{ $user->email }}</p>
                    
                    <div class="mb-3">
                        @switch($user->role)
                            @case('admin')
                                <span class="badge bg-danger">Administrateur</span>
                                @break
                            @case('instructor')
                                <span class="badge bg-success">Formateur</span>
                                @break
                            @case('affiliate')
                                <span class="badge bg-info">Affilié</span>
                                @break
                            @default
                                <span class="badge bg-primary">Étudiant</span>
                        @endswitch
                    </div>
                    
                    <div class="d-flex justify-content-center gap-2">
                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-edit"></i> Modifier
                        </a>
                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline" 
                              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="fas fa-trash"></i> Supprimer
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informations personnelles</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>Téléphone:</strong>
                            <p class="text-muted">{{ $user->phone ?? 'Non renseigné' }}</p>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <strong>Date de naissance:</strong>
                            <p class="text-muted">{{ $user->date_of_birth ? $user->date_of_birth->format('d/m/Y') : 'Non renseignée' }}</p>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <strong>Genre:</strong>
                            <p class="text-muted">
                                @switch($user->gender)
                                    @case('male')
                                        Homme
                                        @break
                                    @case('female')
                                        Femme
                                        @break
                                    @case('other')
                                        Autre
                                        @break
                                    @default
                                        Non renseigné
                                @endswitch
                            </p>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <strong>Statut du compte:</strong>
                            <p>
                                @if($user->is_active)
                                    <span class="badge bg-success">Actif</span>
                                @else
                                    <span class="badge bg-danger">Inactif</span>
                                @endif
                                
                                @if($user->is_verified)
                                    <span class="badge bg-info ms-1">Vérifié</span>
                                @else
                                    <span class="badge bg-warning ms-1">Non vérifié</span>
                                @endif
                            </p>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <strong>Membre depuis:</strong>
                            <p class="text-muted">{{ $user->created_at->format('d/m/Y à H:i') }}</p>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <strong>Dernière connexion:</strong>
                            <p class="text-muted">{{ $user->last_login_at ? $user->last_login_at->format('d/m/Y à H:i') : 'Jamais' }}</p>
                        </div>
                    </div>
                    
                    @if($user->bio)
                        <div class="mb-3">
                            <strong>Biographie:</strong>
                            <p class="text-muted">{{ $user->bio }}</p>
                        </div>
                    @endif
                </div>
            </div>
            
            @if($user->website || $user->linkedin || $user->twitter || $user->youtube)
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Réseaux sociaux</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @if($user->website)
                                <div class="col-md-6 mb-2">
                                    <strong>Site web:</strong>
                                    <p><a href="{{ $user->website }}" target="_blank" class="text-decoration-none">{{ $user->website }}</a></p>
                                </div>
                            @endif
                            
                            @if($user->linkedin)
                                <div class="col-md-6 mb-2">
                                    <strong>LinkedIn:</strong>
                                    <p><a href="{{ $user->linkedin }}" target="_blank" class="text-decoration-none">{{ $user->linkedin }}</a></p>
                                </div>
                            @endif
                            
                            @if($user->twitter)
                                <div class="col-md-6 mb-2">
                                    <strong>Twitter:</strong>
                                    <p><a href="{{ $user->twitter }}" target="_blank" class="text-decoration-none">{{ $user->twitter }}</a></p>
                                </div>
                            @endif
                            
                            @if($user->youtube)
                                <div class="col-md-6 mb-2">
                                    <strong>YouTube:</strong>
                                    <p><a href="{{ $user->youtube }}" target="_blank" class="text-decoration-none">{{ $user->youtube }}</a></p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
            
            @if($user->role == 'instructor')
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Statistiques du formateur</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 text-center">
                                <h4 class="text-primary">{{ $user->courses_count ?? 0 }}</h4>
                                <p class="text-muted mb-0">Cours créés</p>
                            </div>
                            <div class="col-md-4 text-center">
                                <h4 class="text-success">{{ $user->enrollments_count ?? 0 }}</h4>
                                <p class="text-muted mb-0">Étudiants</p>
                            </div>
                            <div class="col-md-4 text-center">
                                <h4 class="text-info">{{ number_format($user->courses->avg('stats.average_rating') ?? 0, 1) }}/5</h4>
                                <p class="text-muted mb-0">Note moyenne</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
