<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\Category;
use App\Models\Order;
use App\Models\Enrollment;
use App\Models\Announcement;
use App\Models\Partner;
use App\Models\Testimonial;
use App\Models\Payment;
use App\Models\Review;
use App\Models\Setting;
use App\Models\CourseDownload;
use App\Models\ProviderApplication;
use App\Models\ProviderPayout;
use App\Models\Visitor;
use App\Models\Certificate;
use App\Models\LessonProgress;
use App\Models\LessonNote;
use App\Models\LessonDiscussion;
use App\Models\DiscussionLike;
use App\Traits\DatabaseCompatibility;
use App\Traits\HandlesBulkActions;
use App\Services\FileUploadService;
use App\Helpers\FileHelper;
use App\Notifications\AnnouncementPublished;
use App\Notifications\CategoryCreatedNotification;
use App\Notifications\CourseModerationNotification;
use App\Notifications\CoursePublishedNotification;
use App\Notifications\ProviderApplicationStatusUpdated;
use App\Mail\CustomAnnouncementMail;
use App\Models\SentEmail;
use App\Models\ScheduledEmail;
use App\Models\SentWhatsAppMessage;
use App\Models\ContactMessage;
use App\Notifications\EmailSentNotification;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Queue;
use Illuminate\Bus\Queueable;
use App\Jobs\SendEmailJob;
use App\Jobs\SendWhatsAppJob;

class AdminController extends Controller
{
    use DatabaseCompatibility, HandlesBulkActions;

    protected $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    public function dashboard()
    {
        // Calculer les revenus des contenus internes
        // IMPORTANT: Les revenus externes sont les revenus provenant des contenus qui appartiennent 
        // à un utilisateur qui a UNIQUEMENT le rôle de "provider"
        // Les revenus internes sont les revenus des contenus créés par des utilisateurs 
        // qui ne sont PAS uniquement prestataires (admins, super_users, etc.)
        // IMPORTANT: Inclure TOUTES les commandes, même celles avec des contenus supprimés
        // Les commandes avec contenus supprimés sont comptées comme revenus internes
        $internalRevenue = Order::withTrashed()->whereIn('status', ['paid', 'completed'])
            ->whereDoesntHave('orderItems', function($query) {
                // Exclure uniquement les commandes avec des contenus existants de providers externes
                // Un prestataire externe = utilisateur avec UNIQUEMENT le rôle "provider"
                $query->whereHas('content', function($q) {
                    $q->whereHas('provider', function($providerQuery) {
                        $providerQuery->where('role', 'provider');
                    });
                });
            })
            ->get()
            ->sum(function ($o) { return $o->total_amount ?? $o->total ?? 0; });
        
        // Calculer les revenus externes (contenus créés par des utilisateurs avec uniquement le rôle provider)
        $externalRevenue = Order::withTrashed()->whereIn('status', ['paid', 'completed'])
            ->whereHas('orderItems', function($query) {
                $query->whereHas('content', function($q) {
                    $q->whereHas('provider', function($providerQuery) {
                        $providerQuery->where('role', 'provider');
                    });
                });
            })
            ->get()
            ->sum(function ($o) { return $o->total_amount ?? $o->total ?? 0; });

        // Calculer les commissions retenues sur les prestataires externes
        $commissionsRevenue = ProviderPayout::withTrashed()->where('status', 'completed')
            ->sum('commission_amount');

        // Calculer le revenu total : Revenus internes + Commissions retenues sur les revenus externes
        // Les revenus externes sont les revenus des contenus créés par des providers
        // On ne compte que les commissions retenues, pas le montant total
        $totalRevenue = $internalRevenue + $commissionsRevenue;

        // Revenus des prestataires externes (montants payés aux prestataires, avant commission)
        $externalProviderPayouts = ProviderPayout::withTrashed()->where('status', 'completed')
            ->sum('amount');

        // Statistiques générales
        $stats = [
            'total_users' => User::count(),
            'total_customers' => User::customers()->count(),
            'total_providers' => User::providers()->count(),
            'total_courses' => Course::count(),
            'published_courses' => Course::published()->count(),
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'paid_orders' => Order::where('status', 'paid')->count(),
            'total_revenue' => $totalRevenue, // Revenu total (internes + commissions)
            'internal_revenue' => $internalRevenue, // Revenus des contenus internes (non-providers)
            'external_revenue' => $externalRevenue, // Revenus des contenus créés par des providers
            'commissions_revenue' => $commissionsRevenue, // Commissions retenues sur les revenus externes
            'external_payouts' => $externalProviderPayouts, // Montants payés aux prestataires externes
            'total_enrollments' => Enrollment::count(),
        ];

        // Revenus internes par mois (6 derniers mois)
        // IMPORTANT: Inclure TOUTES les commandes, même celles avec des contenus supprimés
        // IMPORTANT: Utiliser withTrashed() pour inclure les enregistrements soft-deleted
        $internalRevenueByMonth = Order::withTrashed()
            ->whereIn('status', ['paid', 'completed'])
            ->where('created_at', '>=', now()->subMonths(6))
            ->whereDoesntHave('orderItems', function($query) {
                // Exclure uniquement les commandes avec des contenus existants de providers externes
                $query->whereHas('content', function($q) {
                    $q->whereHas('provider', function($providerQuery) {
                        $providerQuery->where('role', 'provider');
                    });
                });
            })
            ->selectRaw($this->buildDateFormatSelect('created_at', '%Y-%m', 'month') . ', SUM(COALESCE(total_amount, total, 0)) as revenue')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(function ($item) {
                $item->month = $item->month ?? '';
                return $item;
            });

        // Commissions par mois (6 derniers mois)
        // IMPORTANT: Utiliser withTrashed() pour inclure les enregistrements soft-deleted
        $commissionsByMonth = ProviderPayout::withTrashed()
            ->where('status', 'completed')
            ->where('created_at', '>=', now()->subMonths(6))
            ->selectRaw($this->buildDateFormatSelect('created_at', '%Y-%m', 'month') . ', SUM(commission_amount) as revenue')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(function ($item) {
                $item->month = $item->month ?? '';
                return $item;
            });

        // Revenus totaux par mois : Revenus internes + Commissions
        $revenueByMonth = $this->combineRevenueByPeriod($internalRevenueByMonth, $commissionsByMonth, 'month');

        // Préparer les labels et valeurs pour le graphique de revenus
        $revenueLabels = $revenueByMonth->pluck('month')->toArray();
        $revenueValues = $revenueByMonth->pluck('revenue')->toArray();

        // Revenus par catégorie - Utilise le même calcul que /admin/orders
        $revenueByCategory = \App\Models\OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('contents', 'order_items.content_id', '=', 'contents.id')
            ->join('categories', 'contents.category_id', '=', 'categories.id')
            ->whereIn('orders.status', ['paid', 'completed'])
            ->select('categories.id', 'categories.name')
            ->selectRaw('SUM(order_items.total) as revenue')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('revenue')
            ->get();

        // Préparer les labels et valeurs pour le graphique de revenus par catégorie
        $revenueByCategoryLabels = $revenueByCategory->pluck('name')->toArray();
        $revenueByCategoryValues = $revenueByCategory->pluck('revenue')->toArray();

        // Cours les plus populaires
        $popularCourses = Course::published()
            ->with(['provider', 'category'])
            ->withCount('enrollments')
            ->orderBy('enrollments_count', 'desc')
            ->limit(5)
            ->get();

        // Inscriptions récentes
        $recentEnrollments = Enrollment::with(['user', 'course.provider'])
            ->latest()
            ->limit(10)
            ->get();

        // Commandes récentes
        $recentOrders = Order::with(['user', 'orderItems.course'])
            ->latest()
            ->limit(10)
            ->get();

        $baseCurrency = Setting::getBaseCurrency();
        $currencyCode = $baseCurrency['code'] ?? 'USD';
        
        return view('admin.dashboard', compact(
            'stats', 
            'revenueByMonth',
            'revenueByCategory',
            'revenueLabels',
            'revenueValues',
            'revenueByCategoryLabels',
            'revenueByCategoryValues',
            'popularCourses', 
            'recentEnrollments', 
            'recentOrders',
            'baseCurrency',
            'currencyCode'
        ));
    }

    public function analytics()
    {
        // Calculer les revenus des contenus internes
        // IMPORTANT: Les revenus externes sont les revenus provenant des contenus qui appartiennent 
        // à un utilisateur qui a UNIQUEMENT le rôle de "provider"
        // Les revenus internes sont les revenus des contenus créés par des utilisateurs 
        // qui ne sont PAS uniquement prestataires (admins, super_users, etc.)
        // IMPORTANT: Inclure TOUTES les commandes, même celles avec des contenus supprimés
        // Les commandes avec contenus supprimés sont comptées comme revenus internes
        $internalRevenue = Order::withTrashed()->whereIn('status', ['paid', 'completed'])
            ->whereDoesntHave('orderItems', function($query) {
                // Exclure uniquement les commandes avec des contenus existants de providers externes
                // Un prestataire externe = utilisateur avec UNIQUEMENT le rôle "provider"
                $query->whereHas('content', function($q) {
                    $q->whereHas('provider', function($providerQuery) {
                        $providerQuery->where('role', 'provider');
                    });
                });
            })
            ->get()
            ->sum(function ($o) { return $o->total_amount ?? $o->total ?? 0; });
        
        // Calculer les revenus externes (contenus créés par des utilisateurs avec uniquement le rôle provider)
        $externalRevenue = Order::withTrashed()->whereIn('status', ['paid', 'completed'])
            ->whereHas('orderItems', function($query) {
                $query->whereHas('content', function($q) {
                    $q->whereHas('provider', function($providerQuery) {
                        $providerQuery->where('role', 'provider');
                    });
                });
            })
            ->get()
            ->sum(function ($o) { return $o->total_amount ?? $o->total ?? 0; });

        // Calculer les commissions retenues sur les prestataires externes
        $commissionsRevenue = ProviderPayout::withTrashed()->where('status', 'completed')
            ->sum('commission_amount');

        // Calculer le revenu total : Revenus internes + Commissions retenues sur les revenus externes
        // Les revenus externes sont les revenus des contenus créés par des providers
        // On ne compte que les commissions retenues, pas le montant total
        $totalRevenue = $internalRevenue + $commissionsRevenue;

        // Revenus des prestataires externes (montants payés aux prestataires, avant commission)
        $externalProviderPayouts = ProviderPayout::withTrashed()->where('status', 'completed')
            ->sum('amount');

        // Statistiques générales
        $stats = [
            'total_users' => User::count(),
            'total_courses' => Course::count(),
            'total_orders' => Order::count(),
            'total_revenue' => $totalRevenue, // Revenu total (internes + commissions)
            'internal_revenue' => $internalRevenue, // Revenus des contenus internes (non-providers)
            'external_revenue' => $externalRevenue, // Revenus des contenus créés par des providers
            'commissions_revenue' => $commissionsRevenue, // Commissions retenues sur les revenus externes
            'external_payouts' => $externalProviderPayouts, // Montants payés aux prestataires externes
            'total_enrollments' => Enrollment::count(),
            'total_visits' => Visitor::count(), // Total de toutes les visites
            'total_visitors' => Visitor::count(), // Alias pour compatibilité (total des visites)
            'unique_visitors' => (int) Visitor::selectRaw('COUNT(DISTINCT ip_address) as count')->value('count') ?? 0, // Visiteurs uniques par IP
            'visitors_today' => Visitor::today()->count(), // Total des visites aujourd'hui
            'unique_visitors_today' => (int) Visitor::today()->selectRaw('COUNT(DISTINCT ip_address) as count')->value('count') ?? 0, // Visiteurs uniques aujourd'hui
            'visitors_this_week' => Visitor::thisWeek()->count(),
            'visitors_this_month' => Visitor::thisMonth()->count(),
        ];

        // Revenus internes par mois (6 derniers mois) - Utilise le même calcul que /admin/orders
        // IMPORTANT: Inclure TOUTES les commandes, même celles avec des contenus supprimés
        $internalRevenueByMonth = Order::withTrashed()->whereIn('status', ['paid', 'completed'])
            ->where('created_at', '>=', now()->subMonths(6))
            ->whereDoesntHave('orderItems', function($query) {
                // Exclure uniquement les commandes avec des contenus existants de providers externes
                $query->whereHas('content', function($q) {
                    $q->whereHas('provider', function($providerQuery) {
                        $providerQuery->where('role', 'provider');
                    });
                });
            })
            ->selectRaw($this->buildDateFormatSelect('created_at', '%Y-%m', 'month') . ', SUM(COALESCE(total_amount, total, 0)) as revenue')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(function ($item) {
                $item->month = $item->month ?? '';
                return $item;
            });

        // Commissions par mois (6 derniers mois)
        $commissionsByMonth = ProviderPayout::withTrashed()->where('status', 'completed')
            ->where('created_at', '>=', now()->subMonths(6))
            ->selectRaw($this->buildDateFormatSelect('created_at', '%Y-%m', 'month') . ', SUM(commission_amount) as revenue')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(function ($item) {
                $item->month = $item->month ?? '';
                return $item;
            });

        // Revenus totaux par mois : Revenus internes + Commissions
        $revenueByMonth = $this->combineRevenueByPeriod($internalRevenueByMonth, $commissionsByMonth, 'month');

        // Revenus par jour (30 derniers jours) - Sera calculé en combinant revenus internes + commissions

        // Revenus internes par jour (30 derniers jours) - Utilise le même calcul que /admin/orders
        // IMPORTANT: Inclure TOUTES les commandes, même celles avec des contenus supprimés
        $internalRevenueByDay = Order::withTrashed()->whereIn('status', ['paid', 'completed'])
            ->where('created_at', '>=', now()->subDays(30))
            ->whereDoesntHave('orderItems', function($query) {
                // Exclure uniquement les commandes avec des contenus existants de providers externes
                $query->whereHas('content', function($q) {
                    $q->whereHas('provider', function($providerQuery) {
                        $providerQuery->where('role', 'provider');
                    });
                });
            })
            ->selectRaw($this->buildDateFormatSelect('created_at', '%Y-%m-%d', 'date') . ', SUM(COALESCE(total_amount, total, 0)) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                $item->date = $item->date ?? '';
                return $item;
            });

        // Commissions par jour (30 derniers jours)
        $commissionsByDay = ProviderPayout::withTrashed()->where('status', 'completed')
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw($this->buildDateFormatSelect('created_at', '%Y-%m-%d', 'date') . ', SUM(commission_amount) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                $item->date = $item->date ?? '';
                return $item;
            });

        // Revenus totaux par jour : Revenus internes + Commissions
        $revenueByDay = $this->combineRevenueByPeriod($internalRevenueByDay, $commissionsByDay, 'date');

        // Revenus par semaine (12 dernières semaines) - Sera calculé en combinant revenus internes + commissions
        $driver = DB::getDriverName();

        // Revenus internes par semaine (12 dernières semaines) - Utilise le même calcul que /admin/orders
        // Calcul basé sur le montant total des commandes qui contiennent uniquement des items internes
        // IMPORTANT: Inclure TOUTES les commandes, même celles avec des contenus supprimés
        if ($driver === 'pgsql') {
            $internalRevenueByWeek = Order::withTrashed()->whereIn('status', ['paid', 'completed'])
                ->where('created_at', '>=', now()->subWeeks(12))
                ->whereDoesntHave('orderItems', function($query) {
                    // Exclure uniquement les commandes avec des contenus existants de providers externes
                    $query->whereHas('content', function($q) {
                        $q->whereHas('provider', function($providerQuery) {
                            $providerQuery->where('role', 'provider');
                        });
                    });
                })
                ->selectRaw("to_char(created_at, 'IYYY-IW') as week, SUM(COALESCE(total_amount, total, 0)) as revenue")
                ->groupBy('week')
                ->orderBy('week')
                ->get()
                ->map(function ($item) {
                    $item->week = $item->week ?? '';
                    return $item;
                });
        } else {
            $internalRevenueByWeek = Order::withTrashed()->whereIn('status', ['paid', 'completed'])
                ->where('created_at', '>=', now()->subWeeks(12))
                ->whereDoesntHave('orderItems', function($query) {
                    // Exclure uniquement les commandes avec des contenus existants de providers externes
                    $query->whereHas('content', function($q) {
                        $q->whereHas('provider', function($providerQuery) {
                            $providerQuery->where('role', 'provider');
                        });
                    });
                })
                ->selectRaw($this->buildDateFormatSelect('created_at', '%Y-%u', 'week') . ', SUM(COALESCE(total_amount, total, 0)) as revenue')
                ->groupBy('week')
                ->orderBy('week')
                ->get()
                ->map(function ($item) {
                    $item->week = $item->week ?? '';
                    return $item;
                });
        }

        // Commissions par semaine (12 dernières semaines)
        if ($driver === 'pgsql') {
            $commissionsByWeek = ProviderPayout::withTrashed()->where('status', 'completed')
                ->where('created_at', '>=', now()->subWeeks(12))
                ->selectRaw("to_char(created_at, 'IYYY-IW') as week, SUM(commission_amount) as revenue")
                ->groupBy('week')
                ->orderBy('week')
                ->get()
                ->map(function ($item) {
                    $item->week = $item->week ?? '';
                    return $item;
                });
        } else {
            $commissionsByWeek = ProviderPayout::withTrashed()->where('status', 'completed')
                ->where('created_at', '>=', now()->subWeeks(12))
                ->selectRaw($this->buildDateFormatSelect('created_at', '%Y-%u', 'week') . ', SUM(commission_amount) as revenue')
                ->groupBy('week')
                ->orderBy('week')
                ->get()
                ->map(function ($item) {
                    $item->week = $item->week ?? '';
                    return $item;
                });
        }

        // Revenus totaux par semaine : Revenus internes + Commissions
        $revenueByWeek = $this->combineRevenueByPeriod($internalRevenueByWeek, $commissionsByWeek, 'week');

        // Revenus par catégorie - Utilise le même calcul que /admin/orders
        $revenueByCategory = \App\Models\OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('contents', 'order_items.content_id', '=', 'contents.id')
            ->join('categories', 'contents.category_id', '=', 'categories.id')
            ->whereIn('orders.status', ['paid', 'completed'])
            ->select('categories.id', 'categories.name')
            ->selectRaw('SUM(order_items.total) as revenue')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('revenue')
            ->get();

        // Revenus par cours (top 10) - Utilise le même calcul que /admin/orders
        $revenueByCourse = \App\Models\OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('contents', 'order_items.content_id', '=', 'contents.id')
            ->whereIn('orders.status', ['paid', 'completed'])
            ->select('contents.id', 'contents.title')
            ->selectRaw('SUM(order_items.total) as revenue')
            ->groupBy('contents.id', 'contents.title')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get();

        // Revenus par prestataire (top 10) - Utilise le même calcul que /admin/orders
        $revenueByProvider = \App\Models\OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('contents', 'order_items.content_id', '=', 'contents.id')
            ->join('users', 'contents.provider_id', '=', 'users.id')
            ->whereIn('orders.status', ['paid', 'completed'])
            ->select('users.id', 'users.name')
            ->selectRaw('SUM(order_items.total) as revenue')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get();

        // Revenus par année (pour le filtre) - Sera calculé en combinant revenus internes + commissions

        // Revenus internes par année - Utilise le même calcul que /admin/orders
        // IMPORTANT: Inclure TOUTES les commandes, même celles avec des contenus supprimés
        $internalRevenueByYear = Order::withTrashed()->whereIn('status', ['paid', 'completed'])
            ->whereDoesntHave('orderItems', function($query) {
                // Exclure uniquement les commandes avec des contenus existants de providers externes
                $query->whereHas('content', function($q) {
                    $q->whereHas('provider', function($providerQuery) {
                        $providerQuery->where('role', 'provider');
                    });
                });
            })
            ->selectRaw($this->buildDateFormatSelect('created_at', '%Y', 'year') . ', SUM(COALESCE(total_amount, total, 0)) as revenue')
            ->groupBy('year')
            ->orderBy('year')
            ->get()
            ->map(function ($item) {
                $item->year = $item->year ?? '';
                return $item;
            });

        // Commissions par année
        $commissionsByYear = ProviderPayout::withTrashed()->where('status', 'completed')
            ->selectRaw($this->buildDateFormatSelect('created_at', '%Y', 'year') . ', SUM(commission_amount) as revenue')
            ->groupBy('year')
            ->orderBy('year')
            ->get()
            ->map(function ($item) {
                $item->year = $item->year ?? '';
                return $item;
            });

        // Revenus totaux par année : Revenus internes + Commissions
        $revenueByYear = $this->combineRevenueByPeriod($internalRevenueByYear, $commissionsByYear, 'year');

        // Analytics détaillées
        $courseStats = Course::selectRaw('
            COUNT(*) as total_courses,
            SUM(CASE WHEN is_published = 1 THEN 1 ELSE 0 END) as published_courses
        ')->first();
        
        // Calculer les statistiques dynamiquement
        $totalStudents = Enrollment::count();
        $averageRating = Review::avg('rating') ?? 0;
        
        $courseStats->total_customers = $totalStudents;
        $courseStats->average_rating = $averageRating;

        $userGrowth = User::selectRaw($this->buildDateFormatSelect('created_at', '%Y-%m', 'month') . ', COUNT(*) as count')
        ->where('created_at', '>=', now()->subMonths(12))
        ->groupBy('month')
        ->orderBy('month')
        ->get()
        ->map(function ($item) {
            // S'assurer que le mois est au format YYYY-MM
            $item->month = $item->month ?? '';
            return $item;
        });

        $categoryStats = Category::withCount('courses')
            ->orderBy('courses_count', 'desc')
            ->get();

        $providerStats = User::providers()
            ->withCount('contents')
            ->withCount(['contents as total_customers' => function($query) {
                $query->withCount('enrollments');
            }])
            ->orderBy('contents_count', 'desc')
            ->limit(10)
            ->get();

        // Cours les plus populaires (limité à 8 pour l'affichage horizontal)
        $popularCourses = Course::published()
            ->with(['provider', 'category'])
            ->withCount('enrollments')
            ->orderBy('enrollments_count', 'desc')
            ->limit(8)
            ->get();

        // Paiements: répartition par statut et par méthode
        $paymentsByStatus = Payment::select('status')
            ->selectRaw('COUNT(*) as count, SUM(amount) as total')
            ->groupBy('status')
            ->get();

        $paymentsByMethod = Payment::select('payment_method')
            ->selectRaw('COUNT(*) as count, SUM(amount) as total')
            ->groupBy('payment_method')
            ->get();

        // Statistiques des visiteurs
        $visitorStats = [
            'by_device' => Visitor::select('device_type')
                ->selectRaw('COUNT(*) as count')
                ->groupBy('device_type')
                ->get(),
            'by_browser' => Visitor::select('browser')
                ->selectRaw('COUNT(*) as count')
                ->whereNotNull('browser')
                ->groupBy('browser')
                ->orderByDesc('count')
                ->limit(10)
                ->get(),
            'by_os' => Visitor::select('os')
                ->selectRaw('COUNT(*) as count')
                ->whereNotNull('os')
                ->groupBy('os')
                ->orderByDesc('count')
                ->limit(10)
                ->get(),
            'visitors_by_day' => Visitor::selectRaw($this->buildDateFormatSelect('visited_at', '%Y-%m-%d', 'date') . ', COUNT(*) as count')
                ->where('visited_at', '>=', now()->subDays(30))
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->map(function ($item) {
                    // S'assurer que la date est au format YYYY-MM-DD
                    $item->date = $item->date ?? '';
                    return $item;
                }),
            'unique_visitors_by_day' => Visitor::selectRaw($this->buildDateFormatSelect('visited_at', '%Y-%m-%d', 'date') . ', COUNT(DISTINCT ip_address) as count')
                ->where('visited_at', '>=', now()->subDays(30))
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->map(function ($item) {
                    // S'assurer que la date est au format YYYY-MM-DD
                    $item->date = $item->date ?? '';
                    return $item;
                }),
            'by_country' => Visitor::select('country')
                ->selectRaw('COUNT(*) as count')
                ->whereNotNull('country')
                ->groupBy('country')
                ->orderByDesc('count')
                ->limit(10)
                ->get(),
            'by_city' => Visitor::select('city', 'country')
                ->selectRaw('COUNT(*) as count')
                ->whereNotNull('city')
                ->groupBy('city', 'country')
                ->orderByDesc('count')
                ->limit(10)
                ->get(),
        ];

        $baseCurrency = Setting::getBaseCurrency();
        return view('admin.analytics', compact(
            'stats',
            'revenueByMonth',
            'internalRevenueByMonth',
            'commissionsByMonth',
            'revenueByDay',
            'internalRevenueByDay',
            'commissionsByDay',
            'revenueByWeek',
            'internalRevenueByWeek',
            'commissionsByWeek',
            'revenueByYear',
            'internalRevenueByYear',
            'commissionsByYear',
            'revenueByCategory',
            'revenueByCourse',
            'revenueByProvider',
            'courseStats',
            'userGrowth',
            'categoryStats',
            'providerStats',
            'popularCourses',
            'paymentsByStatus',
            'paymentsByMethod',
            'visitorStats',
            'baseCurrency'
        ));
    }

    public function getRevenueData(Request $request)
    {
        $period = $request->input('period', 'month');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Utilise le même calcul que /admin/orders pour uniformiser
        $query = Order::whereIn('status', ['paid', 'completed']);

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('created_at', '<=', $endDate . ' 23:59:59');
        }

        $data = [];
        $labels = [];

        switch($period) {
            case 'day':
                $results = $query->selectRaw($this->buildDateFormatSelect('created_at', '%Y-%m-%d', 'date') . ', SUM(COALESCE(total_amount, total, 0)) as revenue')
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get()
                    ->map(function ($item) {
                        $item->date = $item->date ?? '';
                        return $item;
                    });
                $data = $results->toArray();
                break;
            case 'week':
                $driver = DB::getDriverName();
                if ($driver === 'pgsql') {
                    $results = $query->selectRaw("to_char(created_at, 'IYYY-IW') as week, SUM(COALESCE(total_amount, total, 0)) as revenue")
                        ->groupBy('week')
                        ->orderBy('week')
                        ->get();
                } else {
                    $results = $query->selectRaw($this->buildDateFormatSelect('created_at', '%Y-%u', 'week') . ', SUM(COALESCE(total_amount, total, 0)) as revenue')
                        ->groupBy('week')
                        ->orderBy('week')
                        ->get();
                }
                $data = $results->map(function ($item) {
                    $item->week = $item->week ?? '';
                    return $item;
                })->toArray();
                break;
            case 'month':
                $results = $query->selectRaw($this->buildDateFormatSelect('created_at', '%Y-%m', 'month') . ', SUM(COALESCE(total_amount, total, 0)) as revenue')
                    ->groupBy('month')
                    ->orderBy('month')
                    ->get()
                    ->map(function ($item) {
                        $item->month = $item->month ?? '';
                        return $item;
                    });
                $data = $results->toArray();
                break;
            case 'year':
                $results = $query->selectRaw($this->buildDateFormatSelect('created_at', '%Y', 'year') . ', SUM(COALESCE(total_amount, total, 0)) as revenue')
                    ->groupBy('year')
                    ->orderBy('year')
                    ->get()
                    ->map(function ($item) {
                        $item->year = $item->year ?? '';
                        return $item;
                    });
                $data = $results->toArray();
                break;
        }

        return response()->json(['data' => $data]);
    }

    public function getRevenueByCategory(Request $request)
    {
        $days = $request->input('days', 'all');
        
        // Utilise le même calcul que /admin/orders pour uniformiser
        $query = \App\Models\OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('contents', 'order_items.content_id', '=', 'contents.id')
            ->join('categories', 'contents.category_id', '=', 'categories.id')
            ->whereIn('orders.status', ['paid', 'completed']);

        if ($days !== 'all') {
            $query->where('orders.created_at', '>=', now()->subDays((int)$days));
        }

        $data = $query->select('categories.id', 'categories.name')
            ->selectRaw('SUM(order_items.total) as revenue')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('revenue')
            ->get();

        return response()->json(['data' => $data]);
    }

    public function getRevenueByCourse(Request $request)
    {
        $days = $request->input('days', 'all');
        
        // Utilise le même calcul que /admin/orders pour uniformiser
        $query = \App\Models\OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('contents', 'order_items.content_id', '=', 'contents.id')
            ->whereIn('orders.status', ['paid', 'completed']);

        if ($days !== 'all') {
            $query->where('orders.created_at', '>=', now()->subDays((int)$days));
        }

        $data = $query->select('contents.id', 'contents.title')
            ->selectRaw('SUM(order_items.total) as revenue')
            ->groupBy('contents.id', 'contents.title')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get();

        return response()->json(['data' => $data]);
    }

    public function getRevenueByInstructor(Request $request)
    {
        $days = $request->input('days', 'all');
        
        // Utilise le même calcul que /admin/orders pour uniformiser
        $query = \App\Models\OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('contents', 'order_items.content_id', '=', 'contents.id')
            ->join('users', 'contents.provider_id', '=', 'users.id')
            ->whereIn('orders.status', ['paid', 'completed']);

        if ($days !== 'all') {
            $query->where('orders.created_at', '>=', now()->subDays((int)$days));
        }

        $data = $query->select('users.id', 'users.name')
            ->selectRaw('SUM(order_items.total) as revenue')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get();

        return response()->json(['data' => $data]);
    }

    // Gestion des utilisateurs
    public function users(Request $request)
    {
        $query = User::withCount(['courses', 'enrollments']);

        // Recherche par nom ou email
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filtre par rôle
        if ($request->filled('role')) {
            $query->where('role', $request->get('role'));
        }

        // Filtre par statut
        if ($request->filled('status')) {
            if ($request->get('status') === 'active') {
                $query->where('is_active', true);
            } elseif ($request->get('status') === 'inactive') {
                $query->where('is_active', false);
            } elseif ($request->get('status') === 'verified') {
                $query->where('is_verified', true);
            }
        }

        // Tri
        $sortBy = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        
        if (in_array($sortBy, ['name', 'email', 'role', 'created_at', 'last_login_at'])) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->latest();
        }

        $users = $query->paginate(15)->withQueryString();

        // Statistiques pour les filtres
        $stats = [
            'total' => User::count(),
            'active' => User::where('is_active', true)->count(),
            'inactive' => User::where('is_active', false)->count(),
            'verified' => User::where('is_verified', true)->count(),
            'customers' => User::where('role', 'customer')->count(),
            'providers' => User::where('role', 'provider')->count(),
            'admins' => User::whereIn('role', ['admin', 'super_user'])->count(),
            'affiliates' => User::where('role', 'affiliate')->count(),
        ];

        return view('admin.users.index', compact('users', 'stats'));
    }

    public function editUser(User $user)
    {
        // Récupérer les données Moneroo (pays et providers)
        $monerooData = $this->getMonerooConfiguration();
        
        return view('admin.users.edit', compact('user', 'monerooData'));
    }

    /**
     * Récupérer la configuration Moneroo (pays et providers)
     * Selon la documentation: https://docs.moneroo.io/fr/payouts/methodes-disponibles
     */
    private function getMonerooConfiguration(): array
    {
        try {
            $baseUrl = rtrim(config('services.moneroo.base_url', 'https://api.moneroo.io/v1'), '/');
            $apiKey = config('services.moneroo.api_key');
            
            if (!$apiKey) {
                Log::warning('Moneroo API key not configured');
                return ['countries' => [], 'providers' => []];
            }

            // Utiliser l'endpoint /payouts/methods selon la documentation Moneroo
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->get("{$baseUrl}/payouts/methods");

            if ($response->successful()) {
                $responseData = $response->json();
                // Format Moneroo: { "success": true, "data": {...} }
                $data = $responseData['data'] ?? $responseData;
                
                Log::info('Moneroo configuration retrieved', [
                    'has_data' => !empty($data),
                ]);
                
                // Extraire les pays et providers selon la structure de la réponse Moneroo
                $countries = [];
                $providers = [];
                
                // Moneroo peut retourner les méthodes différemment
                if (isset($data['methods']) && is_array($data['methods'])) {
                    foreach ($data['methods'] as $method) {
                        $countryCode = $method['country'] ?? '';
                        $providerCode = $method['payment_method'] ?? $method['provider'] ?? '';
                        $providerName = $method['name'] ?? $providerCode;
                        $currencies = $method['currencies'] ?? ($method['currency'] ? [$method['currency']] : []);
                        
                        if ($countryCode && !isset($countries[$countryCode])) {
                            $countries[$countryCode] = [
                                'code' => $countryCode,
                                'name' => $countryCode,
                                'prefix' => '',
                                'flag' => '',
                            ];
                        }
                        
                        if ($providerCode) {
                            $providers[] = [
                                'code' => $providerCode,
                                'name' => $providerName,
                                'country' => $countryCode,
                                'currencies' => $currencies,
                                'logo' => $method['logo'] ?? '',
                            ];
                        }
                    }
                    $countries = array_values($countries);
                } elseif (isset($data['countries']) && is_array($data['countries'])) {
                    foreach ($data['countries'] as $country) {
                        $countryCode = $country['country'] ?? $country['code'] ?? '';
                        $countryName = $country['displayName']['fr'] ?? $country['displayName']['en'] ?? $country['name'] ?? $countryCode;
                        
                        $countries[] = [
                            'code' => $countryCode,
                            'name' => $countryName,
                            'prefix' => $country['prefix'] ?? '',
                            'flag' => $country['flag'] ?? '',
                        ];
                        
                        if (isset($country['providers']) && is_array($country['providers'])) {
                            foreach ($country['providers'] as $provider) {
                                $providerCode = $provider['provider'] ?? $provider['payment_method'] ?? '';
                                $providerName = $provider['displayName'] ?? $provider['name'] ?? $providerCode;
                                $currencies = $provider['currencies'] ?? ($provider['currency'] ? [$provider['currency']] : []);
                                
                                $providers[] = [
                                    'code' => $providerCode,
                                    'name' => $providerName,
                                    'country' => $countryCode,
                                    'currencies' => $currencies,
                                    'logo' => $provider['logo'] ?? '',
                                ];
                            }
                        }
                    }
                }
                
                // Trier les pays par nom
                usort($countries, function($a, $b) {
                    return strcmp($a['name'], $b['name']);
                });
                
                // Trier les providers par nom
                usort($providers, function($a, $b) {
                    return strcmp($a['name'], $b['name']);
                });
                
                Log::info('Moneroo configuration processed', [
                    'countries_count' => count($countries),
                    'providers_count' => count($providers),
                ]);
                
                return [
                    'countries' => $countries,
                    'providers' => $providers,
                ];
            } else {
                Log::warning('Échec de la récupération de la configuration Moneroo', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération de la configuration Moneroo', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
        
        return ['countries' => [], 'providers' => []];
    }

    /**
     * Mettre à jour un utilisateur
     * Seuls certains champs peuvent être modifiés localement (rôle, is_active)
     * Les autres données (nom, email, photo) viennent du SSO et seront synchronisées
     */
    public function updateUser(Request $request, User $user)
    {
        // Avec le SSO, on limite les modifications locales
        // Seuls le rôle et le statut actif peuvent être modifiés localement
        // Les autres données (nom, email, photo) viennent du SSO
        
        $request->validate([
            'role' => 'required|in:customer,provider,admin,affiliate,super_user',
            'is_active' => 'boolean',
            'is_external_provider' => 'boolean',
            'moneroo_phone' => 'nullable|string|max:20',
            'moneroo_provider' => 'nullable|string|max:50',
            'moneroo_country' => 'nullable|string|size:2',
            'moneroo_currency' => 'nullable|string|size:3',
            // Note: Les autres champs (name, email, avatar) sont gérés par le SSO
        ]);

        // Mettre à jour uniquement les champs modifiables localement
        $user->update([
            'role' => $request->role,
            'is_active' => $request->has('is_active'),
            'is_external_provider' => $request->has('is_external_provider'),
            'moneroo_phone' => $request->moneroo_phone,
            'moneroo_provider' => $request->moneroo_provider,
            'moneroo_country' => $request->moneroo_country,
            'moneroo_currency' => $request->moneroo_currency,
        ]);

            return redirect()->route('admin.users')
                ->with('success', 'Utilisateur mis à jour avec succès. Les données personnelles (nom, email, photo) sont gérées via Compte Herime et seront synchronisées lors de la prochaine connexion.');
    }
    
    /**
     * Synchroniser un utilisateur avec le SSO
     * Récupère les dernières données depuis le SSO
     */
    public function syncUserFromSSO(User $user)
    {
        try {
            // Si l'utilisateur a un ID SSO, on pourrait faire une requête au SSO
            // Pour l'instant, on synchronise lors de la prochaine connexion
            // Cette méthode peut être utilisée pour forcer une synchronisation
            return redirect()->route('admin.users')
                ->with('info', 'La synchronisation se fait automatiquement lors de la connexion via Compte Herime. Les données seront mises à jour lors de la prochaine connexion de l\'utilisateur.');
        } catch (\Exception $e) {
            Log::error('SSO User Sync Error', [
                'user_id' => $user->id,
                'message' => $e->getMessage()
            ]);
            
            return redirect()->route('admin.users')
                ->with('error', 'Erreur lors de la synchronisation. Les données seront mises à jour lors de la prochaine connexion.');
        }
    }

    /**
     * Afficher la page de création d'utilisateur
     * Redirige vers le SSO car la création se fait via SSO
     */
    public function createUser()
    {
        // La création d'utilisateurs se fait via le SSO
        // Rediriger vers le SSO pour créer un utilisateur
        if (config('services.sso.enabled', true)) {
            $ssoService = app(\App\Services\SSOService::class);
            $callbackUrl = route('sso.callback', [
                'redirect' => route('admin.users')
            ]);
            $ssoRegisterUrl = $ssoService->getRegisterUrl($callbackUrl);
            
            return redirect($ssoRegisterUrl)
                ->with('info', 'La création d\'utilisateurs se fait via Compte Herime. Vous allez être redirigé vers la page d\'inscription.');
        }
        
        // Fallback si SSO désactivé (ne devrait pas arriver)
        return redirect()->route('admin.users')
            ->with('error', 'Compte Herime est requis pour créer des utilisateurs.');
    }

    /**
     * Stocker un utilisateur
     * Cette méthode ne devrait jamais être appelée car la création se fait via SSO
     */
    public function storeUser(Request $request)
    {
        // La création d'utilisateurs se fait uniquement via SSO
        return redirect()->route('admin.users')
            ->with('error', 'La création d\'utilisateurs se fait uniquement via Compte Herime. Veuillez rediriger l\'utilisateur vers Compte Herime pour créer un compte.');
    }

    public function showUser(User $user)
    {
        // Charger les inscriptions avec les cours (exclure les cours supprimés)
        $enrollments = $user->enrollments()
            ->where('status', '!=', 'cancelled')
            ->whereHas('content') // S'assurer que le cours existe toujours
            ->with(['course.provider', 'course.category', 'order'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->filter(function($enrollment) {
                // Filtrer les enrollments dont le cours n'existe plus
                return $enrollment->course !== null;
            });
        
        // Récupérer les cours achetés (via commandes payées) qui n'ont pas d'inscription
        $purchasedCourseIds = $enrollments->pluck('content_id')->filter()->all();
        $purchasedCourses = \App\Models\Order::where('user_id', $user->id)
            ->whereIn('status', ['paid', 'completed'])
            ->whereHas('orderItems', function($query) use ($purchasedCourseIds) {
                $query->whereNotIn('content_id', $purchasedCourseIds ?: [0])
                    ->whereNotNull('content_id')
                    ->whereHas('content', function($q) {
                        $q->where('is_published', true);
                    });
            })
            ->with(['orderItems.course.provider', 'orderItems.course.category'])
            ->get()
            ->flatMap(function($order) use ($purchasedCourseIds) {
                if (!$order->orderItems) {
                    return collect();
                }
                
                return $order->orderItems
                    ->filter(function($item) use ($purchasedCourseIds) {
                        // Vérifier que le cours existe et est publié
                        if (!$item->content_id || !$item->course) {
                            return false;
                        }
                        
                        try {
                            return $item->course->is_published && 
                                   !in_array($item->content_id, $purchasedCourseIds);
                        } catch (\Exception $e) {
                            \Log::warning('Erreur lors du filtrage des cours achetés', [
                                'order_id' => $order->id ?? null,
                                'order_item_id' => $item->id ?? null,
                                'content_id' => $item->content_id ?? null,
                                'error' => $e->getMessage(),
                            ]);
                            return false;
                        }
                    })
                    ->map(function($item) use ($order) {
                        // Vérifier à nouveau que le cours existe avant de créer l'objet
                        if (!$item->course) {
                            return null;
                        }
                        
                        // Créer un objet similaire à un enrollment pour la compatibilité avec la vue
                        return (object)[
                            'id' => null,
                            'content_id' => $item->content_id,
                            'course' => $item->course,
                            'status' => 'purchased',
                            'progress' => 0,
                            'order_id' => $order->id,
                            'order' => $order,
                            'created_at' => $order->created_at,
                            'is_purchased_not_enrolled' => true,
                        ];
                    })
                    ->filter(); // Filtrer les valeurs null
            });
        
        // Récupérer les cours téléchargeables gratuits téléchargés au moins une fois
        $allAccessCourseIds = $enrollments->pluck('content_id')
            ->merge($purchasedCourses->pluck('content_id'))
            ->filter()
            ->unique()
            ->all();
        
        $downloadedFreeCourseIds = \App\Models\CourseDownload::where('user_id', $user->id)
            ->whereNotNull('content_id')
            ->whereHas('content', function($q) {
                $q->where('is_downloadable', true)
                  ->where('is_free', true)
                  ->where('is_published', true);
            })
            ->pluck('content_id')
            ->unique()
            ->filter(function($contentId) use ($allAccessCourseIds) {
                // Exclure ceux déjà dans les enrollments ou les cours achetés
                return $contentId && !in_array($contentId, $allAccessCourseIds);
            })
            ->all();
        
        $downloadedFreeCourses = collect();
        if (!empty($downloadedFreeCourseIds)) {
            $downloadedFreeCourses = \App\Models\Course::whereIn('id', $downloadedFreeCourseIds)
                ->where('is_published', true) // S'assurer que le cours est toujours publié
                ->with(['provider', 'category'])
                ->get()
                ->filter(function($course) {
                    // Filtrer les cours qui n'existent plus ou ne sont plus publiés
                    return $course !== null;
                })
                ->map(function($course) use ($user) {
                    try {
                        $downloadDate = \App\Models\CourseDownload::where('user_id', $user->id)
                            ->where('content_id', $course->id)
                            ->orderBy('created_at', 'desc')
                            ->first()?->created_at ?? now();
                        
                        return (object)[
                            'id' => null,
                            'content_id' => $course->id,
                            'course' => $course,
                            'status' => 'downloaded',
                            'progress' => 0,
                            'order_id' => null,
                            'order' => null,
                            'created_at' => $downloadDate,
                            'is_downloaded_free' => true,
                        ];
                    } catch (\Exception $e) {
                        \Log::warning('Erreur lors de la création de l\'objet cours téléchargé', [
                            'user_id' => $user->id,
                            'content_id' => $course->id ?? null,
                            'error' => $e->getMessage(),
                        ]);
                        return null;
                    }
                })
                ->filter(); // Filtrer les valeurs null
        }
        
        // Combiner toutes les sources d'accès
        $allAccess = $enrollments->concat($purchasedCourses)->concat($downloadedFreeCourses)
            ->sortByDesc('created_at')
            ->values();
        
        // Charger tous les cours disponibles pour le modal d'ajout
        $allCourses = Course::published()
            ->with(['provider', 'category'])
            ->orderBy('title')
            ->get();
        
        return view('admin.users.show', compact('user', 'enrollments', 'allAccess', 'allCourses'));
    }

    /**
     * Exporter les données d'un seul utilisateur
     */
    public function showUserExport(Request $request, User $user)
    {
        $format = $request->get('format', 'csv');
        
        // Préparer les données de l'utilisateur
        $user->loadCount(['courses', 'enrollments']);
        
        // Formater les valeurs pour l'export
        $roleLabels = [
            'admin' => 'Administrateur',
            'provider' => 'Prestataire',
            'customer' => 'Client',
            'affiliate' => 'Affilié',
            'super_user' => 'Super Administrateur'
        ];
        
        $genderLabels = [
            'male' => 'Homme',
            'female' => 'Femme',
            'other' => 'Autre'
        ];
        
        // Créer un objet formaté pour l'export
        $formattedUser = (object)[
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $roleLabels[$user->role] ?? ucfirst($user->role ?? 'utilisateur'),
            'phone' => $user->phone ?? 'Non renseigné',
            'is_active' => $user->is_active ? 'Oui' : 'Non',
            'is_verified' => $user->is_verified ? 'Oui' : 'Non',
            'date_of_birth' => $user->date_of_birth ? $user->date_of_birth->format('d/m/Y') : 'Non renseignée',
            'gender' => $user->gender ? ($genderLabels[$user->gender] ?? ucfirst($user->gender)) : 'Non renseigné',
            'courses_count' => $user->courses_count ?? 0,
            'enrollments_count' => $user->enrollments_count ?? 0,
            'created_at' => $user->created_at->format('d/m/Y à H:i'),
            'last_login_at' => $user->last_login_at ? $user->last_login_at->format('d/m/Y à H:i') : 'Jamais'
        ];
        
        $columns = [
            'id' => 'ID',
            'name' => 'Nom',
            'email' => 'Email',
            'role' => 'Rôle',
            'phone' => 'Téléphone',
            'is_active' => 'Actif',
            'is_verified' => 'Vérifié',
            'date_of_birth' => 'Date de naissance',
            'gender' => 'Genre',
            'courses_count' => 'Nombre de cours',
            'enrollments_count' => 'Nombre d\'inscriptions',
            'created_at' => 'Date d\'inscription',
            'last_login_at' => 'Dernière connexion'
        ];
        
        // Créer une collection avec un seul élément formaté
        $data = collect([$formattedUser]);
        
        if ($format === 'excel') {
            return $this->exportToExcel($data, $columns, 'utilisateur-' . $user->id);
        } elseif ($format === 'pdf') {
            return $this->exportToPdf($data, $columns, 'utilisateur-' . $user->id);
        } else {
            return $this->exportToCsv($data, $columns, 'utilisateur-' . $user->id);
        }
    }

    public function destroyUser(User $user)
    {
        $user->delete();
        return redirect()->route('admin.users')
            ->with('success', 'Utilisateur supprimé avec succès.');
    }

    /**
     * Donner accès gratuit à un cours payant à un utilisateur
     */
    public function grantCourseAccess(Request $request, User $user)
    {
        $request->validate([
            'content_id' => 'required|exists:contents,id',
        ]);

        $course = Course::findOrFail($request->content_id);

        // Vérifier si l'utilisateur n'est pas déjà inscrit
        $existingEnrollment = Enrollment::where('user_id', $user->id)
            ->where('content_id', $course->id)
            ->first();

        if ($existingEnrollment) {
            return redirect()->route('admin.users.show', $user)
                ->with('error', 'L\'utilisateur a déjà accès à ce cours.');
        }

        // Créer l'inscription avec order_id null (accès gratuit)
        // La méthode createAndNotify envoie automatiquement les notifications et emails
        Enrollment::createAndNotify([
            'user_id' => $user->id,
            'content_id' => $course->id,
            'order_id' => null, // Accès gratuit donné par l'admin
            'status' => 'active',
        ]);

        return redirect()->route('admin.users.show', $user)
            ->with('success', "Accès gratuit au contenu \"{$course->title}\" accordé avec succès. L'utilisateur a été notifié par email.");
    }

    /**
     * Enlever complètement l'accès à un cours pour un utilisateur.
     *
     * Cette méthode supprime toutes les données de liaison entre l'utilisateur
     * et le contenu afin de repartir à zéro :
     * - inscriptions
     * - progression des leçons
     * - notes
     * - discussions et likes associés
     * - certificats
     * - avis
     *
     * Fonctionne aussi pour les contenus achetés (avec ou sans enrollment).
     */
    public function revokeCourseAccess(User $user, Course $course)
    {
        // Vérifier qu'il existe au moins une inscription OU une commande payée pour ce contenu
        $hasEnrollment = Enrollment::where('user_id', $user->id)
            ->where('content_id', $course->id)
            ->exists();

        $hasPaidOrder = Order::where('user_id', $user->id)
            ->whereIn('status', ['paid', 'completed'])
            ->whereHas('orderItems', function($query) use ($course) {
                $query->where('content_id', $course->id);
            })
            ->exists();

        if (! $hasEnrollment && ! $hasPaidOrder) {
            return redirect()->route('admin.users.show', $user)
                ->with('error', 'L\'utilisateur n\'a pas accès à ce cours.');
        }

        $courseTitle = $course->title;

        // Envoyer une notification et un email à l'utilisateur avant de supprimer les données
        try {
            try {
                $mailable = new \App\Mail\CourseAccessRevokedMail($course);
                $communicationService = app(\App\Services\CommunicationService::class);
                $communicationService->sendEmailAndWhatsApp($user, $mailable);
                \Log::info("Email CourseAccessRevokedMail envoyé directement à {$user->email} pour le contenu {$course->id}");
            } catch (\Exception $emailException) {
                \Log::error("Erreur lors de l'envoi de l'email CourseAccessRevokedMail", [
                    'user_id'   => $user->id,
                    'content_id'=> $course->id,
                    'error'     => $emailException->getMessage(),
                ]);
            }

            // Envoyer la notification en base de données (sans email car déjà envoyé)
            Notification::sendNow($user, new \App\Notifications\CourseAccessRevoked($course));
        } catch (\Exception $e) {
            \Log::error("Erreur lors de l'envoi de la notification de retrait d'accès: " . $e->getMessage());
        }

        // Supprimer toutes les données de liaison utilisateur <-> contenu
        DB::transaction(function () use ($user, $course) {
            // Toutes les inscriptions à ce contenu
            Enrollment::where('user_id', $user->id)
                ->where('content_id', $course->id)
                ->delete();

            // Progression des leçons
            LessonProgress::where('user_id', $user->id)
                ->where('content_id', $course->id)
                ->delete();

            // Notes de cours
            LessonNote::where('user_id', $user->id)
                ->where('content_id', $course->id)
                ->delete();

            // Discussions créées par l'utilisateur pour ce contenu
            $userDiscussionIds = LessonDiscussion::where('user_id', $user->id)
                ->where('content_id', $course->id)
                ->pluck('id');

            if ($userDiscussionIds->isNotEmpty()) {
                // Likes associés à ces discussions
                DiscussionLike::whereIn('discussion_id', $userDiscussionIds)->delete();
                LessonDiscussion::whereIn('id', $userDiscussionIds)->delete();
            }

            // Likes laissés par l'utilisateur sur des discussions de ce contenu
            $likedDiscussionIds = DiscussionLike::where('user_id', $user->id)
                ->whereHas('discussion', function ($query) use ($course) {
                    $query->where('content_id', $course->id);
                })
                ->pluck('id');

            if ($likedDiscussionIds->isNotEmpty()) {
                DiscussionLike::whereIn('id', $likedDiscussionIds)->delete();
            }

            // Certificats associés à ce contenu
            Certificate::where('user_id', $user->id)
                ->where('content_id', $course->id)
                ->delete();

            // Avis laissés sur ce contenu
            Review::where('user_id', $user->id)
                ->where('content_id', $course->id)
                ->delete();
        });

        return redirect()->route('admin.users.show', $user)
            ->with('success', "Tous les liens entre l'utilisateur et le contenu \"{$courseTitle}\" ont été supprimés. L'utilisateur repart à zéro sur ce contenu.");
    }

    /**
     * Désinscrire l'utilisateur d'un cours (alias pour revokeCourseAccess)
     */
    public function unenrollUser(User $user, Course $course)
    {
        return $this->revokeCourseAccess($user, $course);
    }

    // Gestion des cours
    public function courses(Request $request)
    {
        $query = Course::with(['provider', 'category', 'sections', 'lessons']);

        // Recherche par titre ou description
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filtre par catégorie
        if ($request->filled('category')) {
            $query->where('category_id', $request->get('category'));
        }

        // Filtre par statut
        if ($request->filled('status')) {
            if ($request->get('status') === 'published') {
                $query->where('is_published', true);
            } elseif ($request->get('status') === 'draft') {
                $query->where('is_published', false);
            } elseif ($request->get('status') === 'free') {
                $query->where('is_free', true);
            } elseif ($request->get('status') === 'paid') {
                $query->where('is_free', false);
            }
        }

        // Filtre par prestataire
        if ($request->filled('provider')) {
            $query->where('provider_id', $request->get('provider'));
        }

        // Tri
        $sortBy = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        
        if (in_array($sortBy, ['title', 'price', 'created_at', 'updated_at'])) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->latest();
        }

        $courses = $query->paginate(15)->withQueryString();

        // Données pour les filtres
        $categories = Category::active()->ordered()->get();
        $providers = User::providers()->get();

        // Statistiques
        $stats = [
            'total' => Course::count(),
            'published' => Course::where('is_published', true)->count(),
            'draft' => Course::where('is_published', false)->count(),
            'free' => Course::where('is_free', true)->count(),
            'paid' => Course::where('is_free', false)->count(),
        ];
        
        $baseCurrency = Setting::getBaseCurrency();
        return view('admin.contents.index', compact('courses', 'categories', 'providers', 'stats', 'baseCurrency'));
    }

    public function createCourse()
    {
        $categories = Category::active()->ordered()->get();
        // Inclure les prestataires et les administrateurs dans la liste
        $providers = User::whereIn('role', ['provider', 'admin', 'super_user'])
            ->orderBy('name')
            ->get();
        $baseCurrency = Setting::getBaseCurrency();
        return view('admin.contents.create', compact('categories', 'providers', 'baseCurrency'));
    }

    public function storeCourse(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'short_description' => 'nullable|string|max:500',
            'description' => 'required|string',
            'provider_id' => 'required|exists:users,id',
            'category_id' => 'required|exists:categories,id',
            'price' => 'nullable|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'sale_start_at' => 'nullable|date',
            'sale_end_at' => 'nullable|date|after_or_equal:sale_start_at',
            'sale_start_at' => 'nullable|date',
            'sale_end_at' => 'nullable|date|after_or_equal:sale_start_at',
            'is_free' => 'boolean',
            'is_downloadable' => 'boolean',
            'is_in_person_program' => 'boolean',
            'whatsapp_number' => 'nullable|string|max:30|required_if:is_in_person_program,1',
            'download_file_path' => 'nullable|file|mimes:zip,pdf,doc,docx,rar,7z,tar,gz|max:10485760', // 10GB max (kilobytes)
            'download_file_url' => 'nullable|url|max:1000',
            'is_published' => 'boolean',
            'is_sale_enabled' => 'boolean',
            'is_featured' => 'boolean',
            'show_customers_count' => 'boolean',
            'level' => 'required|in:beginner,intermediate,advanced',
            'language' => 'required|string|max:10',
            'use_external_payment' => 'boolean',
            'external_payment_url' => 'nullable|url|max:500|required_if:use_external_payment,1',
            'external_payment_text' => 'nullable|string|max:100',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max
            'thumbnail_chunk_path' => 'nullable|string|max:2048',
            'thumbnail_chunk_name' => 'nullable|string|max:255',
            'thumbnail_chunk_size' => 'nullable|integer|min:0',
            'thumbnail_chunk_path' => 'nullable|string|max:2048',
            'thumbnail_chunk_name' => 'nullable|string|max:255',
            'thumbnail_chunk_size' => 'nullable|integer|min:0',
            'video_preview' => 'nullable|string|max:255',
            'video_preview_file' => 'nullable|file|mimetypes:video/mp4,video/quicktime,video/webm|max:1048576',
            'video_preview_youtube_id' => 'nullable|string|max:100',
            'video_preview_is_unlisted' => 'boolean',
            'video_preview_path' => 'nullable|string|max:2048',
            'video_preview_name' => 'nullable|string|max:255',
            'video_preview_size' => 'nullable|integer|min:0',
            'video_preview_path' => 'nullable|string|max:2048',
            'video_preview_name' => 'nullable|string|max:255',
            'video_preview_size' => 'nullable|integer|min:0',
            'requirements' => 'nullable|array',
            'what_you_will_learn' => 'nullable|array',
            'meta_description' => 'nullable|string|max:160',
            'meta_keywords' => 'nullable|string|max:255',
            'tags' => 'nullable|string',
            'sections' => 'nullable|array',
            'sections.*.title' => 'required_with:sections|string|max:255',
            'sections.*.description' => 'nullable|string',
            'sections.*.lessons' => 'nullable|array',
            'sections.*.lessons.*.title' => 'required_with:sections.*.lessons|string|max:255',
            'sections.*.lessons.*.description' => 'nullable|string',
            'sections.*.lessons.*.type' => 'required_with:sections.*.lessons|in:video,text,quiz,assignment',
            'sections.*.lessons.*.content_url' => 'nullable|string',
            'sections.*.lessons.*.content_file' => 'nullable|file|mimetypes:video/mp4,video/quicktime,video/webm,application/pdf,application/zip,application/x-zip-compressed,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,text/csv,application/x-rar-compressed,application/x-7z-compressed,application/x-tar,application/gzip|max:1048576',
            'sections.*.lessons.*.content_text' => 'nullable|string',
            'sections.*.lessons.*.duration' => 'nullable|integer|min:0',
            'sections.*.lessons.*.is_preview' => 'boolean',
        ], [
            'thumbnail.image' => 'Le fichier doit être une image.',
            'thumbnail.mimes' => 'Le fichier doit être de type: jpeg, png, jpg, gif, webp.',
            'thumbnail.max' => 'Le fichier ne doit pas dépasser 5MB.',
            'download_file_path.file' => 'Le fichier de téléchargement doit être un fichier valide.',
            'download_file_path.mimes' => 'Le fichier de téléchargement doit être de type: zip, pdf, doc, docx, rar, 7z, tar, gz.',
            'download_file_path.max' => 'Le fichier de téléchargement ne doit pas dépasser 10 Go. Pour les fichiers plus volumineux, utilisez une URL externe.',
            'external_payment_url.required_if' => 'L\'URL de paiement externe est requise quand le paiement externe est activé.',
        ]);

        DB::beginTransaction();
        try {
            // Créer le cours
            $courseData = $request->only([
                'title', 'short_description', 'description', 'provider_id', 'category_id', 'price', 'sale_price',
                'sale_start_at', 'sale_end_at',
                'level', 'language',
                'video_preview', 'meta_description', 'meta_keywords', 'tags',
                'video_preview_youtube_id', 'video_preview_is_unlisted', 'use_external_payment',
                'external_payment_url', 'external_payment_text',
                'whatsapp_number',
            ]);

            $courseData['short_description'] = $this->normalizeNullableString($courseData['short_description'] ?? null);
            $courseData['video_preview'] = $this->normalizeNullableString($courseData['video_preview'] ?? null);
            $courseData['meta_description'] = $this->normalizeNullableString($courseData['meta_description'] ?? null);
            $courseData['meta_keywords'] = $this->normalizeCommaSeparatedString($courseData['meta_keywords'] ?? null);
            $courseData['external_payment_url'] = $this->normalizeNullableString($courseData['external_payment_url'] ?? null);
            $courseData['external_payment_text'] = $this->normalizeNullableString($courseData['external_payment_text'] ?? null);
            $courseData['whatsapp_number'] = $this->normalizeNullableString($courseData['whatsapp_number'] ?? null);
            $courseData['tags'] = $this->normalizeTags($courseData['tags'] ?? null);
            $courseData['requirements'] = $this->normalizeStringArray($request->input('requirements', []));
            $courseData['what_you_will_learn'] = $this->normalizeStringArray($request->input('what_you_will_learn', []));

            // Gérer l'upload de l'image de couverture
            if ($request->hasFile('thumbnail')) {
                $result = $this->fileUploadService->uploadImage(
                    $request->file('thumbnail'),
                    'courses/thumbnails',
                    null,
                    1920 // Max 1920px width
                );
                $courseData['thumbnail'] = $result['path'];
        } elseif ($request->filled('thumbnail_chunk_path')) {
            $chunkPath = $this->sanitizeUploadedPath($request->input('thumbnail_chunk_path'));
            if ($chunkPath) {
                $courseData['thumbnail'] = $this->fileUploadService->promoteTemporaryFile(
                    $chunkPath,
                    'courses/thumbnails'
                );
            }
            }

            // Gérer YouTube ou upload de la vidéo de prévisualisation
            $videoPreviewYoutubeId = $request->video_preview_youtube_id;
            $isUnlisted = $request->boolean('video_preview_is_unlisted', false);
            
            // Si YouTube vidéo ID fourni, extraire et valider
            if ($videoPreviewYoutubeId) {
                $courseData['video_preview_youtube_id'] = $this->extractYouTubeVideoId($videoPreviewYoutubeId);
                $courseData['video_preview_is_unlisted'] = $isUnlisted;
            }
            
            // Gérer upload fichier si fourni
            if ($request->hasFile('video_preview_file')) {
                $result = $this->fileUploadService->uploadVideo(
                    $request->file('video_preview_file'),
                    'courses/previews',
                    null
                );
                $courseData['video_preview'] = $result['path'];
            } elseif ($request->filled('video_preview_path')) {
                $sanitizedPath = $this->sanitizeUploadedPath($request->input('video_preview_path'));
                if ($sanitizedPath) {
                    $courseData['video_preview'] = $this->fileUploadService->promoteTemporaryFile(
                        $sanitizedPath,
                        'courses/previews'
                    );
                }
            }

            // Gérer le fichier de téléchargement spécifique
            if ($request->hasFile('download_file_path')) {
                try {
                    $result = $this->fileUploadService->uploadDocument(
                        $request->file('download_file_path'),
                        'courses/downloads',
                        null
                    );
                    $courseData['download_file_path'] = $result['path'];
                } catch (\Exception $e) {
                    DB::rollback();
                    \Log::error('Erreur upload download_file_path: ' . $e->getMessage(), [
                        'file' => $request->file('download_file_path')->getClientOriginalName(),
                        'size' => $request->file('download_file_path')->getSize(),
                        'error' => $e->getTraceAsString()
                    ]);
                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['download_file_path' => 'Erreur lors de l\'upload du fichier : ' . $e->getMessage()]);
                }
            } elseif ($request->filled('download_file_chunk_path')) {
                $chunkPath = $this->sanitizeUploadedPath($request->input('download_file_chunk_path'));
                if ($chunkPath) {
                    $courseData['download_file_path'] = $this->fileUploadService->promoteTemporaryFile(
                        $chunkPath,
                        'courses/downloads'
                    );
                }
            } elseif ($request->filled('download_file_url')) {
                // Si une URL externe est fournie, l'utiliser
                $courseData['download_file_path'] = $request->download_file_url;
            }

            // Traiter les tableaux
            $courseData['slug'] = $this->generateUniqueCourseSlug($request->title);

            $courseData['price'] = $request->filled('price') ? (float) $request->input('price') : null;
            $courseData['sale_price'] = $request->filled('sale_price') ? (float) $request->input('sale_price') : null;
            $courseData['sale_start_at'] = $request->filled('sale_start_at') ? Carbon::parse($request->input('sale_start_at')) : null;
            $courseData['sale_end_at'] = $request->filled('sale_end_at') ? Carbon::parse($request->input('sale_end_at')) : null;

            $courseData['is_free'] = $request->boolean('is_free', false);
            $courseData['is_downloadable'] = $request->boolean('is_downloadable', false);
            $courseData['is_in_person_program'] = $request->boolean('is_in_person_program', false);
            $courseData['use_external_payment'] = $request->boolean('use_external_payment', false);
            $courseData['is_published'] = $request->boolean('is_published', false);
            // Pour la création : si la checkbox est cochée → true, sinon → true par défaut (comme dans la migration)
            $courseData['is_sale_enabled'] = $request->has('is_sale_enabled') ? (bool) $request->input('is_sale_enabled') : true;
            $courseData['is_featured'] = $request->boolean('is_featured', false);
            $courseData['show_customers_count'] = $request->boolean('show_customers_count', false);
            $courseData['video_preview_is_unlisted'] = $request->boolean('video_preview_is_unlisted', false);

            if (!$courseData['is_in_person_program']) {
                $courseData['whatsapp_number'] = null;
            }

            if ($courseData['is_free']) {
                $courseData['price'] = 0;
                $courseData['sale_price'] = null;
                $courseData['sale_start_at'] = null;
                $courseData['sale_end_at'] = null;
            } else {
                if ($courseData['price'] === null) {
                    throw ValidationException::withMessages([
                        'price' => 'Le prix est obligatoire sauf si le cours est gratuit.',
                    ]);
                }

                if (!is_null($courseData['sale_price']) && $courseData['sale_price'] > $courseData['price']) {
                    throw ValidationException::withMessages([
                        'sale_price' => 'Le prix promotionnel doit être inférieur ou égal au prix standard.',
                    ]);
                }

                if (is_null($courseData['sale_price'])) {
                    $courseData['sale_start_at'] = null;
                    $courseData['sale_end_at'] = null;
                }
            }

            $course = Course::create($courseData);

            // Créer les sections et leçons
            if ($request->has('sections')) {
                foreach ($request->sections as $sectionIndex => $sectionData) {
                    $section = $course->sections()->create([
                        'title' => $sectionData['title'],
                        'description' => $sectionData['description'] ?? '',
                        'sort_order' => $sectionIndex + 1,
                        'is_published' => true,
                    ]);

                    // Créer les leçons de cette section
                    if (isset($sectionData['lessons'])) {
                        foreach ($sectionData['lessons'] as $lessonIndex => $lessonData) {
                            $filePath = null;
                            // Récupérer le fichier uploadé via l'indexation de la requête
                            $uploaded = $request->file("sections.$sectionIndex.lessons.$lessonIndex.content_file");
                            if ($uploaded) {
                                // Déterminer le type de fichier
                                $mimeType = $uploaded->getMimeType();
                                if (strpos($mimeType, 'video/') === 0) {
                                    $result = $this->fileUploadService->uploadVideo($uploaded, 'courses/lessons', null);
                                } elseif (strpos($mimeType, 'application/') === 0) {
                                    $result = $this->fileUploadService->uploadDocument($uploaded, 'courses/lessons', null);
                                } else {
                                    $result = $this->fileUploadService->upload($uploaded, 'courses/lessons', null);
                                }
                                $filePath = $result['path'];
                            } else {
                                $chunkPath = $this->sanitizeUploadedPath($lessonData['content_file_path'] ?? null);
                                if ($chunkPath) {
                                    $filePath = $this->fileUploadService->promoteTemporaryFile(
                                        $chunkPath,
                                        'courses/lessons'
                                    );
                                }
                            }

                            $section->lessons()->create([
                                'content_id' => $course->id,
                                'title' => $lessonData['title'],
                                'description' => $lessonData['description'] ?? '',
                                'type' => $lessonData['type'],
                                'content_url' => $filePath ? $filePath : ($lessonData['content_url'] ?? null),
                                'content_text' => $lessonData['content_text'] ?? null,
                                'duration' => $lessonData['duration'] ?? 0,
                                'sort_order' => $lessonIndex + 1,
                                'is_published' => true,
                                'is_preview' => $lessonData['is_preview'] ?? false,
                            ]);
                        }
                    }
                }
            }

            // Les statistiques (duration, lessons_count, etc.) sont maintenant calculées dynamiquement
            // via les accesseurs du modèle Course

            DB::commit();

            $course->refresh();

            if ($course->is_published) {
                $course->notifyCustomersOfNewCourse();
                $this->notifyInstructorCourseModeration($course, 'approved');
            } else {
                $this->notifyInstructorCourseModeration($course, 'pending');
            }

            $lessonsCount = $course->lessons()->count();
            return redirect()->route('admin.contents')
                ->with('success', 'Cours créé avec succès avec ' . $lessonsCount . ' leçons.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Erreur lors de la création du cours: ' . $e->getMessage()]);
        }
    }

    public function editCourse(Course $course)
    {
        $categories = Category::active()->ordered()->get();
        // Inclure les prestataires et les administrateurs dans la liste
        $providers = User::whereIn('role', ['provider', 'admin', 'super_user'])
            ->orderBy('name')
            ->get();
        $baseCurrency = Setting::getBaseCurrency();
        return view('admin.contents.edit', compact('course', 'categories', 'providers', 'baseCurrency'));
    }

    public function updateCourse(Request $request, Course $course)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'short_description' => 'nullable|string|max:500',
            'description' => 'required|string',
            'provider_id' => 'required|exists:users,id',
            'category_id' => 'required|exists:categories,id',
            'price' => 'nullable|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'is_free' => 'boolean',
            'is_downloadable' => 'boolean',
            'is_in_person_program' => 'boolean',
            'whatsapp_number' => 'nullable|string|max:30|required_if:is_in_person_program,1',
            'download_file_path' => 'nullable|file|mimes:zip,pdf,doc,docx,rar,7z,tar,gz|max:10485760', // 10GB max (kilobytes)
            'download_file_url' => 'nullable|url|max:1000',
            'use_external_payment' => 'boolean',
            'external_payment_url' => 'nullable|url|max:500|required_if:use_external_payment,1',
            'external_payment_text' => 'nullable|string|max:100',
            'is_published' => 'boolean',
            'is_sale_enabled' => 'boolean',
            'is_featured' => 'boolean',
            'show_customers_count' => 'boolean',
            'level' => 'required|in:beginner,intermediate,advanced',
            'language' => 'required|string|max:10',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max
            'video_preview' => 'nullable|string|max:255',
            'video_preview_file' => 'nullable|file|mimetypes:video/mp4,video/quicktime,video/webm|max:1048576',
            'video_preview_youtube_id' => 'nullable|string|max:100',
            'video_preview_is_unlisted' => 'boolean',
            'requirements' => 'nullable|array',
            'what_you_will_learn' => 'nullable|array',
            'meta_description' => 'nullable|string|max:160',
            'meta_keywords' => 'nullable|string|max:255',
            'tags' => 'nullable|string',
            'sections' => 'nullable|array',
            'sections.*.id' => 'nullable|integer|exists:content_sections,id',
            'sections.*.title' => 'required_with:sections|string|max:255',
            'sections.*.description' => 'nullable|string',
            'sections.*.lessons' => 'nullable|array',
            'sections.*.lessons.*.id' => 'nullable|integer|exists:content_lessons,id',
            'sections.*.lessons.*.title' => 'required_with:sections.*.lessons|string|max:255',
            'sections.*.lessons.*.description' => 'nullable|string',
            'sections.*.lessons.*.type' => 'required_with:sections.*.lessons|in:video,text,quiz,assignment',
            'sections.*.lessons.*.content_url' => 'nullable|string',
            'sections.*.lessons.*.content_file' => 'nullable|file|mimetypes:video/mp4,video/quicktime,video/webm,application/pdf,application/zip,application/x-zip-compressed,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,text/csv,application/x-rar-compressed,application/x-7z-compressed,application/x-tar,application/gzip|max:1048576',
            'sections.*.lessons.*.content_text' => 'nullable|string',
            'sections.*.lessons.*.duration' => 'nullable|integer|min:0',
            'sections.*.lessons.*.is_preview' => 'boolean',
            'sections.*.lessons.*.remove_existing_file' => 'nullable',
            'sections.*.lessons.*.existing_file_path' => 'nullable|string',
        ], [
            'thumbnail.image' => 'Le fichier doit être une image.',
            'thumbnail.mimes' => 'Le fichier doit être de type: jpeg, png, jpg, gif, webp.',
            'thumbnail.max' => 'Le fichier ne doit pas dépasser 5MB.',
            'download_file_path.mimes' => 'Le fichier de téléchargement doit être de type: zip, pdf, doc, docx, rar, 7z, tar, gz.',
            'download_file_path.max' => 'Le fichier de téléchargement ne doit pas dépasser 10 Go. Pour les fichiers plus volumineux, utilisez une URL externe.',
            'external_payment_url.required_if' => 'L\'URL de paiement externe est requise quand le paiement externe est activé.',
        ]);

        $wasPublished = $course->is_published;

        DB::beginTransaction();

        try {
            $data = $request->only([
                'title', 'short_description', 'description', 'provider_id', 'category_id', 'price', 'sale_price',
                'sale_start_at', 'sale_end_at',
                'use_external_payment', 'external_payment_url', 'external_payment_text',
                'level', 'language',
                'video_preview', 'meta_description', 'meta_keywords', 'tags',
                'video_preview_youtube_id', 'video_preview_is_unlisted',
                'whatsapp_number',
            ]);

            $data['short_description'] = $this->normalizeNullableString($data['short_description'] ?? null);
            $data['video_preview'] = $this->normalizeNullableString($data['video_preview'] ?? null);
            $data['meta_description'] = $this->normalizeNullableString($data['meta_description'] ?? null);
            $data['meta_keywords'] = $this->normalizeCommaSeparatedString($data['meta_keywords'] ?? null);
            $data['external_payment_url'] = $this->normalizeNullableString($data['external_payment_url'] ?? null);
            $data['external_payment_text'] = $this->normalizeNullableString($data['external_payment_text'] ?? null);
            $data['whatsapp_number'] = $this->normalizeNullableString($data['whatsapp_number'] ?? null);
            $data['tags'] = $this->normalizeTags($data['tags'] ?? null);
            $data['requirements'] = $this->normalizeStringArray($request->input('requirements', []));
            $data['what_you_will_learn'] = $this->normalizeStringArray($request->input('what_you_will_learn', []));

            // Gérer l'upload de l'image de couverture
            if ($request->hasFile('thumbnail')) {
                $result = $this->fileUploadService->uploadImage(
                    $request->file('thumbnail'),
                    'courses/thumbnails',
                    $course->thumbnail,
                    1920 // Max 1920px width
                );
                $data['thumbnail'] = $result['path'];
        } elseif ($request->filled('thumbnail_chunk_path')) {
            $chunkPath = $this->sanitizeUploadedPath($request->input('thumbnail_chunk_path'));
            if ($chunkPath) {
                $newThumbnailPath = $this->fileUploadService->promoteTemporaryFile(
                    $chunkPath,
                    'courses/thumbnails'
                );
                if ($course->thumbnail && $course->thumbnail !== $newThumbnailPath) {
                    $this->fileUploadService->deleteFile($course->thumbnail);
                }
                $data['thumbnail'] = $newThumbnailPath;
            }
            }

            // Gérer YouTube ou upload de la vidéo de prévisualisation
            $videoPreviewYoutubeId = $request->video_preview_youtube_id;
            $isUnlisted = $request->boolean('video_preview_is_unlisted', false);

            if ($videoPreviewYoutubeId) {
                $data['video_preview_youtube_id'] = $this->extractYouTubeVideoId($videoPreviewYoutubeId);
                $data['video_preview_is_unlisted'] = $isUnlisted;
            }

            if ($request->hasFile('video_preview_file')) {
                $result = $this->fileUploadService->uploadVideo(
                    $request->file('video_preview_file'),
                    'courses/previews',
                    $course->video_preview && !filter_var($course->video_preview, FILTER_VALIDATE_URL) ? $course->video_preview : null
                );
                $data['video_preview'] = $result['path'];
            } elseif ($request->filled('video_preview_path')) {
                $sanitizedPath = $this->sanitizeUploadedPath($request->input('video_preview_path'));
                if ($sanitizedPath) {
                    $currentPath = $course->video_preview && !filter_var($course->video_preview, FILTER_VALIDATE_URL)
                        ? $this->sanitizeUploadedPath($course->video_preview)
                        : null;
                    $finalPath = $this->fileUploadService->promoteTemporaryFile(
                        $sanitizedPath,
                        'courses/previews'
                    );
                    if ($currentPath && $currentPath !== $finalPath) {
                        $this->fileUploadService->deleteFile($currentPath);
                    }
                    $data['video_preview'] = $finalPath;
                }
            }

            // Gérer le fichier de téléchargement spécifique
            if ($request->has('remove_download_file') && $request->remove_download_file) {
                if ($course->download_file_path && !filter_var($course->download_file_path, FILTER_VALIDATE_URL)) {
                    $this->fileUploadService->deleteFile($course->download_file_path);
                }
                $data['download_file_path'] = null;
            } elseif ($request->hasFile('download_file_path')) {
                $oldPath = null;
                if ($course->download_file_path && !filter_var($course->download_file_path, FILTER_VALIDATE_URL)) {
                    $oldPath = $course->download_file_path;
                }
                try {
                    $result = $this->fileUploadService->uploadDocument(
                        $request->file('download_file_path'),
                        'courses/downloads',
                        $oldPath
                    );
                    $data['download_file_path'] = $result['path'];
                } catch (\Exception $e) {
                    DB::rollBack();
                    \Log::error('Erreur upload download_file_path (update): ' . $e->getMessage(), [
                        'file' => $request->file('download_file_path')->getClientOriginalName(),
                        'size' => $request->file('download_file_path')->getSize(),
                        'error' => $e->getTraceAsString()
                    ]);
                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['download_file_path' => 'Erreur lors de l\'upload du fichier : ' . $e->getMessage()]);
                }
            } elseif ($request->filled('download_file_chunk_path')) {
                $chunkPath = $this->sanitizeUploadedPath($request->input('download_file_chunk_path'));
                if ($chunkPath) {
                    $finalPath = $this->fileUploadService->promoteTemporaryFile(
                        $chunkPath,
                        'courses/downloads'
                    );
                    if ($course->download_file_path
                        && !filter_var($course->download_file_path, FILTER_VALIDATE_URL)
                        && $course->download_file_path !== $finalPath) {
                        $this->fileUploadService->deleteFile($course->download_file_path);
                    }
                    $data['download_file_path'] = $finalPath;
                }
            } elseif ($request->filled('download_file_url')) {
                $data['download_file_path'] = $request->download_file_url;
            }

            $data['slug'] = $this->generateUniqueCourseSlug($request->title, $course);

            $data['price'] = $request->filled('price') ? (float) $request->input('price') : null;
            $data['sale_price'] = $request->filled('sale_price') ? (float) $request->input('sale_price') : null;
            $data['sale_start_at'] = $request->filled('sale_start_at') ? Carbon::parse($request->input('sale_start_at')) : null;
            $data['sale_end_at'] = $request->filled('sale_end_at') ? Carbon::parse($request->input('sale_end_at')) : null;

            $data['is_free'] = $request->boolean('is_free', false);
            $data['is_downloadable'] = $request->boolean('is_downloadable', false);
            $data['is_in_person_program'] = $request->boolean('is_in_person_program', false);
            $data['use_external_payment'] = $request->boolean('use_external_payment', false);
            $data['is_published'] = $request->boolean('is_published', false);
            // Pour l'édition : si la checkbox est cochée → true, sinon → false (car l'utilisateur a décidé de la décocher)
            $data['is_sale_enabled'] = $request->has('is_sale_enabled') ? (bool) $request->input('is_sale_enabled') : false;
            $data['is_featured'] = $request->boolean('is_featured', false);
            $data['show_customers_count'] = $request->boolean('show_customers_count', false);
            $data['video_preview_is_unlisted'] = $request->boolean('video_preview_is_unlisted', false);

            if (!$data['is_in_person_program']) {
                $data['whatsapp_number'] = null;
            }

            if ($data['is_free']) {
                $data['price'] = 0;
                $data['sale_price'] = null;
                $data['sale_start_at'] = null;
                $data['sale_end_at'] = null;
            } else {
                if ($data['price'] === null) {
                    throw ValidationException::withMessages([
                        'price' => 'Le prix est obligatoire sauf si le cours est gratuit.',
                    ]);
                }

                if (!is_null($data['sale_price']) && $data['sale_price'] > $data['price']) {
                    throw ValidationException::withMessages([
                        'sale_price' => 'Le prix promotionnel doit être inférieur ou égal au prix standard.',
                    ]);
                }

                if (is_null($data['sale_price'])) {
                    $data['sale_start_at'] = null;
                    $data['sale_end_at'] = null;
                }
            }

            $course->update($data);

            $sectionsPayload = $request->input('sections', []);
            $sectionsToKeep = [];

            if (is_array($sectionsPayload)) {
                foreach ($sectionsPayload as $sectionIndex => $sectionData) {
                    if (!is_array($sectionData)) {
                        continue;
                    }

                    $sectionTitle = $sectionData['title'] ?? null;
                    if (empty($sectionTitle)) {
                        continue;
                    }

                    $sectionAttributes = [
                        'title' => $sectionTitle,
                        'description' => $sectionData['description'] ?? '',
                        'sort_order' => $sectionIndex + 1,
                        'is_published' => true,
                    ];

                    $section = null;
                    if (!empty($sectionData['id'])) {
                        $section = $course->sections()->where('id', $sectionData['id'])->first();
                    }

                    if ($section) {
                        $section->update($sectionAttributes);
                    } else {
                        $section = $course->sections()->create($sectionAttributes);
                    }

                    $sectionsToKeep[] = $section->id;

                    $lessonsPayload = $sectionData['lessons'] ?? [];
                    if (!is_array($lessonsPayload)) {
                        $lessonsPayload = [];
                    }

                    $lessonIdsToKeep = [];

                    foreach ($lessonsPayload as $lessonIndex => $lessonData) {
                        if (!is_array($lessonData)) {
                            continue;
                        }

                        $lessonTitle = $lessonData['title'] ?? null;
                        $lessonType = $lessonData['type'] ?? null;

                        if (empty($lessonTitle) || empty($lessonType)) {
                            continue;
                        }

                        $lesson = null;
                        if (!empty($lessonData['id'])) {
                            $lesson = $section->lessons()->where('id', $lessonData['id'])->first();
                        }

                        $lessonAttributes = [
                            'content_id' => $course->id,
                            'section_id' => $section->id,
                            'title' => $lessonTitle,
                            'description' => $lessonData['description'] ?? '',
                            'type' => $lessonType,
                            'content_text' => $lessonData['content_text'] ?? null,
                            'duration' => isset($lessonData['duration']) ? (int) $lessonData['duration'] : 0,
                            'sort_order' => $lessonIndex + 1,
                            'is_published' => true,
                            'is_preview' => in_array($lessonData['is_preview'] ?? false, [1, '1', true, 'true', 'on'], true),
                        ];

                        $uploaded = $request->file("sections.$sectionIndex.lessons.$lessonIndex.content_file");
                        $removeExistingFile = in_array($lessonData['remove_existing_file'] ?? false, [1, '1', true, 'true', 'on'], true);

                        $existingHiddenPath = $this->sanitizeUploadedPath($lessonData['existing_file_path'] ?? null);
                        $contentUrl = $lessonData['content_url'] ?? null;

                        $currentFilePath = null;
                        if ($lesson && $lesson->content_url && !filter_var($lesson->content_url, FILTER_VALIDATE_URL)) {
                            $currentFilePath = $lesson->content_url;
                        }

                        if ($uploaded) {
                            $mimeType = $uploaded->getMimeType();
                            try {
                                if (strpos($mimeType, 'video/') === 0) {
                                    $result = $this->fileUploadService->uploadVideo($uploaded, 'courses/lessons', $currentFilePath);
                                } else {
                                    $result = $this->fileUploadService->uploadDocument($uploaded, 'courses/lessons', $currentFilePath);
                                }
                            } catch (\Exception $e) {
                                DB::rollBack();
                                \Log::error('Erreur upload content_file (update): ' . $e->getMessage(), [
                                    'content_id' => $course->id,
                                    'lesson_id' => $lesson?->id,
                                    'file' => $uploaded->getClientOriginalName(),
                                    'size' => $uploaded->getSize(),
                                    'error' => $e->getTraceAsString()
                                ]);
                                return redirect()->back()
                                    ->withInput()
                                    ->withErrors([
                                        "sections.$sectionIndex.lessons.$lessonIndex.content_file" => 'Erreur lors de l\'upload du fichier : ' . $e->getMessage()
                                    ]);
                            }

                            $contentUrl = $result['path'];
                            $currentFilePath = $contentUrl;
                            $existingHiddenPath = null;
                            $removeExistingFile = false;
                        } else {
                            $chunkPath = $this->sanitizeUploadedPath($lessonData['content_file_path'] ?? null);
                            if ($chunkPath) {
                                $finalChunkPath = $this->fileUploadService->promoteTemporaryFile(
                                    $chunkPath,
                                    'courses/lessons'
                                );
                                if ($currentFilePath && $currentFilePath !== $finalChunkPath) {
                                    $this->fileUploadService->deleteFile($currentFilePath);
                                }
                                $contentUrl = $finalChunkPath;
                                $currentFilePath = $finalChunkPath;
                                $existingHiddenPath = null;
                                $removeExistingFile = false;
                            }
                        }

                        if ($removeExistingFile) {
                            if ($currentFilePath) {
                                $this->fileUploadService->deleteFile($currentFilePath);
                            }
                            $currentFilePath = null;
                            if (!$contentUrl) {
                                $contentUrl = null;
                            }
                        } elseif ($existingHiddenPath) {
                            $contentUrl = $existingHiddenPath;
                        } elseif ($currentFilePath && !$contentUrl) {
                            $contentUrl = $currentFilePath;
                        }

                        $lessonAttributes['content_url'] = $contentUrl;
                        $lessonAttributes['is_preview'] = $lessonAttributes['is_preview'] ? 1 : 0;

                        if ($lesson) {
                            $lesson->update($lessonAttributes);
                        } else {
                            $lesson = $section->lessons()->create($lessonAttributes);
                        }

                        $lessonIdsToKeep[] = $lesson->id;
                    }

                    $section->lessons()
                        ->whereNotIn('id', $lessonIdsToKeep)
                        ->get()
                        ->each(function (CourseLesson $lesson) {
                            if ($lesson->content_url && !filter_var($lesson->content_url, FILTER_VALIDATE_URL)) {
                                $this->fileUploadService->deleteFile($lesson->content_url);
                            }
                            $lesson->delete();
                        });
                }
            }

            $course->sections()
                ->whereNotIn('id', $sectionsToKeep)
                ->get()
                ->each(function ($section) {
                    foreach ($section->lessons as $lesson) {
                        if ($lesson->content_url && !filter_var($lesson->content_url, FILTER_VALIDATE_URL)) {
                            $this->fileUploadService->deleteFile($lesson->content_url);
                        }
                        $lesson->delete();
                    }
                    $section->delete();
                });

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erreur lors de la mise à jour du cours: ' . $e->getMessage(), [
                'content_id' => $course->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Erreur lors de la mise à jour du cours: ' . $e->getMessage()]);
        }

        $course->refresh();

        if (!$wasPublished && $course->is_published) {
            $course->notifyCustomersOfNewCourse();
            $this->notifyInstructorCourseModeration($course, 'approved');
        } elseif ($wasPublished && !$course->is_published) {
            $this->notifyInstructorCourseModeration($course, 'rejected');
        } else {
            $this->notifyInstructorCourseModeration($course, $course->is_published ? 'approved' : 'pending');
        }

        return redirect()->route('admin.contents')
            ->with('success', 'Cours mis à jour avec succès.');
    }

    public function showCourse(Course $course)
    {
        $course->load(['provider', 'category', 'sections.lessons', 'reviews', 'enrollments', 'orderItems.order']);
        // Charger les statistiques complètes
        $course->stats = $course->getCourseStats();
        $baseCurrency = Setting::getBaseCurrency();
        return view('admin.contents.show', compact('course', 'baseCurrency'));
    }

    public function destroyCourse(Course $course)
    {
        $course->delete();
        return redirect()->route('admin.contents')
            ->with('success', 'Cours supprimé avec succès.');
    }

    // Gestion des catégories
    public function categories(Request $request)
    {
        $query = Category::withCount('courses');

        // Filtre par recherche
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        // Filtre par statut
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Tri
        $sortBy = $request->get('sort', 'sort_order');
        $sortDirection = $request->get('direction', 'asc');
        
        if (in_array($sortBy, ['name', 'created_at', 'courses_count'])) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->ordered();
        }

        $categories = $query->paginate(20)->withQueryString();

        return view('admin.categories.index', compact('categories'));
    }

    public function createCategory()
    {
        return view('admin.categories.create');
    }

    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:categories,slug',
            'description' => 'nullable|string',
            'color' => 'required|string|max:7',
            'icon' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $data = $request->only([
            'name',
            'slug',
            'description',
            'color',
            'icon',
            'sort_order',
        ]);

        $data['slug'] = Str::slug($data['slug'] ?? $request->name);
        $data['is_active'] = $request->boolean('is_active', false);

        $category = Category::create($data);

        if ($category->is_active) {
            $this->notifyUsersOfNewCategory($category);
        }

        return redirect()->route('admin.categories')
            ->with('success', 'Catégorie créée avec succès.');
    }

    public function editCategory(Category $category)
    {
        return response()->json($category);
    }

    public function updateCategory(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:categories,slug,' . $category->id,
            'description' => 'nullable|string',
            'color' => 'required|string|max:7',
            'icon' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $data = $request->only([
            'name',
            'slug',
            'description',
            'color',
            'icon',
            'sort_order',
        ]);

        $data['slug'] = Str::slug($data['slug'] ?? $category->name);
        $data['is_active'] = $request->boolean('is_active', false);

        $category->update($data);

        return redirect()->route('admin.categories')
            ->with('success', 'Catégorie mise à jour avec succès.');
    }

    public function destroyCategory(Category $category)
    {
        $category->delete();
        return redirect()->route('admin.categories')
            ->with('success', 'Catégorie supprimée avec succès.');
    }

    // Gestion des annonces
    public function announcements(Request $request)
    {
        // Filtres pour les annonces
        $announcementsQuery = Announcement::query();
        
        if ($request->filled('announcement_search')) {
            $search = $request->announcement_search;
            $announcementsQuery->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }
        
        if ($request->filled('announcement_type')) {
            $announcementsQuery->where('type', $request->announcement_type);
        }
        
        if ($request->filled('announcement_status')) {
            if ($request->announcement_status === 'active') {
                $announcementsQuery->where('is_active', true);
            } elseif ($request->announcement_status === 'inactive') {
                $announcementsQuery->where('is_active', false);
            }
        }
        
        $announcements = $announcementsQuery->latest()->paginate(15)->withQueryString();
        
        // Filtres pour les emails envoyés
        $sentEmailsQuery = SentEmail::with('user');
        
        if ($request->filled('email_search')) {
            $search = $request->email_search;
            $sentEmailsQuery->where(function($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('recipient_email', 'like', "%{$search}%")
                  ->orWhere('recipient_name', 'like', "%{$search}%");
            });
        }
        
        if ($request->filled('email_status')) {
            $sentEmailsQuery->where('status', $request->email_status);
        }
        
        if ($request->filled('email_type')) {
            $sentEmailsQuery->where('type', $request->email_type);
        }
        
        $recentSentEmails = $sentEmailsQuery->latest()
            ->paginate(15, ['*'], 'emails_page')
            ->withQueryString();
        
        // Charger les utilisateurs destinataires pour afficher les avatars
        $recipientEmails = $recentSentEmails->pluck('recipient_email')->unique()->filter();
        $recipientUsers = User::whereIn('email', $recipientEmails)
            ->get()
            ->keyBy('email');
        
        // Ajouter les utilisateurs aux emails
        $recentSentEmails->getCollection()->transform(function ($email) use ($recipientUsers) {
            $email->recipient_user = $recipientUsers->get($email->recipient_email);
            return $email;
        });
        
        // Filtres pour les emails programmés
        $scheduledEmailsQuery = ScheduledEmail::with('creator')
            ->where('status', 'pending')
            ->where('scheduled_at', '>=', now());
        
        if ($request->filled('scheduled_search')) {
            $search = $request->scheduled_search;
            $scheduledEmailsQuery->where(function($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%");
            });
        }
        
        $pendingScheduledEmails = $scheduledEmailsQuery->orderBy('scheduled_at')
            ->paginate(15, ['*'], 'scheduled_page')
            ->withQueryString();
        
        // Statistiques des emails
        $emailStats = [
            'total_sent' => SentEmail::count(),
            'sent_today' => SentEmail::whereDate('sent_at', today())->count(),
            'failed_today' => SentEmail::whereDate('created_at', today())->where('status', 'failed')->count(),
            'pending_scheduled' => ScheduledEmail::where('status', 'pending')->count(),
        ];
        
        // Filtres pour les messages WhatsApp
        $whatsappQuery = SentWhatsAppMessage::with('user');
        
        if ($request->filled('whatsapp_search')) {
            $search = $request->whatsapp_search;
            $whatsappQuery->where(function($q) use ($search) {
                $q->where('message', 'like', "%{$search}%")
                  ->orWhere('recipient_phone', 'like', "%{$search}%")
                  ->orWhere('recipient_name', 'like', "%{$search}%");
            });
        }
        
        if ($request->filled('whatsapp_status')) {
            $whatsappQuery->where('status', $request->whatsapp_status);
        }
        
        if ($request->filled('whatsapp_type')) {
            $whatsappQuery->where('type', $request->whatsapp_type);
        }
        
        $recentSentWhatsApp = $whatsappQuery->latest()
            ->paginate(15, ['*'], 'whatsapp_page')
            ->withQueryString();
        
        // Charger les utilisateurs destinataires pour afficher les avatars
        $recipientPhones = $recentSentWhatsApp->pluck('recipient_phone')->unique()->filter();
        $recipientUsers = User::whereIn('phone', $recipientPhones)
            ->get()
            ->keyBy('phone');
        
        // Ajouter les utilisateurs aux messages
        $recentSentWhatsApp->getCollection()->transform(function ($message) use ($recipientUsers) {
            $message->recipient_user = $recipientUsers->get($message->recipient_phone);
            return $message;
        });
        
        // Statistiques des messages WhatsApp
        $whatsappStats = [
            'total_sent' => SentWhatsAppMessage::count(),
            'sent_today' => SentWhatsAppMessage::whereDate('sent_at', today())->count(),
            'failed_today' => SentWhatsAppMessage::whereDate('created_at', today())->where('status', 'failed')->count(),
        ];
        
        // Filtres pour les messages de contact
        $contactMessagesQuery = ContactMessage::query();
        
        if ($request->filled('contact_search')) {
            $search = $request->contact_search;
            $contactMessagesQuery->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }
        
        if ($request->filled('contact_status')) {
            $contactMessagesQuery->where('status', $request->contact_status);
        }
        
        $contactMessages = $contactMessagesQuery->latest()
            ->paginate(15, ['*'], 'contact_page')
            ->withQueryString();
        
        // Statistiques des messages de contact
        $contactStats = [
            'total' => ContactMessage::count(),
            'unread' => ContactMessage::where('status', 'unread')->count(),
            'read' => ContactMessage::where('status', 'read')->count(),
            'today' => ContactMessage::whereDate('created_at', today())->count(),
        ];
        
        return view('admin.announcements.index', compact('announcements', 'recentSentEmails', 'pendingScheduledEmails', 'emailStats', 'recentSentWhatsApp', 'whatsappStats', 'contactMessages', 'contactStats'));
    }

    public function createAnnouncement()
    {
        return view('admin.announcements.create');
    }

    public function storeAnnouncement(Request $request)
    {
        $data = $this->validatedAnnouncementData($request);

        $announcement = Announcement::create($data);

        $this->notifyUsersOfAnnouncement($announcement);

        return redirect()->route('admin.announcements')
            ->with('success', 'Annonce créée avec succès.');
    }

    public function previewAnnouncement(Announcement $announcement)
    {
        return view('admin.announcements.preview', compact('announcement'));
    }

    public function editAnnouncement(Announcement $announcement)
    {
        return response()->json($announcement);
    }

    public function updateAnnouncement(Request $request, Announcement $announcement)
    {
        $data = $this->validatedAnnouncementData($request);

        $announcement->update($data);

        $this->notifyUsersOfAnnouncement($announcement->refresh());

        return redirect()->route('admin.announcements')
            ->with('success', 'Annonce mise à jour avec succès.');
    }

    public function destroyAnnouncement(Announcement $announcement)
    {
        $announcement->delete();
        return redirect()->route('admin.announcements')
            ->with('success', 'Annonce supprimée avec succès.');
    }

    /**
     * Actions en masse pour les annonces
     */
    public function bulkActionAnnouncements(Request $request)
    {
        $actions = [
            'delete' => function($ids) {
                return $this->bulkDelete($ids, Announcement::class);
            },
            'activate' => function($ids) {
                return $this->bulkUpdate($ids, Announcement::class, ['is_active' => true]);
            },
            'deactivate' => function($ids) {
                return $this->bulkUpdate($ids, Announcement::class, ['is_active' => false]);
            }
        ];

        return $this->handleBulkAction($request, Announcement::class, $actions);
    }

    /**
     * Exporter les annonces
     */
    public function exportAnnouncements(Request $request)
    {
        $columns = [
            'id' => 'ID',
            'title' => 'Titre',
            'content' => 'Contenu',
            'type' => 'Type',
            'is_active' => 'Statut',
            'starts_at' => 'Date de début',
            'expires_at' => 'Date de fin',
            'created_at' => 'Date de création'
        ];

        $query = Announcement::query();

        return $this->exportData($request, $query, $columns, 'annonces');
    }

    /**
     * Actions en masse pour les emails envoyés
     */
    public function bulkActionSentEmails(Request $request)
    {
        $actions = [
            'delete' => function($ids) {
                return $this->bulkDelete($ids, SentEmail::class);
            }
        ];

        return $this->handleBulkAction($request, SentEmail::class, $actions);
    }

    /**
     * Exporter les emails envoyés
     */
    public function exportSentEmails(Request $request)
    {
        $columns = [
            'id' => 'ID',
            'recipient_name' => 'Destinataire',
            'recipient_email' => 'Email',
            'subject' => 'Sujet',
            'type' => 'Type',
            'status' => 'Statut',
            'sent_at' => 'Date d\'envoi',
            'created_at' => 'Date de création'
        ];

        $query = SentEmail::query();

        return $this->exportData($request, $query, $columns, 'emails_envoyes');
    }

    /**
     * Actions en masse pour les emails programmés
     */
    public function bulkActionScheduledEmails(Request $request)
    {
        $actions = [
            'delete' => function($ids) {
                return $this->bulkDelete($ids, ScheduledEmail::class);
            },
            'cancel' => function($ids) {
                $count = ScheduledEmail::whereIn('id', $ids)
                    ->where('status', 'pending')
                    ->update(['status' => 'cancelled']);
                
                return [
                    'message' => "{$count} email(s) programmé(s) annulé(s) avec succès.",
                    'count' => $count
                ];
            }
        ];

        return $this->handleBulkAction($request, ScheduledEmail::class, $actions);
    }

    /**
     * Exporter les emails programmés
     */
    public function exportScheduledEmails(Request $request)
    {
        $columns = [
            'id' => 'ID',
            'subject' => 'Sujet',
            'recipient_type' => 'Type de destinataire',
            'total_recipients' => 'Nombre de destinataires',
            'status' => 'Statut',
            'scheduled_at' => 'Programmé pour',
            'created_at' => 'Date de création'
        ];

        $query = ScheduledEmail::query();

        return $this->exportData($request, $query, $columns, 'emails_programmes');
    }

    /**
     * Actions en masse pour les messages WhatsApp
     */
    public function bulkActionWhatsAppMessages(Request $request)
    {
        $actions = [
            'delete' => function($ids) {
                return $this->bulkDelete($ids, SentWhatsAppMessage::class);
            }
        ];

        return $this->handleBulkAction($request, SentWhatsAppMessage::class, $actions);
    }

    /**
     * Exporter les messages WhatsApp
     */
    public function exportWhatsAppMessages(Request $request)
    {
        $columns = [
            'id' => 'ID',
            'recipient_name' => 'Destinataire',
            'recipient_phone' => 'Téléphone',
            'message' => 'Message',
            'type' => 'Type',
            'status' => 'Statut',
            'sent_at' => 'Date d\'envoi',
            'created_at' => 'Date de création'
        ];

        $query = SentWhatsAppMessage::query();

        return $this->exportData($request, $query, $columns, 'messages_whatsapp');
    }

    protected function validatedAnnouncementData(Request $request): array
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:info,success,warning,error',
            'button_text' => 'nullable|string|max:255',
            'button_url' => 'nullable|url',
            'starts_at' => ['nullable', 'date_format:Y-m-d\TH:i'],
            'expires_at' => ['nullable', 'date_format:Y-m-d\TH:i', 'after_or_equal:starts_at'],
            'is_active' => 'sometimes|boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', false);

        $data['starts_at'] = !empty($data['starts_at'])
            ? Carbon::createFromFormat('Y-m-d\TH:i', $data['starts_at'], config('app.timezone'))
            : null;

        $data['expires_at'] = !empty($data['expires_at'])
            ? Carbon::createFromFormat('Y-m-d\TH:i', $data['expires_at'], config('app.timezone'))
            : null;

        $data['button_text'] = $data['button_text'] ?? null;
        $data['button_url'] = $data['button_url'] ?? null;

        return $data;
    }

    protected function notifyUsersOfAnnouncement(Announcement $announcement): void
    {
        if (!$announcement->is_active) {
            return;
        }

        // Envoi de la notification en lots pour éviter de charger tous les utilisateurs en mémoire
        // Utiliser sendNow() pour envoyer immédiatement sans passer par la queue
        User::where('is_active', true)
            ->select('id', 'name', 'email')
            ->chunk(200, function ($users) use ($announcement) {
                Notification::sendNow($users, new AnnouncementPublished($announcement));
            });
    }

    /**
     * Afficher la page d'envoi d'email
     */
    public function showSendEmail()
    {
        // Vérifier la configuration du mailer
        $mailer = config('mail.default');
        $mailerConfig = config("mail.mailers.{$mailer}");
        $mailerTransport = $mailerConfig['transport'] ?? 'unknown';
        $isTestMode = in_array($mailerTransport, ['log', 'array']);
        
        return view('admin.announcements.send-email', [
            'mailerTransport' => $mailerTransport,
            'isTestMode' => $isTestMode,
            'mailerConfig' => $mailerConfig
        ]);
    }

    /**
     * Rechercher des utilisateurs pour l'envoi d'email
     */
    public function searchUsers(Request $request)
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $users = User::where('is_active', true)
            ->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            })
            ->select('id', 'name', 'email')
            ->limit(20)
            ->get();

        return response()->json($users);
    }

    /**
     * Compter les utilisateurs selon les critères
     */
    public function countUsers(Request $request)
    {
        $type = $request->get('type', 'all');
        
        $query = User::where('is_active', true)->whereNotNull('email');

        if ($type === 'role') {
            $roles = explode(',', $request->get('roles', ''));
            if (!empty($roles)) {
                // Séparer les rôles normaux des ambassadeurs
                $normalRoles = array_filter($roles, function($role) {
                    return $role !== 'ambassador';
                });
                $hasAmbassador = in_array('ambassador', $roles);
                
                if (!empty($normalRoles) && $hasAmbassador) {
                    // Si on a des rôles normaux ET des ambassadeurs
                    $query->where(function($q) use ($normalRoles, $hasAmbassador) {
                        $q->whereIn('role', $normalRoles);
                        if ($hasAmbassador) {
                            $q->orWhereHas('ambassador');
                        }
                    });
                } elseif (!empty($normalRoles)) {
                    // Seulement des rôles normaux
                    $query->whereIn('role', $normalRoles);
                } elseif ($hasAmbassador) {
                    // Seulement des ambassadeurs
                    $query->whereHas('ambassador');
                }
            }
        } elseif ($type === 'course') {
            $contentId = $request->get('content_id');
            if ($contentId) {
                // Récupérer les utilisateurs inscrits à ce cours
                $query->whereHas('enrollments', function($q) use ($contentId) {
                    $q->where('content_id', $contentId)
                      ->where('status', 'active');
                });
            }
        } elseif ($type === 'category') {
            $categoryId = $request->get('category_id');
            if ($categoryId) {
                // Récupérer les utilisateurs inscrits à des cours de cette catégorie
                $query->whereHas('enrollments', function($q) use ($categoryId) {
                    $q->where('status', 'active')
                      ->whereHas('content', function($courseQuery) use ($categoryId) {
                          $courseQuery->where('category_id', $categoryId)
                                     ->where('is_published', true);
                      });
                });
            }
        } elseif ($type === 'provider') {
            $providerId = $request->input('provider_id');
            if ($providerId) {
                // Récupérer les utilisateurs inscrits à des cours de ce prestataire
                $query->whereHas('enrollments', function($q) use ($providerId) {
                    $q->where('status', 'active')
                      ->whereHas('content', function($courseQuery) use ($providerId) {
                          $courseQuery->where('provider_id', $providerId)
                                     ->where('is_published', true);
                      });
                });
            }
        } elseif ($type === 'registration_date') {
            $dateFrom = $request->get('registration_date_from');
            $dateTo = $request->get('registration_date_to');
            if ($dateFrom || $dateTo) {
                if ($dateFrom) {
                    $query->whereDate('created_at', '>=', $dateFrom);
                }
                if ($dateTo) {
                    $query->whereDate('created_at', '<=', $dateTo);
                }
            }
        } elseif ($type === 'activity') {
            $activityType = $request->get('activity_type');
            if ($activityType) {
                switch ($activityType) {
                    case 'active_recent':
                        $query->where('last_login_at', '>=', now()->subDays(7));
                        break;
                    case 'active_month':
                        $query->where('last_login_at', '>=', now()->startOfMonth());
                        break;
                    case 'active_3months':
                        $query->where('last_login_at', '>=', now()->subMonths(3));
                        break;
                    case 'inactive_30days':
                        $query->where(function($q) {
                            $q->where('last_login_at', '<', now()->subDays(30))
                              ->orWhereNull('last_login_at');
                        });
                        break;
                    case 'inactive_90days':
                        $query->where(function($q) {
                            $q->where('last_login_at', '<', now()->subDays(90))
                              ->orWhereNull('last_login_at');
                        });
                        break;
                    case 'never_logged':
                        $query->whereNull('last_login_at');
                        break;
                }
            }
        }

        $count = $query->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Uploader une image pour TinyMCE
     */
    public function uploadImage(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|image|max:2048', // 2MB max
            ]);

            $file = $request->file('file');
            $service = app(FileUploadService::class);
            $uploadResult = $service->upload($file, 'email-images');
            
            // FileUploadService retourne un tableau avec 'path' et 'url'
            $url = is_array($uploadResult) ? ($uploadResult['url'] ?? null) : $uploadResult;
            
            if (!$url) {
                // Si pas d'URL dans le résultat, générer l'URL à partir du path
                $path = is_array($uploadResult) ? ($uploadResult['path'] ?? null) : $uploadResult;
                if ($path) {
                    $url = $service->getUrl($path, 'email-images');
                } else {
                    throw new \Exception('Impossible de déterminer l\'URL de l\'image uploadée');
                }
            }

            return response()->json([
                'location' => $url
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Erreur de validation: ' . implode(', ', $e->validator->errors()->all())
            ], 422);
        } catch (\Exception $e) {
            Log::error("Erreur lors de l'upload d'image: " . $e->getMessage());
            return response()->json([
                'error' => 'Erreur lors de l\'upload de l\'image: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Envoyer un email personnalisé
     */
    public function sendEmail(Request $request)
    {
        try {
            $request->validate([
                'recipient_type' => 'required|in:all,role,course,category,provider,downloaded_free,purchased,registration_date,activity,selected,single',
                'subject' => 'required|string|max:255',
                'email_content' => 'required|string',
                'send_type' => 'required|in:now,scheduled',
                'scheduled_at' => 'nullable|required_if:send_type,scheduled|date|after:now',
            'roles' => 'nullable|required_if:recipient_type,role|array',
            'roles.*' => 'in:customer,provider,admin,affiliate,ambassador',
            'content_id' => 'nullable|required_if:recipient_type,course|exists:contents,id',
            'category_id' => 'nullable|required_if:recipient_type,category|exists:categories,id',
            'provider_id' => 'nullable|required_if:recipient_type,provider|exists:users,id',
            'downloaded_content_id' => 'nullable|exists:contents,id',
            'purchase_type' => 'nullable|required_if:recipient_type,purchased|in:any,paid,completed,specific_content',
            'purchased_content_id' => 'nullable|required_if:purchase_type,specific_content|exists:contents,id',
            'registration_date_from' => 'nullable|required_if:recipient_type,registration_date|date',
            'registration_date_to' => 'nullable|required_if:recipient_type,registration_date|date|after_or_equal:registration_date_from',
            'activity_type' => 'nullable|required_if:recipient_type,activity|in:active_recent,active_month,active_3months,inactive_30days,inactive_90days,never_logged',
            'single_user_id' => 'nullable|required_if:recipient_type,single|exists:users,id',
            'user_ids' => 'nullable|required_if:recipient_type,selected|string',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240', // 10MB max par fichier
        ]);

        $recipientType = $request->recipient_type;
        $subject = $request->subject;
        $content = $request->email_content;

        // Gérer les pièces jointes
        $attachmentPaths = [];
        if ($request->hasFile('attachments')) {
            $service = app(FileUploadService::class);
            foreach ($request->file('attachments') as $file) {
                try {
                    $uploadResult = $service->upload($file, 'email-attachments');
                    // FileUploadService retourne un tableau avec 'path' et 'url', on a besoin du 'path'
                    $attachmentPaths[] = is_array($uploadResult) ? $uploadResult['path'] : $uploadResult;
                } catch (\Exception $e) {
                    Log::error("Erreur lors de l'upload de la pièce jointe: " . $e->getMessage());
                    // Continuer avec les autres fichiers même si un échoue
                }
            }
        }

        // Obtenir les destinataires
        $users = $this->getEmailRecipients($request);

        if ($users->isEmpty()) {
            return redirect()->back()
                ->with('error', 'Aucun destinataire trouvé pour cet envoi.')
                ->withInput();
        }

        // Envoi immédiat ou programmé
        if ($request->send_type === 'now') {
            // S'assurer que $users est une Collection
            if (!$users instanceof \Illuminate\Support\Collection) {
                $users = collect($users);
            }
            
            $sentCount = 0;
            $failedCount = 0;
            
            // Envoyer immédiatement en lots
            $users->chunk(100)->each(function ($userChunk) use ($subject, $content, $attachmentPaths, &$sentCount, &$failedCount, $recipientType) {
                foreach ($userChunk as $user) {
                    try {
                        // Envoyer l'email de manière synchrone (immédiate)
                        // Mail::to()->send() envoie immédiatement, contrairement à Mail::to()->queue()
                        $mailable = new CustomAnnouncementMail($subject, $content, $attachmentPaths);
                        $communicationService = app(\App\Services\CommunicationService::class);
                        $results = $communicationService->sendEmailAndWhatsApp($user, $mailable, null, false);
                        
                        // Vérifier si l'envoi a réussi
                        if ($results['email']['success']) {
                            // Enregistrer l'email envoyé avec succès
                            SentEmail::create([
                                'user_id' => $user->id,
                                'recipient_email' => $user->email,
                                'recipient_name' => $user->name,
                                'subject' => $subject,
                                'content' => $content,
                                'attachments' => $attachmentPaths ?: null,
                                'type' => 'custom',
                                'status' => 'sent',
                                'sent_at' => now(),
                                'metadata' => [
                                    'recipient_type' => $recipientType,
                                ],
                            ]);
                            
                            // Notifier l'utilisateur qu'un email lui a été envoyé
                            // Utiliser sendNow() pour envoyer immédiatement sans passer par la queue
                            try {
                                Notification::sendNow($user, new EmailSentNotification($subject, now()));
                            } catch (\Exception $notifException) {
                                Log::warning("Impossible d'envoyer la notification email à {$user->email}: " . $notifException->getMessage());
                            }
                            
                            $sentCount++;
                        } else {
                            // Enregistrer l'échec
                            $errorMessage = $results['email']['error'] ?? 'Erreur inconnue lors de l\'envoi de l\'email';
                            SentEmail::create([
                                'user_id' => $user->id,
                                'recipient_email' => $user->email,
                                'recipient_name' => $user->name,
                                'subject' => $subject,
                                'content' => $content,
                                'attachments' => $attachmentPaths ?: null,
                                'type' => 'custom',
                                'status' => 'failed',
                                'error_message' => $errorMessage,
                                'metadata' => [
                                    'recipient_type' => $recipientType,
                                ],
                            ]);
                            $failedCount++;
                            Log::error("Échec de l'envoi d'email à {$user->email}: {$errorMessage}");
                        }
                    } catch (\Exception $e) {
                        \Log::error("Erreur lors de l'envoi d'email à {$user->email}: " . $e->getMessage());
                        
                        // Enregistrer l'échec
                        SentEmail::create([
                            'user_id' => $user->id,
                            'recipient_email' => $user->email,
                            'recipient_name' => $user->name,
                            'subject' => $subject,
                            'content' => $content,
                            'attachments' => $attachmentPaths ?: null,
                            'type' => 'custom',
                            'status' => 'failed',
                            'error_message' => $e->getMessage(),
                            'metadata' => [
                                'recipient_type' => $recipientType,
                            ],
                        ]);
                        
                        $failedCount++;
                    }
                }
            });

            $message = "Email envoyé avec succès à {$sentCount} destinataire(s).";
            if ($failedCount > 0) {
                $message .= " {$failedCount} envoi(s) ont échoué.";
            }
        } else {
            // Envoi programmé
            $scheduledAt = Carbon::parse($request->scheduled_at);
            
            // Préparer la configuration des destinataires
            $recipientConfig = [];
            if ($recipientType === 'role') {
                $recipientConfig['roles'] = $request->input('roles', []);
            } elseif ($recipientType === 'selected') {
                $userIdsString = $request->input('user_ids', '');
                $recipientConfig['user_ids'] = array_filter(explode(',', $userIdsString), function($id) {
                    return !empty(trim($id)) && is_numeric(trim($id));
                });
            } elseif ($recipientType === 'single') {
                $recipientConfig['user_id'] = $request->input('single_user_id');
            }
            
            // Créer l'email programmé
            $scheduledEmail = ScheduledEmail::create([
                'created_by' => Auth::id(),
                'recipient_type' => $recipientType,
                'recipient_config' => $recipientConfig,
                'subject' => $subject,
                'content' => $content,
                'attachments' => $attachmentPaths ?: null,
                'status' => 'pending',
                'scheduled_at' => $scheduledAt,
                'total_recipients' => $users->count(),
            ]);
            
            $message = "Email programmé pour être envoyé le " . $scheduledAt->format('d/m/Y à H:i') . " à {$users->count()} destinataire(s).";
        }

        // Si c'est une requête AJAX, retourner JSON
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'redirect' => route('admin.announcements')
            ]);
        }
        
        return redirect()->route('admin.announcements')
            ->with('success', $message);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Erreur de validation
            Log::error("Erreur de validation lors de l'envoi d'email", [
                'errors' => $e->errors(),
                'request_data' => $request->except(['attachments', 'email_content'])
            ]);
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Erreur de validation',
                    'errors' => $e->errors()
                ], 422);
            }
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            // Autres erreurs
            Log::error("Erreur lors de l'envoi d'email", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'request_data' => $request->except(['attachments', 'email_content'])
            ]);
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Une erreur est survenue lors de l\'envoi: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Une erreur est survenue lors de l\'envoi: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Obtenir les destinataires selon le type sélectionné
     */
    protected function getEmailRecipients(Request $request)
    {
        $type = $request->recipient_type;

        $query = User::where('is_active', true)->whereNotNull('email');

        switch ($type) {
            case 'all':
                // Tous les utilisateurs actifs avec email
                break;

            case 'role':
                $roles = $request->input('roles', []);
                if (!empty($roles)) {
                    // Séparer les rôles normaux des ambassadeurs
                    $normalRoles = array_filter($roles, function($role) {
                        return $role !== 'ambassador';
                    });
                    $hasAmbassador = in_array('ambassador', $roles);
                    
                    if (!empty($normalRoles) && $hasAmbassador) {
                        // Si on a des rôles normaux ET des ambassadeurs
                        $query->where(function($q) use ($normalRoles, $hasAmbassador) {
                            $q->whereIn('role', $normalRoles);
                            if ($hasAmbassador) {
                                $q->orWhereHas('ambassador');
                            }
                        });
                    } elseif (!empty($normalRoles)) {
                        // Seulement des rôles normaux
                        $query->whereIn('role', $normalRoles);
                    } elseif ($hasAmbassador) {
                        // Seulement des ambassadeurs
                        $query->whereHas('ambassador');
                    }
                }
                break;

            case 'course':
                $contentId = $request->input('content_id');
                if ($contentId) {
                    // Récupérer les utilisateurs inscrits à ce cours
                    $query->whereHas('enrollments', function($q) use ($contentId) {
                        $q->where('content_id', $contentId)
                          ->where('status', 'active');
                    });
                } else {
                    return collect();
                }
                break;

            case 'category':
                $categoryId = $request->input('category_id');
                if ($categoryId) {
                    // Récupérer les utilisateurs inscrits à des cours de cette catégorie
                    $query->whereHas('enrollments', function($q) use ($categoryId) {
                        $q->where('status', 'active')
                          ->whereHas('content', function($courseQuery) use ($categoryId) {
                              $courseQuery->where('category_id', $categoryId)
                                         ->where('is_published', true);
                          });
                    });
                } else {
                    return collect();
                }
                break;

            case 'provider':
                $providerId = $request->input('provider_id');
                if ($providerId) {
                    // Récupérer les utilisateurs inscrits à des cours de ce prestataire
                    $query->whereHas('enrollments', function($q) use ($providerId) {
                        $q->where('status', 'active')
                          ->whereHas('content', function($courseQuery) use ($providerId) {
                              $courseQuery->where('provider_id', $providerId)
                                         ->where('is_published', true);
                          });
                    });
                } else {
                    return collect();
                }
                break;

            case 'registration_date':
                $dateFrom = $request->input('registration_date_from');
                $dateTo = $request->input('registration_date_to');
                if ($dateFrom || $dateTo) {
                    if ($dateFrom) {
                        $query->whereDate('created_at', '>=', $dateFrom);
                    }
                    if ($dateTo) {
                        $query->whereDate('created_at', '<=', $dateTo);
                    }
                } else {
                    return collect();
                }
                break;

            case 'activity':
                $activityType = $request->input('activity_type');
                if ($activityType) {
                    switch ($activityType) {
                        case 'active_recent':
                            $query->where('last_login_at', '>=', now()->subDays(7));
                            break;
                        case 'active_month':
                            $query->where('last_login_at', '>=', now()->startOfMonth());
                            break;
                        case 'active_3months':
                            $query->where('last_login_at', '>=', now()->subMonths(3));
                            break;
                        case 'inactive_30days':
                            $query->where(function($q) {
                                $q->where('last_login_at', '<', now()->subDays(30))
                                  ->orWhereNull('last_login_at');
                            });
                            break;
                        case 'inactive_90days':
                            $query->where(function($q) {
                                $q->where('last_login_at', '<', now()->subDays(90))
                                  ->orWhereNull('last_login_at');
                            });
                            break;
                        case 'never_logged':
                            $query->whereNull('last_login_at');
                            break;
                    }
                } else {
                    return collect();
                }
                break;

            case 'downloaded_free':
                $downloadedContentId = $request->input('downloaded_content_id');
                // Utilisateurs ayant téléchargé au moins une fois un contenu téléchargeable gratuit
                $query->whereHas('downloads', function($q) use ($downloadedContentId) {
                    $q->whereHas('content', function($contentQuery) use ($downloadedContentId) {
                        $contentQuery->where('is_downloadable', true)
                                    ->where('is_free', true)
                                    ->where('is_published', true);
                        if ($downloadedContentId) {
                            $contentQuery->where('id', $downloadedContentId);
                        }
                    });
                });
                break;

            case 'purchased':
                $purchaseType = $request->input('purchase_type', 'any');
                $purchasedContentId = $request->input('purchased_content_id');
                
                if ($purchaseType === 'specific_content' && $purchasedContentId) {
                    // Utilisateurs ayant acheté un contenu spécifique
                    $query->whereHas('orders', function($orderQuery) use ($purchasedContentId) {
                        $orderQuery->whereIn('status', ['paid', 'completed'])
                                   ->whereHas('orderItems', function($itemQuery) use ($purchasedContentId) {
                                       $itemQuery->where('content_id', $purchasedContentId);
                                   });
                    });
                } else {
                    // Utilisateurs ayant effectué des achats selon le type
                    $query->whereHas('orders', function($orderQuery) use ($purchaseType) {
                        if ($purchaseType === 'paid') {
                            $orderQuery->where('status', 'paid');
                        } elseif ($purchaseType === 'completed') {
                            $orderQuery->where('status', 'completed');
                        } else {
                            // 'any' - tous les utilisateurs ayant des commandes payées ou complétées
                            $orderQuery->whereIn('status', ['paid', 'completed']);
                        }
                    });
                }
                break;

            case 'selected':
                $userIdsString = $request->input('user_ids', '');
                if (empty($userIdsString)) {
                    return collect();
                }
                $userIds = array_filter(explode(',', $userIdsString), function($id) {
                    return !empty(trim($id)) && is_numeric(trim($id));
                });
                if (!empty($userIds)) {
                    $query->whereIn('id', array_map('intval', $userIds));
                } else {
                    return collect();
                }
                break;

            case 'single':
                $userId = $request->single_user_id;
                if ($userId) {
                    $query->where('id', $userId);
                } else {
                    return collect();
                }
                break;
        }

        return $query->select('id', 'name', 'email')->get();
    }

    /**
     * Afficher la liste des emails envoyés
     */
    public function sentEmails(Request $request)
    {
        $query = SentEmail::with('user')->latest();

        // Recherche
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('recipient_email', 'like', "%{$search}%")
                  ->orWhere('recipient_name', 'like', "%{$search}%");
            });
        }

        // Filtre par type
        if ($request->filled('type')) {
            $query->where('type', $request->get('type'));
        }

        // Filtre par statut
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        // Filtre par date
        if ($request->filled('date_from')) {
            $query->whereDate('sent_at', '>=', $request->get('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('sent_at', '<=', $request->get('date_to'));
        }

        $emails = $query->paginate(20);

        $stats = [
            'total' => SentEmail::count(),
            'sent' => SentEmail::where('status', 'sent')->count(),
            'failed' => SentEmail::where('status', 'failed')->count(),
            'pending' => SentEmail::where('status', 'pending')->count(),
        ];

        return view('admin.emails.sent', compact('emails', 'stats'));
    }

    /**
     * Afficher la liste des emails programmés
     */
    public function scheduledEmails(Request $request)
    {
        $query = ScheduledEmail::with('creator')->latest();

        // Recherche
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%");
            });
        }

        // Filtre par statut
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        $emails = $query->paginate(20);

        $stats = [
            'total' => ScheduledEmail::count(),
            'pending' => ScheduledEmail::where('status', 'pending')->count(),
            'processing' => ScheduledEmail::where('status', 'processing')->count(),
            'completed' => ScheduledEmail::where('status', 'completed')->count(),
            'failed' => ScheduledEmail::where('status', 'failed')->count(),
        ];

        return view('admin.emails.scheduled', compact('emails', 'stats'));
    }

    /**
     * Voir les détails d'un email envoyé
     */
    public function showSentEmail(SentEmail $sentEmail)
    {
        // Charger l'utilisateur destinataire pour afficher l'avatar
        $recipientUser = null;
        if ($sentEmail->recipient_email) {
            $recipientUser = User::where('email', $sentEmail->recipient_email)->first();
        }
        
        return view('admin.emails.sent-show', compact('sentEmail', 'recipientUser'));
    }

    /**
     * Voir les détails d'un email programmé
     */
    public function showScheduledEmail(ScheduledEmail $scheduledEmail)
    {
        return view('admin.emails.scheduled-show', compact('scheduledEmail'));
    }

    /**
     * Annuler un email programmé
     */
    public function destroySentEmail(SentEmail $sentEmail)
    {
        $sentEmail->delete();
        
        return redirect()->back()
            ->with('success', 'Email supprimé avec succès.');
    }

    public function destroyScheduledEmail(ScheduledEmail $scheduledEmail)
    {
        $scheduledEmail->delete();
        
        return redirect()->back()
            ->with('success', 'Email programmé supprimé avec succès.');
    }

    public function cancelScheduledEmail(ScheduledEmail $scheduledEmail)
    {
        if ($scheduledEmail->status !== 'pending') {
            return redirect()->back()
                ->with('error', 'Seuls les emails en attente peuvent être annulés.');
        }

        $scheduledEmail->update(['status' => 'cancelled']);

        return redirect()->back()
            ->with('success', 'Email programmé annulé avec succès.');
    }

    protected function generateUniqueCourseSlug(string $title, ?Course $ignoreCourse = null): string
    {
        $baseSlug = Str::slug($title) ?: 'cours';
        $slug = $baseSlug;
        $counter = 1;

        while (
            Course::where('slug', $slug)
                ->when($ignoreCourse, fn($query) => $query->where('id', '!=', $ignoreCourse->id))
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter++;
        }

        return $slug;
    }

    protected function notifyUsersOfNewCategory(Category $category): void
    {
        // Utiliser sendNow() pour envoyer immédiatement sans passer par la queue
        User::where('is_active', true)
            ->chunk(200, function ($users) use ($category) {
                Notification::sendNow($users, new CategoryCreatedNotification($category));
            });
    }

    protected function notifyCustomersOfNewCourse(Course $course): void
    {
        // Utiliser sendNow() pour envoyer immédiatement sans passer par la queue
        User::customers()
            ->where('is_active', true)
            ->chunk(200, function ($users) use ($course) {
                Notification::sendNow($users, new CoursePublishedNotification($course));
            });
    }

    protected function notifyInstructorCourseModeration(Course $course, string $status): void
    {
        $provider = $course->provider;
        if (!$provider) {
            return;
        }

        // Utiliser sendNow() pour envoyer immédiatement sans passer par la queue
        Notification::sendNow($provider, new CourseModerationNotification($course, $status));
    }

    // Gestion des partenaires
    public function partners()
    {
        $partners = Partner::ordered()->paginate(20);
        return view('admin.partners.index', compact('partners'));
    }

    public function createPartner()
    {
        return view('admin.partners.create');
    }

    public function storePartner(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'required|url',
            'website' => 'nullable|url',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        Partner::create($request->all());

        return redirect()->route('admin.partners')
            ->with('success', 'Partenaire ajouté avec succès.');
    }

    public function editPartner(Partner $partner)
    {
        return response()->json($partner);
    }

    public function updatePartner(Request $request, Partner $partner)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'required|url',
            'website' => 'nullable|url',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $partner->update($request->all());

        return redirect()->route('admin.partners')
            ->with('success', 'Partenaire mis à jour avec succès.');
    }

    public function destroyPartner(Partner $partner)
    {
        $partner->delete();
        return redirect()->route('admin.partners')
            ->with('success', 'Partenaire supprimé avec succès.');
    }

    /**
     * AJAX: Upload d'un fichier de leçon (vidéo/PDF) avec réponse JSON
     */
    public function uploadLessonFile(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimetypes:video/mp4,video/quicktime,video/webm,application/pdf|max:1048576',
        ]);

        // Déterminer le type de fichier
        $mimeType = $request->file('file')->getMimeType();
        if (strpos($mimeType, 'video/') === 0) {
            $result = $this->fileUploadService->uploadVideo($request->file('file'), 'courses/lessons', null);
        } else {
            $result = $this->fileUploadService->uploadDocument($request->file('file'), 'courses/lessons', null);
        }

        return response()->json([
            'success' => true,
            'path' => $result['path'],
            'url' => $result['url'],
        ]);
    }

    /**
     * AJAX: Upload de la vidéo de prévisualisation du cours
     */
    public function uploadVideoPreview(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimetypes:video/mp4,video/quicktime,video/webm|max:1048576',
        ]);

        $result = $this->fileUploadService->uploadVideo($request->file('file'), 'courses/previews', null);

        return response()->json([
            'success' => true,
            'path' => $result['path'],
            'url' => $result['url'],
        ]);
    }

    // Gestion des témoignages
    public function testimonials()
    {
        $testimonials = Testimonial::ordered()->paginate(15)->withQueryString();
        return view('admin.testimonials.index', compact('testimonials'));
    }

    public function createTestimonial()
    {
        return view('admin.testimonials.create');
    }

    public function storeTestimonial(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'photo' => 'nullable|string|max:2048',
            'photo_chunk_path' => 'nullable|string|max:2048',
            'photo_chunk_name' => 'nullable|string|max:255',
            'photo_chunk_size' => 'nullable|integer|min:0',
            'testimonial' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
            'is_active' => 'boolean',
        ]);

        $data = $request->only([
            'name',
            'title',
            'company',
            'testimonial',
            'rating',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);

        // Gérer l'upload de la photo via système de chunks
        $photoPath = null;
        if ($request->filled('photo_chunk_path')) {
            $chunkPath = $this->sanitizeUploadedPath($request->input('photo_chunk_path'));
            if ($chunkPath) {
                $photoPath = $this->fileUploadService->promoteTemporaryFile(
                    $chunkPath,
                    'testimonials/photos'
                );
            }
        } elseif ($request->filled('photo')) {
            // Compatibilité au cas où une URL serait encore envoyée
            $photoPath = $this->normalizeNullableString($request->input('photo'));
        }

        $data['photo'] = $photoPath;

        Testimonial::create($data);

        return redirect()->route('admin.testimonials')
            ->with('success', 'Témoignage ajouté avec succès.');
    }

    public function editTestimonial(Testimonial $testimonial)
    {
        $photoUrl = null;
        if ($testimonial->photo) {
            $photoUrl = str_starts_with($testimonial->photo, 'http')
                ? $testimonial->photo
                : FileHelper::url($testimonial->photo);
        }

        return response()->json([
            'id' => $testimonial->id,
            'name' => $testimonial->name,
            'title' => $testimonial->title,
            'company' => $testimonial->company,
            'testimonial' => $testimonial->testimonial,
            'rating' => $testimonial->rating,
            'is_active' => $testimonial->is_active,
            'photo' => $testimonial->photo,
            'photo_url' => $photoUrl,
        ]);
    }

    public function updateTestimonial(Request $request, Testimonial $testimonial)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'photo' => 'nullable|string|max:2048',
            'photo_chunk_path' => 'nullable|string|max:2048',
            'photo_chunk_name' => 'nullable|string|max:255',
            'photo_chunk_size' => 'nullable|integer|min:0',
            'testimonial' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
            'is_active' => 'boolean',
        ]);

        $data = $request->only([
            'name',
            'title',
            'company',
            'testimonial',
            'rating',
        ]);
        $data['is_active'] = $request->boolean('is_active', $testimonial->is_active);

        // Gérer la mise à jour de la photo
        if ($request->filled('photo_chunk_path')) {
            $chunkPath = $this->sanitizeUploadedPath($request->input('photo_chunk_path'));
            if ($chunkPath) {
                $newPhotoPath = $this->fileUploadService->promoteTemporaryFile(
                    $chunkPath,
                    'testimonials/photos'
                );
                if ($newPhotoPath) {
                    $data['photo'] = $newPhotoPath;
                }
            }
        } elseif ($request->filled('photo')) {
            // Toujours accepter une URL manuelle si fournie
            $data['photo'] = $this->normalizeNullableString($request->input('photo'));
        }

        $testimonial->update($data);

        return redirect()->route('admin.testimonials')
            ->with('success', 'Témoignage mis à jour avec succès.');
    }

    public function destroyTestimonial(Testimonial $testimonial)
    {
        $testimonial->delete();
        return redirect()->route('admin.testimonials')
            ->with('success', 'Témoignage supprimé avec succès.');
    }

    // Course Lessons Management (legacy - removed)

    /**
     * Afficher la page de gestion des statistiques
     */
    public function statistics()
    {
        // Statistiques générales
        $stats = [
            'total_courses' => Course::count(),
            'published_courses' => Course::published()->count(),
            'total_enrollments' => Enrollment::count(),
            'total_reviews' => \App\Models\Review::count(),
            'total_downloads' => CourseDownload::count(),
            'unique_downloaders' => CourseDownload::distinct('user_id')->count('user_id'),
        ];

        // Statistiques de téléchargements
        $downloadStats = [
            // Par cours
            'by_course' => Course::where('is_downloadable', true)
                ->withCount('downloads')
                ->orderBy('downloads_count', 'desc')
                ->limit(20)
                ->get(),
            
            // Par utilisateur
            'by_user' => User::withCount('downloads')
                ->having('downloads_count', '>', 0)
                ->orderBy('downloads_count', 'desc')
                ->limit(20)
                ->get(),
            
            // Par catégorie
            'by_category' => Category::withCount(['courses' => function($query) {
                    $query->where('is_downloadable', true);
                }])
                ->with(['courses' => function($query) {
                    $query->where('is_downloadable', true)->withCount('downloads');
                }])
                ->get()
                ->map(function($category) {
                    $category->total_downloads = $category->courses->sum('downloads_count');
                    return $category;
                })
                ->sortByDesc('total_downloads')
                ->take(10),
            
            // Par pays
            'by_country' => CourseDownload::select('country', 'country_name')
                ->selectRaw('COUNT(*) as downloads_count')
                ->whereNotNull('country')
                ->groupBy('country', 'country_name')
                ->orderBy('downloads_count', 'desc')
                ->limit(20)
                ->get(),
            
            // Par ville
            'by_city' => CourseDownload::select('city', 'country_name')
                ->selectRaw('COUNT(*) as downloads_count')
                ->whereNotNull('city')
                ->groupBy('city', 'country_name')
                ->orderBy('downloads_count', 'desc')
                ->limit(20)
                ->get(),
            
            // Téléchargements par jour (30 derniers jours)
            'daily' => CourseDownload::selectRaw($this->buildDateFormatSelect('created_at', '%Y-%m-%d', 'date') . ', COUNT(*) as downloads_count')
                ->where('created_at', '>=', now()->subDays(30))
                ->groupBy('date')
                ->orderBy('date')
                ->get(),
        ];

        // Contenus avec le plus de clients
        $topCourses = Course::published()
            ->with(['provider', 'category'])
            ->withCount('enrollments')
            ->orderBy('enrollments_count', 'desc')
            ->limit(10)
            ->get();

        // Cours les mieux notés
        $topRatedCourses = Course::published()
            ->with(['provider', 'category'])
            ->withAvg('reviews', 'rating')
            ->having('reviews_avg_rating', '>', 0)
            ->orderBy('reviews_avg_rating', 'desc')
            ->limit(10)
            ->get();

        return view('admin.statistics', compact('stats', 'topCourses', 'topRatedCourses', 'downloadStats'));
    }

    /**
     * Recalculer les statistiques d'un cours
     */
    public function recalculateCourseStats(Course $course)
    {
        try {
            // Forcer le recalcul des statistiques
            $stats = $course->getCourseStats();
            
            return response()->json([
                'success' => true,
                'message' => 'Statistiques recalculées avec succès',
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du recalcul: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Recalculer toutes les statistiques
     */
    public function recalculateAllStats()
    {
        try {
            $courses = Course::with(['enrollments', 'reviews', 'sections.lessons'])->get();
            $processed = 0;
            
            foreach ($courses as $course) {
                // Forcer le recalcul des statistiques
                $course->getCourseStats();
                $processed++;
            }
            
            return response()->json([
                'success' => true,
                'message' => "Statistiques recalculées pour {$processed} cours",
                'processed' => $processed
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du recalcul: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Liste des paiements/transactions (réussis/échoués)
     */
    public function payments(Request $request)
    {
        $query = Payment::with(['order.user'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }
        if ($request->filled('method')) {
            $query->where('payment_method', $request->string('method')->toString());
        }
        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->whereHas('order.user', function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%");
            });
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date('date_to'));
        }

        $payments = $query->paginate(20)->withQueryString();
        $baseCurrency = Setting::getBaseCurrency();

        return view('admin.payments.index', compact('payments', 'baseCurrency'));
    }

    /**
     * Afficher la page de paramètres
     */
    public function settings()
    {
        $settings = Setting::all()->keyBy('key');
        $baseCurrency = Setting::getBaseCurrency();
        $commissionPercentage = Setting::get('external_provider_commission_percentage', 20);
        $metaTrackingEnabled = Setting::get('meta_tracking_enabled', false);
        
        // Paramètres Wallet
        $walletSettings = [
            'holding_period_days' => Setting::get('wallet_holding_period_days', 7),
            'minimum_payout_amount' => Setting::get('wallet_minimum_payout_amount', 5),
            'auto_release_enabled' => Setting::get('wallet_auto_release_enabled', true),
        ];
        
        // Liste des devises courantes
        $currencies = [
            'USD' => 'USD - Dollar américain',
            'EUR' => 'EUR - Euro',
            'CDF' => 'CDF - Franc congolais',
            'XOF' => 'XOF - Franc CFA (BCEAO)',
            'XAF' => 'XAF - Franc CFA (BEAC)',
            'RWF' => 'RWF - Franc rwandais',
            'KES' => 'KES - Shilling kenyan',
            'UGX' => 'UGX - Shilling ougandais',
            'TZS' => 'TZS - Shilling tanzanien',
            'GHS' => 'GHS - Cedi ghanéen',
            'NGN' => 'NGN - Naira nigérian',
            'ZAR' => 'ZAR - Rand sud-africain',
        ];
        
        $metaPixels = \App\Models\MetaPixel::query()
            ->orderByDesc('priority')
            ->orderByDesc('id')
            ->get();
        $metaEvents = \App\Models\MetaEvent::query()->orderBy('event_name')->get();
        $metaTriggers = \App\Models\MetaEventTrigger::query()
            ->with(['event:id,event_name'])
            ->orderByDesc('priority')
            ->orderByDesc('id')
            ->get();

        // Pages (routes GET sans paramètres) pour choisir une page dans les triggers
        $metaPageOptions = collect(\Illuminate\Support\Facades\Route::getRoutes())
            ->filter(function ($r) {
                try {
                    $methods = $r->methods();
                    $uri = ltrim((string) $r->uri(), '/');

                    if (!in_array('GET', $methods, true)) {
                        return false;
                    }
                    if ($uri !== '' && (str_starts_with($uri, 'admin') || str_starts_with($uri, 'api'))) {
                        return false;
                    }
                    if (str_contains($uri, '{')) {
                        return false;
                    }

                    return true;
                } catch (\Throwable $e) {
                    return false;
                }
            })
            ->map(function ($r) {
                $name = $r->getName();
                $rawUri = (string) $r->uri();
                $path = '/' . ltrim($rawUri, '/');
                if ($path === '/') {
                    $label = $name ? ($name . ' — /') : '/';
                } else {
                    $label = $name ? ($name . ' — ' . $path) : $path;
                }

                return [
                    'path' => $path,
                    'label' => $label,
                ];
            })
            ->unique('path')
            ->sortBy('label')
            ->values()
            ->all();

        $metaEventNameOptions = collect([
            'PageView',
            'ViewContent',
            'Search',
            'AddToCart',
            'InitiateCheckout',
            'AddPaymentInfo',
            'Purchase',
            'Lead',
            'CompleteRegistration',
            'Contact',
            'Subscribe',
            'StartTrial',
            'Schedule',
            'Donate',
        ])->merge($metaEvents->pluck('event_name'))
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();

        $metaStandardEventNameOptions = [
            'PageView',
            'ViewContent',
            'Search',
            'AddToCart',
            'InitiateCheckout',
            'AddPaymentInfo',
            'Purchase',
            'Lead',
            'CompleteRegistration',
            'Contact',
            'Subscribe',
            'StartTrial',
            'Schedule',
            'Donate',
        ];

        return view('admin.settings.index', compact(
            'baseCurrency',
            'currencies',
            'settings',
            'commissionPercentage',
            'walletSettings',
            'metaTrackingEnabled',
            'metaPixels',
            'metaEvents',
            'metaTriggers',
            'metaPageOptions',
            'metaEventNameOptions',
            'metaStandardEventNameOptions',
        ));
    }

    /**
     * Mettre à jour les paramètres
     */
    public function updateSettings(Request $request)
    {
        // Gestion Meta (Pixel + Events) via /admin/settings (sans dépendre des autres settings)
        if ($request->filled('meta_action')) {
            $action = $request->string('meta_action')->toString();

            $metaStandardEventNames = [
                'PageView',
                'ViewContent',
                'Search',
                'AddToCart',
                'InitiateCheckout',
                'AddPaymentInfo',
                'Purchase',
                'Lead',
                'CompleteRegistration',
                'Contact',
                'Subscribe',
                'StartTrial',
                'Schedule',
                'Donate',
            ];
            $metaStandardEventNamesLowerMap = collect($metaStandardEventNames)
                ->mapWithKeys(fn (string $n) => [strtolower($n) => $n])
                ->all();

            $normalizeEventName = function (?string $raw) use ($metaStandardEventNamesLowerMap): string {
                $raw = trim((string) $raw);
                if ($raw === '') {
                    return '';
                }
                $lower = strtolower($raw);
                return $metaStandardEventNamesLowerMap[$lower] ?? $raw;
            };

            $isValidCustomEventName = function (string $name): bool {
                if ($name === '') return false;
                if (strlen($name) > 64) return false;
                // Pas d'espaces ni virgules: c'est une valeur unique, pas une liste.
                if (preg_match('/[,\s]/', $name)) return false;
                // Autoriser lettres, chiffres, ".", "_", "-"
                return (bool) preg_match('/^[A-Za-z0-9._-]+$/', $name);
            };

            $parseCsv = function ($raw): array {
                if (!is_string($raw)) {
                    return [];
                }
                $parts = preg_split('/[,\n\r]+/', $raw) ?: [];
                $out = [];
                foreach ($parts as $p) {
                    $p = trim($p);
                    if ($p === '') continue;
                    $out[] = $p;
                }
                return array_values(array_unique($out));
            };

            $parseJson = function ($raw): ?array {
                if (!is_string($raw)) {
                    return null;
                }
                $raw = trim($raw);
                if ($raw === '') {
                    return null;
                }
                $decoded = json_decode($raw, true);
                return is_array($decoded) ? $decoded : null;
            };

            if ($action === 'meta_update_global') {
                Setting::set(
                    'meta_tracking_enabled',
                    $request->input('meta_tracking_enabled') === 'on' ? 1 : 0,
                    'boolean',
                    'Activer le tracking Meta (Facebook Pixel) globalement'
                );

                Setting::set(
                    'meta_capi_enabled',
                    $request->input('meta_capi_enabled') === 'on' ? 1 : 0,
                    'boolean',
                    'Activer Meta Conversions API (CAPI) pour déduplication et fiabilité'
                );

                // Token CAPI (peut être vide si CAPI off)
                $token = (string) $request->input('meta_capi_access_token', '');
                Setting::set(
                    'meta_capi_access_token',
                    $token,
                    'string',
                    'CAPI Access Token (Graph API) — à garder privé'
                );

                // Test event code (optionnel)
                $testCode = (string) $request->input('meta_capi_test_event_code', '');
                Setting::set(
                    'meta_capi_test_event_code',
                    $testCode,
                    'string',
                    'CAPI Test Event Code (optionnel, Events Manager)'
                );

                return redirect()->route('admin.settings')->with('success', 'Paramètres Meta mis à jour.');
            }

            if ($action === 'meta_pixel_create') {
                $request->validate([
                    'pixel_id' => 'required|string|max:64',
                    'pixel_name' => 'nullable|string|max:255',
                ]);

                \App\Models\MetaPixel::create([
                    'pixel_id' => trim($request->input('pixel_id')),
                    'name' => $request->input('pixel_name'),
                    'is_active' => $request->input('pixel_is_active') === 'on',
                ]);

                return redirect()
                    ->route('admin.settings', ['open' => 'triggers'])
                    ->with('success', 'Pixel Meta ajouté.');
            }

            if ($action === 'meta_pixel_update') {
                $request->validate([
                    'meta_pixel_id' => 'required|integer|exists:meta_pixels,id',
                    'pixel_id' => 'required|string|max:64',
                    'pixel_name' => 'nullable|string|max:255',
                ]);

                $pixel = \App\Models\MetaPixel::query()->findOrFail((int) $request->input('meta_pixel_id'));
                $pixel->fill([
                    'pixel_id' => trim($request->input('pixel_id')),
                    'name' => $request->input('pixel_name'),
                    'is_active' => $request->input('pixel_is_active') === 'on',
                ]);
                $pixel->save();

                return redirect()->route('admin.settings')->with('success', 'Pixel Meta mis à jour.');
            }

            if ($action === 'meta_pixel_delete') {
                $request->validate(['meta_pixel_id' => 'required|integer']);
                \App\Models\MetaPixel::query()->whereKey((int) $request->input('meta_pixel_id'))->delete();
                return redirect()->route('admin.settings')->with('success', 'Pixel Meta supprimé.');
            }

            if ($action === 'meta_event_create') {
                $request->validate([
                    'event_name' => 'required|string|max:64',
                    'event_description' => 'nullable|string|max:1000',
                ]);

                $eventName = $normalizeEventName($request->input('event_name'));
                $isStandard = $request->input('event_is_standard') === 'on';

                // Validation stricte: standard => whitelist, custom => format strict
                if ($isStandard) {
                    if (!in_array($eventName, $metaStandardEventNames, true)) {
                        return redirect()->route('admin.settings')->with('error', "Nom d'événement standard invalide. Utilisez un événement Meta officiel (ex: Purchase, Lead...).");
                    }
                } else {
                    if (!$isValidCustomEventName($eventName)) {
                        return redirect()->route('admin.settings')->with('error', "Nom d'événement custom invalide. Caractères autorisés: lettres/chiffres/._- (sans espaces).");
                    }
                    // Éviter de créer un "custom" avec un nom de standard (typo/erreur d'intention)
                    if (in_array($eventName, $metaStandardEventNames, true)) {
                        return redirect()->route('admin.settings')->with('error', "Cet événement est un événement Meta standard. Cochez 'Standard' pour l'utiliser.");
                    }
                }

                \App\Models\MetaEvent::create([
                    'event_name' => $eventName,
                    'is_standard' => $isStandard,
                    'is_active' => $request->input('event_is_active') === 'on',
                    'default_payload' => $parseJson((string) $request->input('event_default_payload')) ?? [],
                    'description' => $request->input('event_description'),
                ]);

                return redirect()
                    ->route('admin.settings', ['open' => 'triggers'])
                    ->with('success', 'Événement Meta ajouté.');
            }

            if ($action === 'meta_event_update') {
                $request->validate([
                    'meta_event_id' => 'required|integer|exists:meta_events,id',
                    'event_name' => 'required|string|max:64',
                    'event_description' => 'nullable|string|max:1000',
                ]);

                $eventName = $normalizeEventName($request->input('event_name'));
                $isStandard = $request->input('event_is_standard') === 'on';

                if ($isStandard) {
                    if (!in_array($eventName, $metaStandardEventNames, true)) {
                        return redirect()->route('admin.settings')->with('error', "Nom d'événement standard invalide. Utilisez un événement Meta officiel (ex: Purchase, Lead...).");
                    }
                } else {
                    if (!$isValidCustomEventName($eventName)) {
                        return redirect()->route('admin.settings')->with('error', "Nom d'événement custom invalide. Caractères autorisés: lettres/chiffres/._- (sans espaces).");
                    }
                    if (in_array($eventName, $metaStandardEventNames, true)) {
                        return redirect()->route('admin.settings')->with('error', "Cet événement est un événement Meta standard. Cochez 'Standard' pour l'utiliser.");
                    }
                }

                $event = \App\Models\MetaEvent::query()->findOrFail((int) $request->input('meta_event_id'));
                $event->fill([
                    'event_name' => $eventName,
                    'is_standard' => $isStandard,
                    'is_active' => $request->input('event_is_active') === 'on',
                    'default_payload' => $parseJson((string) $request->input('event_default_payload')) ?? [],
                    'description' => $request->input('event_description'),
                ]);
                $event->save();

                return redirect()->route('admin.settings')->with('success', 'Événement Meta mis à jour.');
            }

            if ($action === 'meta_event_delete') {
                $request->validate(['meta_event_id' => 'required|integer']);
                \App\Models\MetaEvent::query()->whereKey((int) $request->input('meta_event_id'))->delete();
                return redirect()->route('admin.settings')->with('success', 'Événement Meta supprimé.');
            }

            if ($action === 'meta_trigger_create') {
                $request->validate([
                    'meta_event_id' => 'nullable|integer|exists:meta_events,id',
                    'event_name' => 'required_without:meta_event_id|string|max:64',
                    'trigger_type' => 'required|string|in:page_load,click,form_submit',
                    'css_selector' => 'nullable|string|max:255',
                    'match_path_pattern' => 'nullable|string|max:255',
                ]);

                $triggerType = $request->input('trigger_type');
                if ($triggerType === 'page_load' && !$request->filled('match_path_pattern')) {
                    return redirect()->route('admin.settings')->with('error', 'Pour page_load, la sélection de page est obligatoire (choisissez une page ou “Toutes les pages”).');
                }
                if (in_array($triggerType, ['click', 'form_submit'], true) && !$request->filled('css_selector')) {
                    return redirect()->route('admin.settings')->with('error', 'Le sélecteur CSS est obligatoire pour click/form_submit.');
                }

                $matchPathPattern = trim((string) $request->input('match_path_pattern', ''));
                if ($matchPathPattern === '__all__') {
                    $matchPathPattern = '';
                }

                $metaEventId = $request->input('meta_event_id');
                if (!$metaEventId) {
                    $eventName = $normalizeEventName($request->input('event_name'));
                    if ($eventName === '') {
                        return redirect()->route('admin.settings')->with('error', 'Veuillez sélectionner un événement.');
                    }

                    // Triggers: uniquement des événements existants en BDD (créés via section "Événements")
                    $existing = \App\Models\MetaEvent::query()->where('event_name', $eventName)->first();
                    if (!$existing) {
                        return redirect()->route('admin.settings')->with('error', "Événement inconnu. Créez d'abord l'événement dans la section “Événements”, puis sélectionnez-le.");
                    }
                    $metaEventId = $existing->id;
                }

                \App\Models\MetaEventTrigger::create([
                    'meta_event_id' => (int) $metaEventId,
                    'trigger_type' => $triggerType,
                    'css_selector' => $request->input('css_selector'),
                    'match_path_pattern' => $matchPathPattern !== '' ? $matchPathPattern : null,
                    'pixel_ids' => $parseCsv((string) $request->input('trigger_pixel_ids')),
                    'payload' => $parseJson((string) $request->input('trigger_payload')) ?? [],
                    'is_active' => $request->input('trigger_is_active') === 'on',
                    'once_per_page' => $request->input('once_per_page') === 'on',
                ]);

                return redirect()->route('admin.settings')->with('success', 'Trigger Meta ajouté.');
            }

            if ($action === 'meta_trigger_update') {
                $request->validate([
                    'meta_trigger_id' => 'required|integer|exists:meta_event_triggers,id',
                    'meta_event_id' => 'nullable|integer|exists:meta_events,id',
                    'event_name' => 'required_without:meta_event_id|string|max:64',
                    'trigger_type' => 'required|string|in:page_load,click,form_submit',
                    'css_selector' => 'nullable|string|max:255',
                    'match_path_pattern' => 'nullable|string|max:255',
                ]);

                $triggerType = $request->input('trigger_type');
                if ($triggerType === 'page_load' && !$request->filled('match_path_pattern')) {
                    return redirect()->route('admin.settings')->with('error', 'Pour page_load, la sélection de page est obligatoire (choisissez une page ou “Toutes les pages”).');
                }
                if (in_array($triggerType, ['click', 'form_submit'], true) && !$request->filled('css_selector')) {
                    return redirect()->route('admin.settings')->with('error', 'Le sélecteur CSS est obligatoire pour click/form_submit.');
                }

                $trigger = \App\Models\MetaEventTrigger::query()->findOrFail((int) $request->input('meta_trigger_id'));

                $matchPathPattern = trim((string) $request->input('match_path_pattern', ''));
                if ($matchPathPattern === '__all__') {
                    $matchPathPattern = '';
                }

                $metaEventId = $request->input('meta_event_id');
                if (!$metaEventId) {
                    $eventName = $normalizeEventName($request->input('event_name'));
                    if ($eventName === '') {
                        return redirect()->route('admin.settings')->with('error', 'Veuillez sélectionner un événement.');
                    }

                    $existing = \App\Models\MetaEvent::query()->where('event_name', $eventName)->first();
                    if (!$existing) {
                        return redirect()->route('admin.settings')->with('error', "Événement inconnu. Créez d'abord l'événement dans la section “Événements”, puis sélectionnez-le.");
                    }
                    $metaEventId = $existing->id;
                }

                $trigger->fill([
                    'meta_event_id' => (int) $metaEventId,
                    'trigger_type' => $triggerType,
                    'css_selector' => $request->input('css_selector'),
                    'match_path_pattern' => $matchPathPattern !== '' ? $matchPathPattern : null,
                    'pixel_ids' => $parseCsv((string) $request->input('trigger_pixel_ids')),
                    'payload' => $parseJson((string) $request->input('trigger_payload')) ?? [],
                    'is_active' => $request->input('trigger_is_active') === 'on',
                    'once_per_page' => $request->input('once_per_page') === 'on',
                ]);
                $trigger->save();

                return redirect()->route('admin.settings')->with('success', 'Trigger Meta mis à jour.');
            }

            if ($action === 'meta_trigger_delete') {
                $request->validate(['meta_trigger_id' => 'required|integer']);
                \App\Models\MetaEventTrigger::query()->whereKey((int) $request->input('meta_trigger_id'))->delete();
                return redirect()->route('admin.settings')->with('success', 'Trigger Meta supprimé.');
            }

            return redirect()->route('admin.settings')->with('error', 'Action Meta inconnue.');
        }

        $request->validate([
            'base_currency' => 'required|string|size:3|uppercase',
            'external_provider_commission_percentage' => 'nullable|numeric|min:0|max:100',
            'ambassador_commission_rate' => 'nullable|numeric|min:0|max:100',
            // Paramètres Wallet
            'wallet_holding_period_days' => 'nullable|integer|min:0|max:365',
            'wallet_minimum_payout_amount' => 'nullable|numeric|min:0',
        ]);

        Setting::set('base_currency', strtoupper($request->base_currency), 'string', 'Devise de base du site');
        
        if ($request->has('external_provider_commission_percentage')) {
            Setting::set('external_provider_commission_percentage', $request->external_provider_commission_percentage, 'number', 'Pourcentage de commission retenu sur les paiements aux prestataires externes');
        }

        if ($request->has('ambassador_commission_rate')) {
            Setting::set('ambassador_commission_rate', $request->ambassador_commission_rate, 'number', 'Pourcentage de commission versé aux ambassadeurs sur chaque vente réalisée avec leur code promo');
        }

        // Paramètres Wallet - Toujours sauvegarder, même avec des valeurs par défaut
        Setting::set(
            'wallet_holding_period_days', 
            $request->input('wallet_holding_period_days', 7), 
            'number', 
            'Nombre de jours pendant lesquels les fonds sont bloqués avant d\'être disponibles au retrait'
        );

        Setting::set(
            'wallet_minimum_payout_amount', 
            $request->input('wallet_minimum_payout_amount', 5), 
            'number', 
            'Montant minimum pour effectuer un retrait'
        );

        // Le checkbox renvoie 'on' quand coché, null quand décoché
        Setting::set(
            'wallet_auto_release_enabled', 
            $request->input('wallet_auto_release_enabled') === 'on' ? 1 : 0, 
            'boolean', 
            'Activer la libération automatique des fonds bloqués'
        );

        \Log::info('Paramètres Wallet mis à jour', [
            'wallet_holding_period_days' => $request->input('wallet_holding_period_days'),
            'wallet_minimum_payout_amount' => $request->input('wallet_minimum_payout_amount'),
            'wallet_auto_release_enabled' => $request->input('wallet_auto_release_enabled'),
        ]);

        return redirect()->route('admin.settings')
            ->with('success', 'Paramètres mis à jour avec succès.');
    }

    /**
     * Afficher la liste des payouts aux prestataires externes
     */
    public function providerPayouts(Request $request)
    {
        $query = ProviderPayout::with(['provider', 'order', 'course']);

        // Filtre par statut
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        // Filtre par prestataire
        if ($request->input('provider_id')) {
            $query->where('provider_id', $request->input('provider_id'));
        }

        // Recherche par payout_id ou order_number
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('payout_id', 'like', "%{$search}%")
                  ->orWhereHas('order', function($orderQuery) use ($search) {
                      $orderQuery->where('order_number', 'like', "%{$search}%");
                  });
            });
        }

        // Tri
        $sortBy = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        
        if (in_array($sortBy, ['amount', 'status', 'created_at', 'processed_at'])) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->latest();
        }

        $payouts = $query->paginate(20)->withQueryString();

        // Statistiques
        $stats = [
            'total' => ProviderPayout::count(),
            'pending' => ProviderPayout::where('status', 'pending')->count(),
            'processing' => ProviderPayout::where('status', 'processing')->count(),
            'completed' => ProviderPayout::where('status', 'completed')->count(),
            'failed' => ProviderPayout::where('status', 'failed')->count(),
            'total_amount' => ProviderPayout::where('status', 'completed')->sum('amount'),
            'total_commission' => ProviderPayout::where('status', 'completed')->sum('commission_amount'),
        ];

        // Liste des prestataires externes pour le filtre
        $providers = User::where('is_external_provider', true)
            ->where('role', 'provider')
            ->get();

        return view('admin.provider-payouts.index', compact('payouts', 'stats', 'providers'));
    }

    /**
     * Extraire l'ID vidéo YouTube depuis une URL ou un ID
     */
    private function extractYouTubeVideoId($url): ?string
    {
        if (empty($url)) {
            return null;
        }

        // Si c'est déjà un ID simple (11 caractères alphanumériques)
        if (preg_match('/^[a-zA-Z0-9_-]{11}$/', $url)) {
            return $url;
        }

        // Extraire depuis différentes formes d'URL YouTube
        $patterns = [
            // https://www.youtube.com/watch?v=dQw4w9WgXcQ
            '/[?&]v=([a-zA-Z0-9_-]{11})/',
            // https://youtu.be/dQw4w9WgXcQ
            '/youtu\.be\/([a-zA-Z0-9_-]{11})/',
            // https://www.youtube.com/embed/dQw4w9WgXcQ
            '/youtube\.com\/embed\/([a-zA-Z0-9_-]{11})/',
            // https://www.youtube.com/v/dQw4w9WgXcQ
            '/youtube\.com\/v\/([a-zA-Z0-9_-]{11})/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * Gérer les candidatures prestataire
     */
    public function providerApplications(Request $request)
    {
        $tab = $request->get('tab', 'providers'); // Par défaut: prestataires

        // Tab: Prestataires
        if ($tab === 'providers') {
            // Récupérer tous les prestataires (avec ou sans candidature)
            $providersQuery = User::where('role', 'provider')
                ->with(['providerApplication.reviewer']);

            // Recherche par nom ou email
            if ($request->filled('search')) {
                $search = $request->get('search');
                $providersQuery->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            $allProviders = $providersQuery->get();

            // Créer une collection combinée de candidatures réelles et prestataires sans candidature
            $combinedApplications = collect();

            foreach ($allProviders as $provider) {
                if ($provider->providerApplication) {
                    // Prestataire avec candidature - utiliser la candidature réelle
                    $combinedApplications->push($provider->providerApplication);
                } else {
                    // Prestataire nommé directement par admin - créer un objet virtuel
                    $virtualApplication = new ProviderApplication();
                    $virtualApplication->id = 'virtual_' . $provider->id;
                    $virtualApplication->user_id = $provider->id;
                    $virtualApplication->user = $provider;
                    $virtualApplication->status = 'approved'; // Les prestataires nommés sont considérés comme approuvés
                    $virtualApplication->created_at = $provider->created_at;
                    $virtualApplication->reviewed_at = $provider->created_at;
                    $virtualApplication->reviewed_by = null;
                    $virtualApplication->reviewer = null;
                    $virtualApplication->is_virtual = true; // Marqueur pour identifier les candidatures virtuelles
                    $combinedApplications->push($virtualApplication);
                }
            }

            // Filtre par statut
            if ($request->filled('status')) {
                $status = $request->get('status');
                $combinedApplications = $combinedApplications->filter(function($app) use ($status) {
                    return $app->status === $status;
                });
            }

            // Tri
            $sortBy = $request->get('sort', 'created_at');
            $sortDirection = $request->get('direction', 'desc');
            
            $combinedApplications = $combinedApplications->sort(function($a, $b) use ($sortBy, $sortDirection) {
                $valueA = match($sortBy) {
                    'created_at' => $a->created_at?->timestamp ?? 0,
                    'status' => $a->status ?? '',
                    'reviewed_at' => $a->reviewed_at?->timestamp ?? 0,
                    default => $a->created_at?->timestamp ?? 0,
                };
                
                $valueB = match($sortBy) {
                    'created_at' => $b->created_at?->timestamp ?? 0,
                    'status' => $b->status ?? '',
                    'reviewed_at' => $b->reviewed_at?->timestamp ?? 0,
                    default => $b->created_at?->timestamp ?? 0,
                };
                
                if ($sortDirection === 'asc') {
                    return $valueA <=> $valueB;
                } else {
                    return $valueB <=> $valueA;
                }
            })->values();

            // Pagination manuelle
            $page = $request->get('page', 1);
            $perPage = 20;
            $total = $combinedApplications->count();
            $items = $combinedApplications->slice(($page - 1) * $perPage, $perPage)->values();
            
            $applications = new \Illuminate\Pagination\LengthAwarePaginator(
                $items,
                $total,
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );

            // Statistiques (incluant les prestataires nommés directement)
            $stats = [
                'total' => $allProviders->count(),
                'pending' => $combinedApplications->where('status', 'pending')->count(),
                'under_review' => $combinedApplications->where('status', 'under_review')->count(),
                'approved' => $combinedApplications->where('status', 'approved')->count(),
                'rejected' => $combinedApplications->where('status', 'rejected')->count(),
            ];

            return view('admin.provider-applications.index', compact('applications', 'stats', 'tab'));
        }

        // Tab: Paiements (intégration de providerPayouts)
        if ($tab === 'payouts') {
            $payoutQuery = ProviderPayout::with(['provider', 'order', 'course']);

            // Filtre par statut
            if ($request->filled('status')) {
                $payoutQuery->where('status', $request->get('status'));
            }

            // Filtre par prestataire
            if ($request->input('provider_id')) {
                $payoutQuery->where('provider_id', $request->input('provider_id'));
            }

            // Recherche par payout_id ou order_number
            if ($request->filled('search')) {
                $search = $request->get('search');
                $payoutQuery->where(function($q) use ($search) {
                    $q->where('payout_id', 'like', "%{$search}%")
                      ->orWhereHas('order', function($orderQuery) use ($search) {
                          $orderQuery->where('order_number', 'like', "%{$search}%");
                      });
                });
            }

            // Tri
            $sortBy = $request->get('sort', 'created_at');
            $sortDirection = $request->get('direction', 'desc');
            
            if (in_array($sortBy, ['amount', 'status', 'created_at', 'processed_at'])) {
                $payoutQuery->orderBy($sortBy, $sortDirection);
            } else {
                $payoutQuery->latest();
            }

            $payouts = $payoutQuery->paginate(20)->withQueryString();

            // Statistiques
            $payoutStats = [
                'total' => ProviderPayout::count(),
                'pending' => ProviderPayout::where('status', 'pending')->count(),
                'processing' => ProviderPayout::where('status', 'processing')->count(),
                'completed' => ProviderPayout::where('status', 'completed')->count(),
                'failed' => ProviderPayout::where('status', 'failed')->count(),
                'total_amount' => ProviderPayout::where('status', 'completed')->sum('amount'),
                'total_commission' => ProviderPayout::where('status', 'completed')->sum('commission_amount'),
            ];

            // Liste des prestataires externes pour le filtre
            $providers = User::where('is_external_provider', true)
                ->where('role', 'provider')
                ->get();

            return view('admin.provider-applications.index', compact('payouts', 'payoutStats', 'providers', 'tab'));
        }

        // Tab: Candidatures
        if ($tab === 'applications') {
            $applicationsQuery = ProviderApplication::with(['user', 'reviewer']);

            // Recherche par nom ou email
            if ($request->filled('search')) {
                $search = $request->get('search');
                $applicationsQuery->whereHas('user', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            // Filtre par statut
            if ($request->filled('status')) {
                $applicationsQuery->where('status', $request->get('status'));
            }

            // Tri
            $sortBy = $request->get('sort', 'created_at');
            $sortDirection = $request->get('direction', 'desc');
            
            if (in_array($sortBy, ['created_at', 'status', 'reviewed_at'])) {
                $applicationsQuery->orderBy($sortBy, $sortDirection);
            } else {
                $applicationsQuery->latest();
            }

            $applications = $applicationsQuery->paginate(20)->withQueryString();

            // Statistiques
            $applicationStats = [
                'total' => ProviderApplication::count(),
                'pending' => ProviderApplication::where('status', 'pending')->count(),
                'under_review' => ProviderApplication::where('status', 'under_review')->count(),
                'approved' => ProviderApplication::where('status', 'approved')->count(),
                'rejected' => ProviderApplication::where('status', 'rejected')->count(),
            ];

            return view('admin.provider-applications.index', compact('applications', 'applicationStats', 'tab'));
        }

        // Par défaut, retourner le tab prestataires
        return redirect()->route('admin.provider-applications', ['tab' => 'providers']);
    }

    /**
     * Afficher une candidature
     */
    public function showProviderApplication(ProviderApplication $application)
    {
        $application->load(['user', 'reviewer']);
            return view('admin.provider-applications.show', compact('application'));
    }

    /**
     * Mettre à jour le statut d'une candidature
     */
    public function updateProviderApplicationStatus(Request $request, ProviderApplication $application)
    {
        $request->validate([
            'status' => 'required|in:pending,under_review,approved,rejected',
            'admin_notes' => 'nullable|string|max:2000',
        ]);

        $application->update([
            'status' => $request->status,
            'admin_notes' => $request->admin_notes,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        // Si approuvée, changer le rôle de l'utilisateur
        if ($request->status === 'approved') {
            $application->user->update([
                'role' => 'provider'
            ]);
        }

        if ($application->relationLoaded('user') === false) {
            $application->load('user');
        }

        if ($application->user) {
            // Utiliser sendNow() pour envoyer immédiatement sans passer par la queue
            Notification::sendNow($application->user, new ProviderApplicationStatusUpdated($application));
        }

        return redirect()->route('admin.provider-applications.show', $application)
            ->with('success', 'Statut de la candidature mis à jour avec succès.');
    }

    /**
     * Actions en lot sur les candidatures de prestataires
     */
    public function bulkActionProviderApplications(Request $request)
    {
        $actions = [
            'delete' => function($ids) {
                $count = 0;
                foreach ($ids as $id) {
                    // Ignorer les candidatures virtuelles (prestataires nommés directement)
                    if (str_starts_with($id, 'virtual_')) {
                        continue;
                    }
                    
                    $application = ProviderApplication::find($id);
                    if ($application) {
                        $application->delete();
                        $count++;
                    }
                }
                return [
                    'message' => "{$count} candidature(s) supprimée(s) avec succès.",
                    'count' => $count
                ];
            }
        ];

        return $this->handleBulkAction($request, ProviderApplication::class, $actions);
    }

    private function normalizeStringArray($values): array
    {
        if (!is_array($values)) {
            return [];
        }

        return collect($values)
            ->map(function ($value) {
                if (is_string($value) || is_numeric($value)) {
                    $trimmed = trim((string) $value);
                    return $trimmed === '' ? null : $trimmed;
                }
                return null;
            })
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function normalizeTags($tags): array
    {
        if (is_array($tags)) {
            return $this->normalizeStringArray($tags);
        }

        if (is_string($tags)) {
            $chunks = preg_split('/[,;]+/', $tags) ?: [];
            return $this->normalizeStringArray($chunks);
        }

        return [];
    }

    private function normalizeNullableString(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }

    private function normalizeCommaSeparatedString(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $items = preg_split('/[,;]+/', $value) ?: [];
        $normalized = $this->normalizeStringArray($items);

        return empty($normalized) ? null : implode(', ', $normalized);
    }

    private function sanitizeUploadedPath(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        $clean = trim($path);

        if ($clean === '') {
            return null;
        }

        $clean = str_replace('..', '', $clean);
        $clean = ltrim($clean, '/');

        if (str_starts_with($clean, 'storage/')) {
            $clean = ltrim(substr($clean, strlen('storage/')), '/');
        }

        $allowedPrefixes = [
            FileUploadService::TEMPORARY_BASE_PATH,
            'courses/thumbnails',
            'courses/previews',
            'courses/lessons',
            'courses/downloads',
        ];

        foreach ($allowedPrefixes as $prefix) {
            $normalized = rtrim($prefix, '/');
            if ($clean === $normalized || str_starts_with($clean, $normalized . '/')) {
                return $clean;
            }
        }

        return null;
    }

    /**
     * Afficher la liste des avis (reviews)
     */
    public function reviews(Request $request)
    {
        $query = Review::with(['user', 'course.provider', 'course.category']);

        // Recherche par nom d'utilisateur, titre de cours ou commentaire
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('comment', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                               ->orWhere('email', 'like', "%{$search}%");
                  })
                  ->orWhereHas('course', function ($courseQuery) use ($search) {
                      $courseQuery->where('title', 'like', "%{$search}%");
                  });
            });
        }

        // Filtre par statut (approuvé/en attente)
        if ($request->filled('status')) {
            $status = $request->get('status');
            if ($status === 'approved') {
                $query->where('is_approved', true);
            } elseif ($status === 'pending') {
                $query->where('is_approved', false);
            }
        }

        // Filtre par note (étoiles)
        if ($request->filled('rating')) {
            $rating = $request->get('rating');
            $query->where('rating', $rating);
        }

        // Filtre par cours
        if ($request->filled('content_id')) {
            $query->where('content_id', $request->get('content_id'));
        }

        // Tri
        $sortBy = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);

        $reviews = $query->paginate(15)->withQueryString();

        // Statistiques
        $stats = [
            'total' => Review::count(),
            'approved' => Review::where('is_approved', true)->count(),
            'pending' => Review::where('is_approved', false)->count(),
            'average_rating' => Review::where('is_approved', true)->avg('rating'),
            'by_rating' => Review::where('is_approved', true)
                ->selectRaw('rating, COUNT(*) as count')
                ->groupBy('rating')
                ->orderBy('rating', 'desc')
                ->get(),
        ];

        // Liste des cours pour le filtre
        $courses = Course::published()
            ->orderBy('title')
            ->get(['id', 'title']);

        return view('admin.reviews.index', compact('reviews', 'stats', 'courses'));
    }

    /**
     * Approuver un avis
     */
    public function approveReview(Review $review)
    {
        $review->update(['is_approved' => true]);

        // Recalculer la note moyenne du cours
        $this->recalculateCourseRating($review->content_id);

        return redirect()->route('admin.reviews')
            ->with('success', 'Avis approuvé avec succès.');
    }

    /**
     * Rejeter un avis (désapprouver)
     */
    public function rejectReview(Review $review)
    {
        $review->update(['is_approved' => false]);

        // Recalculer la note moyenne du cours
        $this->recalculateCourseRating($review->content_id);

        return redirect()->route('admin.reviews')
            ->with('success', 'Avis rejeté avec succès.');
    }

    /**
     * Supprimer un avis
     */
    public function deleteReview(Review $review)
    {
        $contentId = $review->content_id;
        $review->delete();

        // Recalculer la note moyenne du cours
        $this->recalculateCourseRating($contentId);

        return redirect()->route('admin.reviews')
            ->with('success', 'Avis supprimé avec succès.');
    }

    /**
     * Recalculer la note moyenne et le nombre d'avis d'un cours
     */
    private function recalculateCourseRating($contentId)
    {
        $course = Course::find($contentId);
        if (!$course) {
            return;
        }

        $approvedReviews = $course->reviews()->approved()->get();

        $course->update([
            'rating' => round($approvedReviews->avg('rating') ?? 0, 2),
            'reviews_count' => $approvedReviews->count(),
        ]);
    }

    // Gestion des certificats
    public function certificates(Request $request)
    {
        $query = Certificate::with(['user', 'course.provider', 'course.category']);

        // Recherche par nom d'utilisateur, titre de cours, numéro de certificat
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('certificate_number', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                               ->orWhere('email', 'like', "%{$search}%");
                  })
                  ->orWhereHas('course', function ($courseQuery) use ($search) {
                      $courseQuery->where('title', 'like', "%{$search}%");
                  });
            });
        }

        // Filtre par utilisateur
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->get('user_id'));
        }

        // Filtre par cours
        if ($request->filled('content_id')) {
            $query->where('content_id', $request->get('content_id'));
        }

        // Tri
        $sortBy = $request->get('sort', 'issued_at');
        $sortDirection = $request->get('direction', 'desc');
        
        if (in_array($sortBy, ['certificate_number', 'title', 'issued_at', 'created_at'])) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->orderBy('issued_at', 'desc');
        }

        $certificates = $query->paginate(15)->withQueryString();

        // Statistiques
        $stats = [
            'total' => Certificate::count(),
            'this_month' => Certificate::whereMonth('issued_at', now()->month)
                ->whereYear('issued_at', now()->year)
                ->count(),
            'this_year' => Certificate::whereYear('issued_at', now()->year)->count(),
        ];

        // Liste des cours pour le filtre
        $courses = Course::published()
            ->orderBy('title')
            ->get(['id', 'title']);

        // Liste des utilisateurs pour le filtre (avec certificats)
        $users = \App\Models\User::whereHas('certificates')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return view('admin.certificates.index', compact('certificates', 'stats', 'courses', 'users'));
    }

    /**
     * Afficher les détails d'un certificat
     */
    public function showCertificate(Certificate $certificate)
    {
        $certificate->load(['user', 'course.provider', 'course.category']);
        return view('admin.certificates.show', compact('certificate'));
    }

    /**
     * Télécharger un certificat
     */
    public function downloadCertificate(Certificate $certificate)
    {
        try {
            $certificateService = app(\App\Services\CertificateService::class);
            $pdfContent = $certificateService->getCertificatePdfContent($certificate);
            
            $filename = $certificate->certificate_number . '.pdf';
            
            return response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur lors du téléchargement du certificat', [
                'certificate_id' => $certificate->id,
                'error' => $e->getMessage(),
            ]);
            
            return redirect()->route('admin.certificates')
                ->with('error', 'Erreur lors du téléchargement du certificat: ' . $e->getMessage());
        }
    }

    /**
     * Régénérer un certificat (recréer le PDF)
     */
    public function regenerateCertificate(Certificate $certificate)
    {
        try {
            $certificateService = app(\App\Services\CertificateService::class);
            $certificate = $certificateService->regenerateCertificate($certificate);
            
            return redirect()->route('admin.certificates.show', $certificate)
                ->with('success', "Le certificat {$certificate->certificate_number} a été régénéré avec succès.");
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la régénération du certificat', [
                'certificate_id' => $certificate->id,
                'error' => $e->getMessage(),
            ]);
            
            return redirect()->route('admin.certificates.show', $certificate)
                ->with('error', 'Erreur lors de la régénération du certificat: ' . $e->getMessage());
        }
    }

    /**
     * Supprimer un certificat
     */
    public function destroyCertificate(Certificate $certificate)
    {
        try {
            // Supprimer le fichier PDF si il existe
            if ($certificate->file_path) {
                Storage::disk('public')->delete($certificate->file_path);
            }
            
            $certificateNumber = $certificate->certificate_number;
            $certificate->delete();
            
            return redirect()->route('admin.certificates')
                ->with('success', "Le certificat {$certificateNumber} a été supprimé avec succès.");
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la suppression du certificat', [
                'certificate_id' => $certificate->id,
                'error' => $e->getMessage(),
            ]);
            
            return redirect()->route('admin.certificates')
                ->with('error', 'Erreur lors de la suppression du certificat: ' . $e->getMessage());
        }
    }

    /**
     * Afficher la page d'envoi de message WhatsApp
     */
    public function showSendWhatsApp()
    {
        $whatsappService = app(WhatsAppService::class);
        $connectionStatus = $whatsappService->checkConnection();
        
        return view('admin.announcements.send-whatsapp', compact('connectionStatus'));
    }

    /**
     * Rechercher des utilisateurs pour l'envoi WhatsApp (avec numéro de téléphone)
     */
    public function searchUsersForWhatsApp(Request $request)
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $users = User::where('is_active', true)
            ->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%")
                  ->orWhere('phone', 'like', "%{$query}%");
            })
            ->whereNotNull('phone')
            ->select('id', 'name', 'email', 'phone')
            ->limit(20)
            ->get();

        return response()->json($users);
    }

    /**
     * Compter les utilisateurs avec numéro de téléphone selon les critères
     */
    public function countUsersForWhatsApp(Request $request)
    {
        $type = $request->get('type', 'all');
        
        $query = User::where('is_active', true)->whereNotNull('phone');

        if ($type === 'role') {
            $roles = explode(',', $request->get('roles', ''));
            if (!empty($roles)) {
                // Séparer les rôles normaux des ambassadeurs
                $normalRoles = array_filter($roles, function($role) {
                    return $role !== 'ambassador';
                });
                $hasAmbassador = in_array('ambassador', $roles);
                
                if (!empty($normalRoles) && $hasAmbassador) {
                    // Si on a des rôles normaux ET des ambassadeurs
                    $query->where(function($q) use ($normalRoles, $hasAmbassador) {
                        $q->whereIn('role', $normalRoles);
                        if ($hasAmbassador) {
                            $q->orWhereHas('ambassador');
                        }
                    });
                } elseif (!empty($normalRoles)) {
                    // Seulement des rôles normaux
                    $query->whereIn('role', $normalRoles);
                } elseif ($hasAmbassador) {
                    // Seulement des ambassadeurs
                    $query->whereHas('ambassador');
                }
            }
        } elseif ($type === 'course') {
            $contentId = $request->get('content_id');
            if ($contentId) {
                // Récupérer les utilisateurs inscrits à ce cours
                $query->whereHas('enrollments', function($q) use ($contentId) {
                    $q->where('content_id', $contentId)
                      ->where('status', 'active');
                });
            }
        } elseif ($type === 'category') {
            $categoryId = $request->get('category_id');
            if ($categoryId) {
                // Récupérer les utilisateurs inscrits à des cours de cette catégorie
                $query->whereHas('enrollments', function($q) use ($categoryId) {
                    $q->where('status', 'active')
                      ->whereHas('content', function($courseQuery) use ($categoryId) {
                          $courseQuery->where('category_id', $categoryId)
                                     ->where('is_published', true);
                      });
                });
            }
        } elseif ($type === 'provider') {
            $providerId = $request->input('provider_id');
            if ($providerId) {
                // Récupérer les utilisateurs inscrits à des cours de ce prestataire
                $query->whereHas('enrollments', function($q) use ($providerId) {
                    $q->where('status', 'active')
                      ->whereHas('content', function($courseQuery) use ($providerId) {
                          $courseQuery->where('provider_id', $providerId)
                                     ->where('is_published', true);
                      });
                });
            }
        } elseif ($type === 'registration_date') {
            $dateFrom = $request->get('registration_date_from');
            $dateTo = $request->get('registration_date_to');
            if ($dateFrom || $dateTo) {
                if ($dateFrom) {
                    $query->whereDate('created_at', '>=', $dateFrom);
                }
                if ($dateTo) {
                    $query->whereDate('created_at', '<=', $dateTo);
                }
            }
        } elseif ($type === 'activity') {
            $activityType = $request->get('activity_type');
            if ($activityType) {
                switch ($activityType) {
                    case 'active_recent':
                        $query->where('last_login_at', '>=', now()->subDays(7));
                        break;
                    case 'active_month':
                        $query->where('last_login_at', '>=', now()->startOfMonth());
                        break;
                    case 'active_3months':
                        $query->where('last_login_at', '>=', now()->subMonths(3));
                        break;
                    case 'inactive_30days':
                        $query->where(function($q) {
                            $q->where('last_login_at', '<', now()->subDays(30))
                              ->orWhereNull('last_login_at');
                        });
                        break;
                    case 'inactive_90days':
                        $query->where(function($q) {
                            $q->where('last_login_at', '<', now()->subDays(90))
                              ->orWhereNull('last_login_at');
                        });
                        break;
                    case 'never_logged':
                        $query->whereNull('last_login_at');
                        break;
                }
            }
        }

        $count = $query->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Envoyer un message WhatsApp personnalisé
     */
    public function sendWhatsApp(Request $request)
    {
        $request->validate([
            'recipient_type' => 'required|in:all,role,course,category,provider,registration_date,activity,selected,single',
            'message' => 'required|string|max:4096',
            'send_type' => 'required|in:now',
            'roles' => 'nullable|required_if:recipient_type,role|array',
            'roles.*' => 'in:customer,provider,admin,affiliate,ambassador',
            'content_id' => 'nullable|required_if:recipient_type,course|exists:contents,id',
            'category_id' => 'nullable|required_if:recipient_type,category|exists:categories,id',
            'provider_id' => 'nullable|required_if:recipient_type,provider|exists:users,id',
            'registration_date_from' => 'nullable|required_if:recipient_type,registration_date|date',
            'registration_date_to' => 'nullable|required_if:recipient_type,registration_date|date|after_or_equal:registration_date_from',
            'activity_type' => 'nullable|required_if:recipient_type,activity|in:active_recent,active_month,active_3months,inactive_30days,inactive_90days,never_logged',
            'single_user_id' => 'nullable|required_if:recipient_type,single|exists:users,id',
            'user_ids' => 'nullable|required_if:recipient_type,selected|string',
            'roles' => 'nullable|required_if:recipient_type,role|array',
            'roles.*' => 'in:customer,provider,admin,affiliate,ambassador',
            'single_user_id' => 'nullable|required_if:recipient_type,single|exists:users,id',
            'user_ids' => 'nullable|required_if:recipient_type,selected|string',
        ]);

        $recipientType = $request->recipient_type;
        $message = $request->message;

        // Obtenir les destinataires avec numéro de téléphone
        $users = $this->getWhatsAppRecipients($request);

        if ($users->isEmpty()) {
            return redirect()->back()
                ->with('error', 'Aucun destinataire avec numéro de téléphone trouvé pour cet envoi.')
                ->withInput();
        }

        $whatsappService = app(WhatsAppService::class);
        $sentCount = 0;
        $failedCount = 0;

        // Envoyer immédiatement en lots
        $users->chunk(50)->each(function ($userChunk) use ($message, &$sentCount, &$failedCount, $recipientType, $whatsappService) {
            foreach ($userChunk as $user) {
                try {
                    // Envoyer le message WhatsApp
                    $result = $whatsappService->sendMessage($user->phone, $message);
                    
                    // Enregistrer le message
                    SentWhatsAppMessage::create([
                        'user_id' => $user->id,
                        'recipient_phone' => $user->phone,
                        'recipient_name' => $user->name,
                        'message_id' => $result['message_id'] ?? null,
                        'message' => $message,
                        'type' => 'custom',
                        'status' => $result['success'] ? 'sent' : 'failed',
                        'error_message' => $result['error'] ?? null,
                        'sent_at' => $result['success'] ? now() : null,
                        'metadata' => [
                            'recipient_type' => $recipientType,
                        ],
                    ]);
                    
                    if ($result['success']) {
                        $sentCount++;
                    } else {
                        $failedCount++;
                    }
                } catch (\Exception $e) {
                    \Log::error("Erreur lors de l'envoi WhatsApp à {$user->phone}: " . $e->getMessage());
                    
                    // Enregistrer l'échec
                    SentWhatsAppMessage::create([
                        'user_id' => $user->id,
                        'recipient_phone' => $user->phone,
                        'recipient_name' => $user->name,
                        'message' => $message,
                        'type' => 'custom',
                        'status' => 'failed',
                        'error_message' => $e->getMessage(),
                        'metadata' => [
                            'recipient_type' => $recipientType,
                        ],
                    ]);
                    
                    $failedCount++;
                }
            }
        });

        $messageResult = "Message WhatsApp envoyé avec succès à {$sentCount} destinataire(s).";
        if ($failedCount > 0) {
            $messageResult .= " {$failedCount} envoi(s) ont échoué.";
        }

        // Si c'est une requête AJAX, retourner JSON
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $messageResult,
                'redirect' => route('admin.announcements')
            ]);
        }

        return redirect()->route('admin.announcements')
            ->with('success', $messageResult);
    }

    /**
     * Obtenir les destinataires WhatsApp selon le type sélectionné (avec numéro de téléphone)
     */
    protected function getWhatsAppRecipients(Request $request)
    {
        $type = $request->recipient_type;

        $query = User::where('is_active', true)->whereNotNull('phone');

        switch ($type) {
            case 'all':
                // Tous les utilisateurs actifs avec numéro de téléphone
                break;

            case 'role':
                $roles = $request->input('roles', []);
                if (!empty($roles)) {
                    // Séparer les rôles normaux des ambassadeurs
                    $normalRoles = array_filter($roles, function($role) {
                        return $role !== 'ambassador';
                    });
                    $hasAmbassador = in_array('ambassador', $roles);
                    
                    if (!empty($normalRoles) && $hasAmbassador) {
                        // Si on a des rôles normaux ET des ambassadeurs
                        $query->where(function($q) use ($normalRoles, $hasAmbassador) {
                            $q->whereIn('role', $normalRoles);
                            if ($hasAmbassador) {
                                $q->orWhereHas('ambassador');
                            }
                        });
                    } elseif (!empty($normalRoles)) {
                        // Seulement des rôles normaux
                        $query->whereIn('role', $normalRoles);
                    } elseif ($hasAmbassador) {
                        // Seulement des ambassadeurs
                        $query->whereHas('ambassador');
                    }
                }
                break;

            case 'course':
                $contentId = $request->input('content_id');
                if ($contentId) {
                    $query->whereHas('enrollments', function($q) use ($contentId) {
                        $q->where('content_id', $contentId)->where('status', 'active');
                    });
                } else {
                    return collect();
                }
                break;

            case 'category':
                $categoryId = $request->input('category_id');
                if ($categoryId) {
                    $query->whereHas('enrollments', function($q) use ($categoryId) {
                        $q->where('status', 'active')
                          ->whereHas('content', function($courseQuery) use ($categoryId) {
                              $courseQuery->where('category_id', $categoryId)->where('is_published', true);
                          });
                    });
                } else {
                    return collect();
                }
                break;

            case 'provider':
                $providerId = $request->input('provider_id');
                if ($providerId) {
                    $query->whereHas('enrollments', function($q) use ($providerId) {
                        $q->where('status', 'active')
                          ->whereHas('content', function($courseQuery) use ($providerId) {
                              $courseQuery->where('provider_id', $providerId)->where('is_published', true);
                          });
                    });
                } else {
                    return collect();
                }
                break;

            case 'registration_date':
                $dateFrom = $request->input('registration_date_from');
                $dateTo = $request->input('registration_date_to');
                if ($dateFrom || $dateTo) {
                    if ($dateFrom) $query->whereDate('created_at', '>=', $dateFrom);
                    if ($dateTo) $query->whereDate('created_at', '<=', $dateTo);
                } else {
                    return collect();
                }
                break;

            case 'activity':
                $activityType = $request->input('activity_type');
                if ($activityType) {
                    switch ($activityType) {
                        case 'active_recent': $query->where('last_login_at', '>=', now()->subDays(7)); break;
                        case 'active_month': $query->where('last_login_at', '>=', now()->startOfMonth()); break;
                        case 'active_3months': $query->where('last_login_at', '>=', now()->subMonths(3)); break;
                        case 'inactive_30days': $query->where(function($q) {
                            $q->where('last_login_at', '<', now()->subDays(30))->orWhereNull('last_login_at');
                        }); break;
                        case 'inactive_90days': $query->where(function($q) {
                            $q->where('last_login_at', '<', now()->subDays(90))->orWhereNull('last_login_at');
                        }); break;
                        case 'never_logged': $query->whereNull('last_login_at'); break;
                    }
                } else {
                    return collect();
                }
                break;

            case 'downloaded_free':
                $downloadedContentId = $request->input('downloaded_content_id');
                // Utilisateurs ayant téléchargé au moins une fois un contenu téléchargeable gratuit
                $query->whereHas('downloads', function($q) use ($downloadedContentId) {
                    $q->whereHas('content', function($contentQuery) use ($downloadedContentId) {
                        $contentQuery->where('is_downloadable', true)
                                    ->where('is_free', true)
                                    ->where('is_published', true);
                        if ($downloadedContentId) {
                            $contentQuery->where('id', $downloadedContentId);
                        }
                    });
                });
                break;

            case 'purchased':
                $purchaseType = $request->input('purchase_type', 'any');
                $purchasedContentId = $request->input('purchased_content_id');
                
                if ($purchaseType === 'specific_content' && $purchasedContentId) {
                    // Utilisateurs ayant acheté un contenu spécifique
                    $query->whereHas('orders', function($orderQuery) use ($purchasedContentId) {
                        $orderQuery->whereIn('status', ['paid', 'completed'])
                                   ->whereHas('orderItems', function($itemQuery) use ($purchasedContentId) {
                                       $itemQuery->where('content_id', $purchasedContentId);
                                   });
                    });
                } else {
                    // Utilisateurs ayant effectué des achats selon le type
                    $query->whereHas('orders', function($orderQuery) use ($purchaseType) {
                        if ($purchaseType === 'paid') {
                            $orderQuery->where('status', 'paid');
                        } elseif ($purchaseType === 'completed') {
                            $orderQuery->where('status', 'completed');
                        } else {
                            // 'any' - tous les utilisateurs ayant des commandes payées ou complétées
                            $orderQuery->whereIn('status', ['paid', 'completed']);
                        }
                    });
                }
                break;

            case 'selected':
                $userIdsString = $request->input('user_ids', '');
                if (empty($userIdsString)) {
                    return collect();
                }
                $userIds = array_filter(explode(',', $userIdsString), function($id) {
                    return !empty(trim($id)) && is_numeric(trim($id));
                });
                if (!empty($userIds)) {
                    $query->whereIn('id', array_map('intval', $userIds));
                } else {
                    return collect();
                }
                break;

            case 'single':
                $userId = $request->single_user_id;
                if ($userId) {
                    $query->where('id', $userId);
                } else {
                    return collect();
                }
                break;
        }

        return $query->select('id', 'name', 'email', 'phone')->get();
    }

    /**
     * Voir les détails d'un message WhatsApp envoyé
     */
    public function showWhatsAppMessage(SentWhatsAppMessage $sentWhatsAppMessage)
    {
        $sentWhatsAppMessage->load('user');
        
        // Charger l'utilisateur destinataire si le numéro de téléphone correspond
        if ($sentWhatsAppMessage->recipient_phone) {
            $recipientUser = User::where('phone', $sentWhatsAppMessage->recipient_phone)->first();
            $sentWhatsAppMessage->recipient_user = $recipientUser;
        }
        
        // Traduire le message d'erreur en français si présent
        if ($sentWhatsAppMessage->error_message) {
            $sentWhatsAppMessage->translated_error = $this->translateWhatsAppError($sentWhatsAppMessage->error_message);
        }
        
        return view('admin.whatsapp.show', compact('sentWhatsAppMessage'));
    }

    /**
     * Traduire un message d'erreur WhatsApp en français
     */
    protected function translateWhatsAppError(string $errorMessage): string
    {
        // Nettoyer le message d'erreur (enlever "Erreur :" au début si présent)
        $errorMessage = preg_replace('/^Erreur\s*:\s*/i', '', trim($errorMessage));
        $errorLower = strtolower($errorMessage);
        
        // Traductions des messages d'erreur courants (par ordre de priorité)
        $translations = [
            // Messages HTTP courants (priorité haute)
            'bad request' => 'Requête invalide - Vérifiez les paramètres de la requête',
            'unauthorized' => 'Non autorisé - Vérifiez votre clé API',
            'forbidden' => 'Accès interdit',
            'not found' => 'Ressource introuvable',
            'method not allowed' => 'Méthode non autorisée',
            'request timeout' => 'Délai d\'attente de la requête dépassé',
            'too many requests' => 'Trop de requêtes - Veuillez réessayer plus tard',
            'internal server error' => 'Erreur interne du serveur',
            'bad gateway' => 'Mauvaise passerelle',
            'service unavailable' => 'Service indisponible',
            'gateway timeout' => 'Délai d\'attente de la passerelle dépassé',
            
            // Erreurs de connexion
            'not connected' => 'Instance WhatsApp non connectée',
            'instance not found' => 'Instance WhatsApp introuvable',
            'instance not connected' => 'Instance WhatsApp non connectée',
            'connection timeout' => 'Délai de connexion dépassé',
            'timeout' => 'Délai d\'attente dépassé',
            'connection' => 'Erreur de connexion',
            
            // Erreurs de numéro
            'invalid phone' => 'Numéro de téléphone invalide',
            'invalid number' => 'Numéro de téléphone invalide',
            'phone number' => 'Numéro de téléphone invalide',
            'number not found' => 'Numéro de téléphone introuvable',
            
            // Erreurs d'API
            'api error' => 'Erreur de l\'API WhatsApp',
            'api key' => 'Clé API invalide',
            'server error' => 'Erreur du serveur WhatsApp',
            
            // Erreurs de message
            'message failed' => 'Échec de l\'envoi du message',
            'send failed' => 'Échec de l\'envoi',
            'failed to send' => 'Échec de l\'envoi',
            'unable to send' => 'Impossible d\'envoyer le message',
            
            // Erreurs réseau
            'network error' => 'Erreur réseau',
            'connection refused' => 'Connexion refusée',
            'could not connect' => 'Impossible de se connecter',
            'connection error' => 'Erreur de connexion',
            
            // Erreurs spécifiques Evolution API
            'qr code' => 'Code QR requis - Veuillez scanner le code QR',
            'qr' => 'Code QR requis',
            'authentication' => 'Erreur d\'authentification WhatsApp',
            'session' => 'Session WhatsApp expirée',
            
            // Erreurs génériques
            'unknown error' => 'Erreur inconnue',
            'error occurred' => 'Une erreur s\'est produite',
            'something went wrong' => 'Une erreur s\'est produite',
        ];
        
        // Chercher une correspondance exacte d'abord
        if (isset($translations[$errorLower])) {
            return $translations[$errorLower];
        }
        
        // Chercher une correspondance partielle dans les traductions
        foreach ($translations as $key => $translation) {
            if (stripos($errorLower, $key) !== false) {
                return $translation;
            }
        }
        
        // Si c'est un message d'erreur HTTP avec code numérique, le traduire
        if (preg_match('/\b(\d{3})\b/', $errorMessage, $matches)) {
            $httpCode = (int)$matches[1];
            $httpMessages = [
                400 => 'Requête invalide - Vérifiez les paramètres de la requête',
                401 => 'Non autorisé - Vérifiez votre clé API',
                403 => 'Accès interdit',
                404 => 'Ressource introuvable',
                405 => 'Méthode non autorisée',
                408 => 'Délai d\'attente dépassé',
                429 => 'Trop de requêtes - Veuillez réessayer plus tard',
                500 => 'Erreur interne du serveur',
                502 => 'Mauvaise passerelle',
                503 => 'Service indisponible',
                504 => 'Délai d\'attente de la passerelle dépassé',
            ];
            
            if (isset($httpMessages[$httpCode])) {
                return $httpMessages[$httpCode];
            }
        }
        
        // Retourner le message original si aucune traduction n'est trouvée
        return $errorMessage;
    }

    /**
     * Afficher le formulaire d'envoi combiné (email + WhatsApp)
     */
    public function showSendCombined()
    {
        $whatsappConnectionStatus = app(WhatsAppService::class)->checkConnection();
        return view('admin.announcements.send-combined', compact('whatsappConnectionStatus'));
    }

    /**
     * Envoyer un message combiné (email + WhatsApp) simultanément
     */
    public function sendCombined(Request $request)
    {
        $request->validate([
            'recipient_type' => 'required|in:all,role,course,category,provider,registration_date,activity,selected,single',
            'subject' => 'required|string|max:255',
            'email_content' => 'required|string',
            'roles' => 'nullable|required_if:recipient_type,role|array',
            'roles.*' => 'in:customer,provider,admin,affiliate,ambassador',
            'content_id' => 'nullable|required_if:recipient_type,course|exists:contents,id',
            'category_id' => 'nullable|required_if:recipient_type,category|exists:categories,id',
            'provider_id' => 'nullable|required_if:recipient_type,provider|exists:users,id',
            'registration_date_from' => 'nullable|required_if:recipient_type,registration_date|date',
            'registration_date_to' => 'nullable|required_if:recipient_type,registration_date|date|after_or_equal:registration_date_from',
            'activity_type' => 'nullable|required_if:recipient_type,activity|in:active_recent,active_month,active_3months,inactive_30days,inactive_90days,never_logged',
            'single_user_id' => 'nullable|required_if:recipient_type,single|exists:users,id',
            'user_ids' => 'nullable|required_if:recipient_type,selected|string',
            'whatsapp_message' => 'required|string|max:4096',
            'send_email' => 'nullable|boolean',
            'send_whatsapp' => 'nullable|boolean',
            'roles' => 'nullable|required_if:recipient_type,role|array',
            'roles.*' => 'in:customer,provider,admin,affiliate,ambassador',
            'single_user_id' => 'nullable|required_if:recipient_type,single|exists:users,id',
            'user_ids' => 'nullable|required_if:recipient_type,selected|string',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240', // 10MB max par fichier
        ]);

        // Vérifier qu'au moins un canal est sélectionné
        if (!$request->has('send_email') && !$request->has('send_whatsapp')) {
            return redirect()->back()
                ->with('error', 'Veuillez sélectionner au moins un canal d\'envoi (Email ou WhatsApp).')
                ->withInput();
        }

        $recipientType = $request->recipient_type;
        $subject = $request->subject;
        $emailContent = $request->email_content;
        $whatsappMessage = $request->whatsapp_message;
        $sendEmail = $request->has('send_email');
        $sendWhatsApp = $request->has('send_whatsapp');

        // Gérer les pièces jointes pour l'email
        $attachmentPaths = [];
        if ($request->hasFile('attachments')) {
            $service = app(FileUploadService::class);
            foreach ($request->file('attachments') as $file) {
                try {
                    $uploadResult = $service->upload($file, 'email-attachments');
                    // FileUploadService retourne un tableau avec 'path' et 'url', on a besoin du 'path'
                    $attachmentPaths[] = is_array($uploadResult) ? $uploadResult['path'] : $uploadResult;
                } catch (\Exception $e) {
                    Log::error("Erreur lors de l'upload de la pièce jointe: " . $e->getMessage());
                    // Continuer avec les autres fichiers même si un échoue
                }
            }
        }

        // Obtenir les destinataires pour email (avec email)
        $emailUsers = collect();
        if ($sendEmail) {
            $emailUsers = $this->getEmailRecipients($request);
        }

        // Obtenir les destinataires pour WhatsApp (avec téléphone)
        $whatsappUsers = collect();
        if ($sendWhatsApp) {
            $whatsappUsers = $this->getWhatsAppRecipients($request);
        }

        // Vérifier qu'il y a au moins un destinataire
        if ($sendEmail && $emailUsers->isEmpty()) {
            return redirect()->back()
                ->with('error', 'Aucun destinataire avec adresse email trouvé pour cet envoi.')
                ->withInput();
        }

        if ($sendWhatsApp && $whatsappUsers->isEmpty()) {
            return redirect()->back()
                ->with('error', 'Aucun destinataire avec numéro de téléphone trouvé pour cet envoi.')
                ->withInput();
        }

        if ($sendEmail && $emailUsers->isEmpty() && $sendWhatsApp && $whatsappUsers->isEmpty()) {
            return redirect()->back()
                ->with('error', 'Aucun destinataire trouvé pour cet envoi.')
                ->withInput();
        }

        $emailSentCount = 0;
        $emailFailedCount = 0;
        $whatsappSentCount = 0;
        $whatsappFailedCount = 0;

        // Exécuter directement les envois sans passer par la queue/worker
        // Les erreurs sont gérées individuellement pour ne pas bloquer les autres envois
        
        // Envoyer les emails directement
        if ($sendEmail && $emailUsers->isNotEmpty()) {
            foreach ($emailUsers as $user) {
                if ($user->email) {
                    try {
                        // Exécuter directement le job sans passer par la queue
                        $job = new SendEmailJob($user, $subject, $emailContent, $attachmentPaths, $recipientType);
                        $job->handle();
                        $emailSentCount++;
                    } catch (\Exception $e) {
                        Log::error("Erreur lors de l'envoi d'email à {$user->email}: " . $e->getMessage());
                        $emailFailedCount++;
                    }
                }
            }
        }

        // Envoyer les messages WhatsApp directement
        if ($sendWhatsApp && $whatsappUsers->isNotEmpty()) {
            foreach ($whatsappUsers as $user) {
                if ($user->phone) {
                    try {
                        // Exécuter directement le job sans passer par la queue
                        $job = new SendWhatsAppJob($user, $whatsappMessage, $recipientType);
                        $job->handle();
                        $whatsappSentCount++;
                    } catch (\Exception $e) {
                        Log::error("Erreur lors de l'envoi WhatsApp à {$user->phone}: " . $e->getMessage());
                        $whatsappFailedCount++;
                    }
                }
            }
        }
        
        // Construire le message de résultat
        $message = "Envoi combiné terminé ! ";
        if ($sendEmail) {
            $message .= "{$emailSentCount} email(s) envoyé(s)";
            if ($emailFailedCount > 0) {
                $message .= ", {$emailFailedCount} échec(s)";
            }
            $message .= ". ";
        }
        if ($sendWhatsApp) {
            $message .= "{$whatsappSentCount} message(s) WhatsApp envoyé(s)";
            if ($whatsappFailedCount > 0) {
                $message .= ", {$whatsappFailedCount} échec(s)";
            }
            $message .= ". ";
        }

        // Si c'est une requête AJAX, retourner JSON
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'redirect' => route('admin.announcements')
            ]);
        }
        
        return redirect()->route('admin.announcements')
            ->with('success', $message);
    }

    /**
     * Supprimer un message WhatsApp envoyé
     */
    public function destroyWhatsAppMessage(SentWhatsAppMessage $sentWhatsAppMessage)
    {
        try {
            $sentWhatsAppMessage->delete();

            return redirect()->route('admin.announcements')
                ->with('success', 'Message WhatsApp supprimé avec succès.');
        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression du message WhatsApp', [
                'message_id' => $sentWhatsAppMessage->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('admin.announcements')
                ->with('error', 'Erreur lors de la suppression du message WhatsApp.');
        }
    }

    /**
     * Actions en lot sur les utilisateurs
     */
    public function bulkActionUsers(Request $request)
    {
        $actions = [
            'delete' => function($ids) {
                $count = 0;
                foreach ($ids as $id) {
                    $user = User::find($id);
                    if ($user && !$user->isAdmin()) {
                        $user->delete();
                        $count++;
                    }
                }
                return [
                    'message' => "{$count} utilisateur(s) supprimé(s) avec succès.",
                    'count' => $count
                ];
            },
            'activate' => function($ids) {
                $count = User::whereIn('id', $ids)
                    ->where('is_active', false)
                    ->update(['is_active' => true]);
                return [
                    'message' => "{$count} utilisateur(s) activé(s) avec succès.",
                    'count' => $count
                ];
            },
            'deactivate' => function($ids) {
                $count = User::whereIn('id', $ids)
                    ->where('is_active', true)
                    ->whereNotIn('role', ['admin', 'super_user'])
                    ->update(['is_active' => false]);
                return [
                    'message' => "{$count} utilisateur(s) désactivé(s) avec succès.",
                    'count' => $count
                ];
            }
        ];

        return $this->handleBulkAction($request, User::class, $actions);
    }

    /**
     * Exporter les utilisateurs
     */
    public function exportUsers(Request $request)
    {
        $query = User::withCount(['courses', 'enrollments']);

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->get('role'));
        }

        if ($request->filled('status')) {
            if ($request->get('status') === 'active') {
                $query->where('is_active', true);
            } elseif ($request->get('status') === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $columns = [
            'id' => 'ID',
            'name' => 'Nom',
            'email' => 'Email',
            'role' => 'Rôle',
            'is_active' => 'Actif',
            'is_verified' => 'Vérifié',
            'courses_count' => 'Nombre de cours',
            'enrollments_count' => 'Nombre d\'inscriptions',
            'created_at' => 'Date d\'inscription',
            'last_login_at' => 'Dernière connexion'
        ];

        return $this->exportData($request, $query, $columns, 'utilisateurs');
    }

    /**
     * Actions en lot sur les contenus
     */
    public function bulkActionContents(Request $request)
    {
        $actions = [
            'delete' => function($ids) {
                return $this->bulkDelete($ids, Course::class);
            },
            'publish' => function($ids) {
                return $this->bulkUpdate($ids, Course::class, ['is_published' => true]);
            },
            'unpublish' => function($ids) {
                return $this->bulkUpdate($ids, Course::class, ['is_published' => false]);
            }
        ];

        return $this->handleBulkAction($request, Course::class, $actions);
    }

    /**
     * Exporter les contenus
     */
    public function exportContents(Request $request)
    {
        try {
            $query = Course::with(['category', 'provider'])
            ->withCount(['enrollments', 'reviews'])
            ->withAvg('reviews', 'rating');

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('subtitle', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('status')) {
            if ($request->status === 'published') {
                $query->where('is_published', true);
            } elseif ($request->status === 'draft') {
                $query->where('is_published', false);
            }
        }

        if ($request->filled('provider')) {
            $query->where('provider_id', $request->provider);
        }

        $columns = [
            'id' => 'ID',
            'title' => 'Titre',
            'subtitle' => 'Sous-titre',
            'category.name' => 'Catégorie',
            'provider.name' => 'Prestataire',
            'price' => 'Prix',
            'is_free' => 'Gratuit',
            'is_published' => 'Publié',
            'enrollments_count' => 'Inscriptions',
            'reviews_avg_rating' => 'Note moyenne',
            'created_at' => 'Date de création'
        ];

        return $this->exportData($request, $query, $columns, 'contenus');
        } catch (\Exception $e) {
            Log::error('Erreur exportContents', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Erreur lors de l\'export: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Combine internal revenue and commissions by period
     * 
     * @param \Illuminate\Support\Collection $internalRevenue Collection with period key and revenue value
     * @param \Illuminate\Support\Collection $commissions Collection with period key and revenue value
     * @param string $periodKey The key name for the period (month, date, week, year)
     * @return \Illuminate\Support\Collection Combined revenue by period
     */
    private function combineRevenueByPeriod($internalRevenue, $commissions, $periodKey)
    {
        // Create a map of all periods from both collections
        $allPeriods = collect();
        
        // Add all periods from internal revenue
        $internalRevenue->each(function ($item) use ($allPeriods, $periodKey) {
            $period = $item->{$periodKey} ?? '';
            if ($period && !$allPeriods->contains($period)) {
                $allPeriods->push($period);
            }
        });
        
        // Add all periods from commissions
        $commissions->each(function ($item) use ($allPeriods, $periodKey) {
            $period = $item->{$periodKey} ?? '';
            if ($period && !$allPeriods->contains($period)) {
                $allPeriods->push($period);
            }
        });
        
        // Create a map for quick lookup
        $internalMap = $internalRevenue->keyBy($periodKey);
        $commissionsMap = $commissions->keyBy($periodKey);
        
        // Combine revenues for each period
        return $allPeriods->map(function ($period) use ($internalMap, $commissionsMap, $periodKey) {
            $internal = $internalMap->get($period);
            $commission = $commissionsMap->get($period);
            
            $internalRevenue = $internal ? (float)($internal->revenue ?? 0) : 0;
            $commissionRevenue = $commission ? (float)($commission->revenue ?? 0) : 0;
            
            return (object) [
                $periodKey => $period,
                'revenue' => $internalRevenue + $commissionRevenue
            ];
        })->sortBy($periodKey)->values();
    }

    /**
     * Afficher les détails d'un message de contact
     */
    public function showContactMessage(ContactMessage $contactMessage)
    {
        $subjectLabels = [
            'inscription' => 'Inscription à un contenu',
            'paiement' => 'Paiement',
            'technique' => 'Problème technique',
            'support' => 'Support pédagogique',
            'partenariat' => 'Partenariat',
            'autre' => 'Autre',
        ];
        
        $contactMessage->subject_label = $subjectLabels[$contactMessage->subject] ?? ucfirst($contactMessage->subject);
        
        return view('admin.contact.show', compact('contactMessage'));
    }

    /**
     * Marquer un message de contact comme lu
     */
    public function markContactMessageAsRead(ContactMessage $contactMessage)
    {
        $contactMessage->markAsRead();
        
        return redirect()->route('admin.announcements')
            ->with('success', 'Message marqué comme lu.');
    }

    /**
     * Supprimer un message de contact
     */
    public function destroyContactMessage(ContactMessage $contactMessage)
    {
        $contactMessage->delete();
        
        return redirect()->route('admin.announcements')
            ->with('success', 'Message supprimé avec succès.');
    }
}
