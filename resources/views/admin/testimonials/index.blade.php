@extends('layouts.app')

@section('title', 'Gestion des témoignages - Admin')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-quote-left me-2"></i>Gestion des témoignages
                        </h4>
                        <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#createTestimonialModal">
                            <i class="fas fa-plus me-1"></i>Nouveau témoignage
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Grille des témoignages -->
                    <div class="row">
                        @forelse($testimonials as $testimonial)
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        @if($testimonial->photo)
                                        <img src="{{ $testimonial->photo }}" 
                                             alt="{{ $testimonial->name }}" 
                                             class="rounded-circle me-3" 
                                             width="50" 
                                             height="50"
                                             style="object-fit: cover;">
                                        @else
                                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3" 
                                             style="width: 50px; height: 50px;">
                                            {{ substr($testimonial->name, 0, 1) }}
                                        </div>
                                        @endif
                                        
                                        <div>
                                            <h6 class="mb-0">{{ $testimonial->name }}</h6>
                                            @if($testimonial->title)
                                            <small class="text-muted">{{ $testimonial->title }}</small>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="text-warning mb-2">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fas fa-star{{ $i <= $testimonial->rating ? '' : '-o' }}"></i>
                                        @endfor
                                    </div>
                                    
                                    <p class="card-text">{{ Str::limit($testimonial->testimonial, 150) }}</p>
                                    
                                    @if($testimonial->company)
                                    <small class="text-muted">
                                        <i class="fas fa-building me-1"></i>{{ $testimonial->company }}
                                    </small>
                                    @endif
                                </div>
                                
                                <div class="card-footer bg-light">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            {{ $testimonial->created_at->format('d/m/Y') }}
                                        </small>
                                        <div class="d-flex gap-1">
                                            <button class="btn btn-sm btn-outline-warning" onclick="editTestimonial({{ $testimonial->id }})">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" onclick="deleteTestimonial({{ $testimonial->id }})">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="col-12">
                            <div class="text-center py-5">
                                <i class="fas fa-quote-left fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Aucun témoignage trouvé</p>
                            </div>
                        </div>
                        @endforelse
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $testimonials->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de création de témoignage -->
<div class="modal fade" id="createTestimonialModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nouveau témoignage</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.testimonials.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Nom *</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="title" class="form-label">Titre</label>
                                <input type="text" class="form-control" id="title" name="title">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="company" class="form-label">Entreprise</label>
                                <input type="text" class="form-control" id="company" name="company">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="photo" class="form-label">Photo (URL)</label>
                                <input type="url" class="form-control" id="photo" name="photo">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="testimonial" class="form-label">Témoignage *</label>
                        <textarea class="form-control" id="testimonial" name="testimonial" rows="4" required></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="rating" class="form-label">Note *</label>
                                <select class="form-select" id="rating" name="rating" required>
                                    <option value="1">1 étoile</option>
                                    <option value="2">2 étoiles</option>
                                    <option value="3">3 étoiles</option>
                                    <option value="4">4 étoiles</option>
                                    <option value="5" selected>5 étoiles</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" checked>
                                    <label class="form-check-label" for="is_active">
                                        Témoignage actif
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Créer le témoignage</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal d'édition -->
<div class="modal fade" id="editTestimonialModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Modifier le témoignage</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editTestimonialForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_name" class="form-label">Nom *</label>
                                <input type="text" class="form-control" id="edit_name" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_title" class="form-label">Titre</label>
                                <input type="text" class="form-control" id="edit_title" name="title">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_company" class="form-label">Entreprise</label>
                                <input type="text" class="form-control" id="edit_company" name="company">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_photo" class="form-label">URL de la photo</label>
                                <input type="url" class="form-control" id="edit_photo" name="photo">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_testimonial" class="form-label">Témoignage *</label>
                        <textarea class="form-control" id="edit_testimonial" name="testimonial" rows="4" required></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_rating" class="form-label">Note *</label>
                                <select class="form-select" id="edit_rating" name="rating" required>
                                    <option value="1">1 étoile</option>
                                    <option value="2">2 étoiles</option>
                                    <option value="3">3 étoiles</option>
                                    <option value="4">4 étoiles</option>
                                    <option value="5">5 étoiles</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active" value="1">
                                <label class="form-check-label" for="edit_is_active">
                                    Témoignage actif
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Modifier le témoignage</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function editTestimonial(id) {
    // Récupérer les données du témoignage via AJAX
    fetch(`/admin/testimonials/${id}/edit`)
        .then(response => response.json())
        .then(data => {
            // Remplir le formulaire d'édition
            document.getElementById('edit_name').value = data.name || '';
            document.getElementById('edit_title').value = data.title || '';
            document.getElementById('edit_company').value = data.company || '';
            document.getElementById('edit_photo').value = data.photo || '';
            document.getElementById('edit_testimonial').value = data.testimonial || '';
            document.getElementById('edit_rating').value = data.rating || '5';
            document.getElementById('edit_is_active').checked = data.is_active || false;
            
            // Mettre à jour l'action du formulaire
            document.getElementById('editTestimonialForm').action = `/admin/testimonials/${id}`;
            
            // Ouvrir le modal
            const modal = new bootstrap.Modal(document.getElementById('editTestimonialModal'));
            modal.show();
        })
        .catch(error => {
            console.error('Erreur lors du chargement du témoignage:', error);
            alert('Erreur lors du chargement du témoignage');
        });
}

function deleteTestimonial(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce témoignage ?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/testimonials/${id}`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        
        form.appendChild(csrfToken);
        form.appendChild(methodField);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endpush
