@php
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;

    $instructor = $instructor ?? auth()->user();
    if (!$instructor) {
        return;
    }
    $dashboardBrand = 'Espace formateur';
    $dashboardReturnRoute = route('home');
    $dashboardNavItems = [
        [
            'label' => 'Tableau de bord',
            'icon' => 'fas fa-chart-pie',
            'route' => 'instructor.dashboard',
        ],
        [
            'label' => 'Mes cours',
            'icon' => 'fas fa-chalkboard-teacher',
            'route' => 'instructor.courses.list',
            'active' => ['instructor.courses.list', 'instructor.courses.index', 'instructor.courses.create', 'instructor.courses.edit']
        ],
        [
            'label' => 'Mes étudiants',
            'icon' => 'fas fa-users',
            'route' => 'instructor.students',
        ],
        [
            'label' => 'Analytics',
            'icon' => 'fas fa-chart-line',
            'route' => 'instructor.analytics',
        ],
        [
            'label' => 'Notifications',
            'icon' => 'fas fa-bell',
            'route' => 'notifications.index',
            'active' => ['notifications.*']
        ],
        [
            'label' => 'Profil',
            'icon' => 'fas fa-user-circle',
            'route' => 'profile.redirect',
        ],
    ];
@endphp
