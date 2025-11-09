@php
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;

    $user = $user ?? auth()->user();
    if (!$user) {
        return;
    }
    $dashboardBrand = 'Espace étudiant';
    $dashboardReturnRoute = route('home');
    $dashboardNavItems = [
        [
            'label' => 'Tableau de bord',
            'icon' => 'fas fa-chart-pie',
            'route' => 'student.dashboard',
        ],
        [
            'label' => 'Mes cours',
            'icon' => 'fas fa-graduation-cap',
            'route' => 'student.courses',
            'active' => ['student.courses', 'student.courses.*']
        ],
        [
            'label' => 'Certificats',
            'icon' => 'fas fa-certificate',
            'route' => 'student.certificates',
        ],
        [
            'label' => 'Commandes',
            'icon' => 'fas fa-shopping-bag',
            'route' => 'orders.index',
            'active' => ['orders.index', 'orders.show']
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
