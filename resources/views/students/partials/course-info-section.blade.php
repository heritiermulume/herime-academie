<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-primary text-white py-3">
        <h5 class="mb-0 fw-bold">Informations du cours</h5>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <h6 class="fw-bold">Formateur</h6>
            <div class="d-flex align-items-center">
                <img src="{{ $course->instructor->avatar ? $course->instructor->avatar : 'https://ui-avatars.com/api/?name=' . urlencode($course->instructor->name) . '&background=003366&color=fff' }}"
                     alt="{{ $course->instructor->name }}" class="rounded-circle me-2" width="30" height="30">
                <span>{{ $course->instructor->name }}</span>
            </div>
        </div>
        <div class="mb-3">
            <h6 class="fw-bold">Progression</h6>
            <div class="progress mb-2" style="height: 8px;">
                <div class="progress-bar bg-primary" role="progressbar"
                     style="width: {{ $enrollment->progress }}%"
                     aria-valuenow="{{ $enrollment->progress }}"
                     aria-valuemin="0" aria-valuemax="100"></div>
            </div>
            <small class="text-muted">{{ $enrollment->progress }}% terminé</small>
        </div>
        <div class="mb-3">
            <h6 class="fw-bold">Temps restant</h6>
            <small class="text-muted time-remaining-placeholder">Calcul en cours...</small>
        </div>
        <div class="mb-0">
            <h6 class="fw-bold">Leçons terminées</h6>
            <small class="text-muted">
                {{ count($enrollment->completed_lessons ?? []) }} / {{ $course->lessons_count }}
            </small>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-light py-3">
        <h5 class="mb-0 fw-bold">Mes notes</h5>
    </div>
    <div class="card-body">
        <textarea class="form-control" rows="4" placeholder="Prenez des notes sur cette leçon..."></textarea>
        <button class="btn btn-primary btn-sm mt-2">
            <i class="fas fa-save me-1"></i>Sauvegarder
        </button>
    </div>
</div>

