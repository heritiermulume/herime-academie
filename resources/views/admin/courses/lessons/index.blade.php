@extends('layouts.app')

@section('title', 'Gestion des leçons - ' . $course->title)

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-list me-2"></i>Gestion des leçons - {{ $course->title }}
                        </h4>
                        <div>
                            <a href="{{ route('admin.courses.lessons.create', $course) }}" class="btn btn-light btn-sm me-2">
                                <i class="fas fa-plus me-1"></i>Nouvelle leçon
                            </a>
                            <a href="{{ route('admin.courses.show', $course) }}" class="btn btn-outline-light btn-sm">
                                <i class="fas fa-arrow-left me-1"></i>Retour au cours
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if($course->sections->count() > 0)
                        <div class="accordion" id="lessonsAccordion">
                            @foreach($course->sections->sortBy('sort_order') as $section)
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="heading{{ $section->id }}">
                                        <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" 
                                                type="button" data-bs-toggle="collapse" 
                                                data-bs-target="#collapse{{ $section->id }}" 
                                                aria-expanded="{{ $loop->first ? 'true' : 'false' }}" 
                                                aria-controls="collapse{{ $section->id }}">
                                            <div class="d-flex justify-content-between w-100 me-3">
                                                <span>{{ $section->title }}</span>
                                                <span class="badge bg-primary">{{ $section->lessons->count() }} leçons</span>
                                            </div>
                                        </button>
                                    </h2>
                                    <div id="collapse{{ $section->id }}" 
                                         class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" 
                                         aria-labelledby="heading{{ $section->id }}" 
                                         data-bs-parent="#lessonsAccordion">
                                        <div class="accordion-body">
                                            @if($section->description)
                                                <p class="text-muted mb-3">{{ $section->description }}</p>
                                            @endif
                                            
                                            @if($section->lessons->count() > 0)
                                                <div class="table-responsive">
                                                    <table class="table table-hover">
                                                        <thead>
                                                            <tr>
                                                                <th>Titre</th>
                                                                <th>Type</th>
                                                                <th>Durée</th>
                                                                <th>Statut</th>
                                                                <th>Aperçu</th>
                                                                <th>Actions</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($section->lessons->sortBy('sort_order') as $lesson)
                                                                <tr>
                                                                    <td>
                                                                        <div class="d-flex align-items-center">
                                                                            <i class="fas fa-{{ $lesson->type === 'video' ? 'video' : ($lesson->type === 'text' ? 'file-alt' : ($lesson->type === 'quiz' ? 'question' : 'tasks')) }} me-2 text-primary"></i>
                                                                            <div>
                                                                                <strong>{{ $lesson->title }}</strong>
                                                                                @if($lesson->description)
                                                                                    <br><small class="text-muted">{{ Str::limit($lesson->description, 50) }}</small>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                    <td>
                                                                        <span class="badge bg-{{ $lesson->type === 'video' ? 'danger' : ($lesson->type === 'text' ? 'info' : ($lesson->type === 'quiz' ? 'warning' : 'success')) }}">
                                                                            {{ ucfirst($lesson->type) }}
                                                                        </span>
                                                                    </td>
                                                                    <td>{{ $lesson->duration }} min</td>
                                                                    <td>
                                                                        @if($lesson->is_published)
                                                                            <span class="badge bg-success">Publié</span>
                                                                        @else
                                                                            <span class="badge bg-secondary">Brouillon</span>
                                                                        @endif
                                                                    </td>
                                                                    <td>
                                                                        @if($lesson->is_preview)
                                                                            <span class="badge bg-info">Aperçu</span>
                                                                        @else
                                                                            <span class="text-muted">-</span>
                                                                        @endif
                                                                    </td>
                                                                    <td>
                                                                        <div class="btn-group btn-group-sm" role="group">
                                                                            <a href="{{ route('courses.lesson', ['course' => $course->slug, 'lesson' => $lesson->id]) }}" 
                                                                               class="btn btn-outline-primary" 
                                                                               title="Voir la leçon" 
                                                                               target="_blank">
                                                                                <i class="fas fa-eye"></i>
                                                                            </a>
                                                                            <a href="{{ route('admin.lessons.edit', $lesson) }}" 
                                                                               class="btn btn-outline-warning" 
                                                                               title="Modifier">
                                                                                <i class="fas fa-edit"></i>
                                                                            </a>
                                                                            <button type="button" 
                                                                                    class="btn btn-outline-danger" 
                                                                                    title="Supprimer"
                                                                                    onclick="deleteLesson({{ $lesson->id }})">
                                                                                <i class="fas fa-trash"></i>
                                                                            </button>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @else
                                                <div class="text-center py-4">
                                                    <i class="fas fa-book-open fa-3x text-muted mb-3"></i>
                                                    <p class="text-muted">Aucune leçon dans cette section</p>
                                                    <a href="{{ route('admin.courses.lessons.create', $course) }}" class="btn btn-primary">
                                                        <i class="fas fa-plus me-1"></i>Ajouter une leçon
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-book-open fa-4x text-muted mb-4"></i>
                            <h5 class="text-muted">Aucune section trouvée</h5>
                            <p class="text-muted">Ce cours n'a pas encore de sections. Commencez par créer des sections et des leçons.</p>
                            <a href="{{ route('admin.courses.edit', $course) }}" class="btn btn-primary">
                                <i class="fas fa-edit me-1"></i>Modifier le cours
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmation de suppression -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmer la suppression</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir supprimer cette leçon ? Cette action est irréversible.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Supprimer</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let lessonIdToDelete = null;

    function deleteLesson(lessonId) {
        lessonIdToDelete = lessonId;
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }

    document.getElementById('confirmDelete').addEventListener('click', function() {
        if (lessonIdToDelete) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/lessons/${lessonIdToDelete}`;
            
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
    });
</script>
@endpush
@endsection


