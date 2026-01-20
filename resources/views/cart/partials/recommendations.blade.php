@if($recommendedCourses->count() > 0)
<div class="row g-3">
    @foreach($recommendedCourses as $course)
    <div class="col-12 col-sm-6 col-md-6 col-lg-3">
        <x-contenu-card-standard :course="$course" />
    </div>
    @endforeach
</div>
@else
<div class="text-center py-4">
    <p class="text-muted">Aucune recommandation disponible pour le moment.</p>
</div>
@endif
