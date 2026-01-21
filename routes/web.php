<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\ChunkUploadController;
use App\Http\Controllers\ProviderController;
use App\Http\Controllers\ProviderApplicationController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\BannerController;
// use App\Http\Controllers\PaymentController; // désactivé
use App\Http\Controllers\AffiliateController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\LearningController;
use App\Http\Controllers\YouTubeAccessController;
use App\Http\Controllers\FilterController;
use App\Http\Controllers\MonerooController;
use App\Http\Controllers\MonerooPayoutController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\TemporaryUploadController;
use App\Http\Controllers\Auth\SSOController;
use App\Http\Controllers\SSOCallbackController;
use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

// File serving routes (sécurisées)
Route::get('/files/{type}/{path}', [FileController::class, 'serve'])
    ->where('path', '.*')
    ->name('files.serve');

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', function() {
    return view('about');
})->name('about');
Route::get('/contact', function() {
    return view('contact');
})->name('contact');

// Legal pages
Route::get('/conditions-generales-de-vente', function() {
    return view('legal.terms');
})->name('legal.terms');
Route::get('/politique-de-confidentialite', function() {
    return view('legal.privacy');
})->name('legal.privacy');
Route::get('/categories', function() {
    $categories = App\Models\Category::active()->ordered()->withCount('courses')->get();
    return view('categories.index', compact('categories'));
})->name('categories.index');

// Filtres et recherche
Route::post('/contents/filter', [FilterController::class, 'filterCourses'])->name('contents.filter');
Route::get('/contents/filter-options', [FilterController::class, 'getFilterOptions'])->name('contents.filter-options');
Route::get('/contents/search', [FilterController::class, 'searchCourses'])->name('contents.search');

// Test route for categories
Route::get('/test-categories', function() {
    $categories = App\Models\Category::withCount('courses')->ordered()->get();
    return response()->json([
        'categories_count' => $categories->count(),
        'categories' => $categories->map(function($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'courses_count' => $category->courses_count,
                'is_active' => $category->is_active
            ];
        })
    ]);
});

// Test route for categories view
Route::get('/test-categories-view', function() {
    $categories = App\Models\Category::withCount('courses')->ordered()->paginate(20);
    return view('admin.categories.index', compact('categories'));
});
Route::get('/contents', [CourseController::class, 'index'])->name('contents.index');
Route::get('/contents/{course:slug}', [CourseController::class, 'show'])->name('contents.show');
Route::get('/contents/{course:slug}/reviews', [CourseController::class, 'reviews'])->name('contents.reviews');
Route::get('/contents/{course:slug}/preview-data', [CourseController::class, 'previewData'])->name('contents.preview-data');
Route::get('/contents/{course:slug}/lesson/{lesson}', [CourseController::class, 'lesson'])->name('contents.lesson');
Route::get('/categories/{category:slug}', [CourseController::class, 'byCategory'])->name('contents.category');
Route::get('/providers', [ProviderController::class, 'index'])->name('providers.index');
Route::get('/providers/{provider}', [ProviderController::class, 'show'])->name('providers.show');
Route::get('/become-provider', [ProviderApplicationController::class, 'index'])->name('provider-application.index');
Route::get('/become-ambassador', [App\Http\Controllers\AmbassadorApplicationController::class, 'index'])->name('ambassador-application.index');

// Blog routes
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{post:slug}', [BlogController::class, 'show'])->name('blog.show');
Route::get('/blog/category/{category:slug}', [BlogController::class, 'category'])->name('blog.category');
Route::get('/blog/author/{author}', [BlogController::class, 'author'])->name('blog.author');
Route::get('/blog/search', [BlogController::class, 'search'])->name('blog.search');

// Newsletter routes
Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe'])->name('newsletter.subscribe');
Route::post('/newsletter/unsubscribe', [NewsletterController::class, 'unsubscribe'])->name('newsletter.unsubscribe');
Route::get('/newsletter/confirm/{token}', [NewsletterController::class, 'confirm'])->name('newsletter.confirm');



// Order management routes - avec validation SSO pour les actions de modification
Route::middleware('auth')->group(function () {
    // Customer order routes (moved under /customers/ordres)
    Route::get('/customers/ordres', [App\Http\Controllers\OrderController::class, 'index'])->name('orders.index');
    Route::get('/customers/ordres/{order}', [App\Http\Controllers\OrderController::class, 'show'])->name('orders.show');
});

// SSO routes (must be before auth routes)
Route::get('/sso/callback', [SSOCallbackController::class, 'handle'])->name('sso.callback');

// Authentication routes
require __DIR__.'/auth.php';

// Cart routes (accessible to all users) - avec validation SSO pour les actions de modification
Route::middleware('sync.cart')->group(function () {
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::delete('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
    Route::delete('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');
    Route::post('/cart/apply-promo', [CartController::class, 'applyPromoCode'])->name('cart.apply-promo');
    Route::delete('/cart/remove-promo', [CartController::class, 'removePromoCode'])->name('cart.remove-promo');
    Route::get('/cart/count', [CartController::class, 'count'])->name('cart.count');
    Route::get('/cart/content', [CartController::class, 'getCartContent'])->name('cart.content');
    Route::get('/cart/summary', [CartController::class, 'getSummary'])->name('cart.summary');
    Route::get('/cart/recommendations', [CartController::class, 'getRecommendations'])->name('cart.recommendations');
    Route::get('/cart/checkout', [CartController::class, 'checkout'])->name('cart.checkout');
});

// Routes de fallback pour éviter les erreurs 500 sur /me et /logout
// Ces routes sont appelées par certains scripts mais n'existent pas
// Note: Pas de middleware 'auth' pour permettre de retourner 401 au lieu de rediriger
Route::get('/me', function() {
    try {
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]
        ]);
    } catch (\Throwable $e) {
        \Log::debug('Error in /me route', [
            'error' => $e->getMessage(),
            'type' => get_class($e),
        ]);
        return response()->json(['error' => 'Unauthorized'], 401);
    }
});

Route::get('/api/me', function() {
    try {
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]
        ]);
    } catch (\Throwable $e) {
        \Log::debug('Error in /api/me route', [
            'error' => $e->getMessage(),
            'type' => get_class($e),
        ]);
        return response()->json(['error' => 'Unauthorized'], 401);
    }
});

// Route GET pour /logout (fallback pour les appels AJAX qui utilisent GET)
// Note: La route POST logout est définie dans routes/auth.php
Route::get('/logout', function(Request $request) {
    try {
        // Pour les requêtes AJAX GET, retourner une réponse JSON
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'Utilisez POST pour la déconnexion',
                'redirect' => route('logout')
            ], 405);
        }
        // Pour les requêtes normales GET, rediriger vers la page d'accueil
        return redirect()->route('home');
    } catch (\Throwable $e) {
        \Log::debug('Error in GET /logout route', ['error' => $e->getMessage()]);
        return redirect()->route('home');
    }
})->middleware('auth');

// Authenticated routes
Route::middleware(['auth', 'verified'])->group(function () {
    // Route pour valider SSO avant redirection vers le profil
    Route::get('/profile/redirect', [App\Http\Controllers\ProfileRedirectController::class, 'redirect'])->name('profile.redirect');
    
    // Dashboard based on user role
    Route::get('/dashboard', function () {
        $user = auth()->user();
        // Les super_user et admin redirigent vers l'admin
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        return match($user->role) {
            'provider' => redirect()->route('provider.dashboard'),
            'affiliate' => redirect()->route('affiliate.dashboard'),
            default => redirect()->route('customer.dashboard'),
        };
    })->name('dashboard');

    Route::match(['POST', 'DELETE'], '/uploads/temp', [TemporaryUploadController::class, 'destroy'])
        ->name('uploads.temp.destroy');

    // Customer routes
    Route::prefix('customer')->name('customer.')->middleware('role:customer')->group(function () {
        Route::get('/dashboard', [CustomerController::class, 'dashboard'])->name('dashboard');
        Route::get('/contents', [CustomerController::class, 'courses'])->name('contents');
        Route::get('/certificates', [CustomerController::class, 'certificates'])->name('certificates');
    });
    
    // Certificate download route (accessible to authenticated users who own the certificate)
    Route::get('/certificates/{certificate}/download', [CustomerController::class, 'downloadCertificate'])
        ->middleware('auth')
        ->name('certificates.download');
    
    // Enrollment route (accessible to all authenticated users) - avec validation SSO
    Route::post('/customer/courses/{course:slug}/enroll', [CustomerController::class, 'enroll'])
        ->middleware('sso.validate')
        ->name('customer.contents.enroll');

    // Review routes (accessible to all authenticated users who are enrolled)
    Route::post('/contents/{course:slug}/review', [ReviewController::class, 'store'])
        ->middleware('sso.validate')
        ->name('contents.review.store');
    Route::delete('/contents/{course:slug}/review', [ReviewController::class, 'destroy'])
        ->middleware('sso.validate')
        ->name('contents.review.destroy');

    // Provider Application routes (accessible to authenticated users) - avec validation SSO pour les POST
    Route::middleware('auth')->group(function () {
        Route::get('/provider-application/create', [App\Http\Controllers\ProviderApplicationController::class, 'create'])->name('provider-application.create');
        Route::post('/provider-application/step1', [App\Http\Controllers\ProviderApplicationController::class, 'storeStep1'])
            ->middleware('sso.validate')
            ->name('provider-application.store-step1');
        Route::get('/provider-application/{application}/step2', [App\Http\Controllers\ProviderApplicationController::class, 'step2'])->name('provider-application.step2');
        Route::post('/provider-application/{application}/step2', [App\Http\Controllers\ProviderApplicationController::class, 'storeStep2'])
            ->middleware('sso.validate')
            ->name('provider-application.store-step2');
        Route::get('/provider-application/{application}/step3', [App\Http\Controllers\ProviderApplicationController::class, 'step3'])->name('provider-application.step3');
        Route::post('/provider-application/{application}/step3', [App\Http\Controllers\ProviderApplicationController::class, 'storeStep3'])
            ->middleware('sso.validate')
            ->name('provider-application.store-step3');
        Route::get('/provider-application/{application}/status', [App\Http\Controllers\ProviderApplicationController::class, 'status'])->name('provider-application.status');
        Route::get('/provider-application/{application}/cv', [App\Http\Controllers\ProviderApplicationController::class, 'downloadCv'])->name('provider-application.download-cv');
        Route::get('/provider-application/{application}/motivation-letter', [App\Http\Controllers\ProviderApplicationController::class, 'downloadMotivationLetter'])->name('provider-application.download-motivation-letter');
    Route::delete('/provider-application/{application}', [App\Http\Controllers\ProviderApplicationController::class, 'abandon'])
        ->middleware('sso.validate')
        ->name('provider-application.abandon');
    });

    // Ambassador Application routes (accessible to authenticated users) - avec validation SSO pour les POST
    Route::middleware('auth')->group(function () {
        Route::get('/ambassador-application/create', [App\Http\Controllers\AmbassadorApplicationController::class, 'create'])->name('ambassador-application.create');
        Route::post('/ambassador-application/step1', [App\Http\Controllers\AmbassadorApplicationController::class, 'storeStep1'])
            ->middleware('sso.validate')
            ->name('ambassador-application.store-step1');
        Route::get('/ambassador-application/{application}/step2', [App\Http\Controllers\AmbassadorApplicationController::class, 'step2'])->name('ambassador-application.step2');
        Route::post('/ambassador-application/{application}/step2', [App\Http\Controllers\AmbassadorApplicationController::class, 'storeStep2'])
            ->middleware('sso.validate')
            ->name('ambassador-application.store-step2');
        Route::get('/ambassador-application/{application}/step3', [App\Http\Controllers\AmbassadorApplicationController::class, 'step3'])->name('ambassador-application.step3');
        Route::post('/ambassador-application/{application}/step3', [App\Http\Controllers\AmbassadorApplicationController::class, 'storeStep3'])
            ->middleware('sso.validate')
            ->name('ambassador-application.store-step3');
        Route::get('/ambassador-application/{application}/status', [App\Http\Controllers\AmbassadorApplicationController::class, 'status'])->name('ambassador-application.status');
        Route::get('/ambassador-application/{application}/document', [App\Http\Controllers\AmbassadorApplicationController::class, 'downloadDocument'])->name('ambassador-application.download-document');
        Route::delete('/ambassador-application/{application}', [App\Http\Controllers\AmbassadorApplicationController::class, 'abandon'])
            ->middleware('sso.validate')
            ->name('ambassador-application.abandon');
        
        // Dashboard ambassadeur
        Route::get('/ambassador/dashboard', [App\Http\Controllers\AmbassadorApplicationController::class, 'dashboard'])->name('ambassador.dashboard');
        Route::get('/ambassador/analytics', [App\Http\Controllers\AmbassadorApplicationController::class, 'analytics'])->name('ambassador.analytics');
        
        // 🔒 Routes Wallet - Protégées pour les ambassadeurs uniquement
        // GET routes (lecture seule, pas de modification de données)
        Route::get('/ambassador/payment-settings', [App\Http\Controllers\WalletController::class, 'index'])
            ->middleware('role:ambassador')
            ->name('ambassador.payment-settings');
        Route::get('/wallet', [App\Http\Controllers\WalletController::class, 'index'])
            ->middleware('role:ambassador')
            ->name('wallet.index');
        Route::get('/wallet/transactions', [App\Http\Controllers\WalletController::class, 'transactions'])
            ->middleware('role:ambassador')
            ->name('wallet.transactions');
        Route::get('/wallet/payouts', [App\Http\Controllers\WalletController::class, 'payouts'])
            ->middleware('role:ambassador')
            ->name('wallet.payouts');
        Route::get('/wallet/payout/create', [App\Http\Controllers\WalletController::class, 'createPayout'])
            ->middleware('role:ambassador')
            ->name('wallet.create-payout');
        Route::get('/wallet/payout/{payout}', [App\Http\Controllers\WalletController::class, 'showPayout'])
            ->middleware('role:ambassador')
            ->name('wallet.show-payout');
            
        // POST/DELETE routes (modifications de données) - Protection SSO en plus
        Route::post('/wallet/payout', [App\Http\Controllers\WalletController::class, 'storePayout'])
            ->middleware(['role:ambassador', 'sso.validate', 'throttle:5,1'])
            ->name('wallet.store-payout');
        Route::delete('/wallet/payout/{payout}', [App\Http\Controllers\WalletController::class, 'cancelPayout'])
            ->middleware(['role:ambassador', 'sso.validate'])
            ->name('wallet.cancel-payout');
        Route::post('/wallet/payout/{payout}/check-status', [App\Http\Controllers\WalletController::class, 'checkPayoutStatus'])
            ->middleware(['role:ambassador', 'sso.validate', 'throttle:10,1'])
            ->name('wallet.check-payout-status');
    });

    // Provider routes (only for approved providers) - avec validation SSO pour les POST/PUT/DELETE
    Route::prefix('provider')->name('provider.')->middleware('role:provider')->group(function () {
        Route::get('/courses/list', function () {
            return redirect()->route('provider.contents.index');
        });
        Route::get('/contents', [ProviderController::class, 'coursesIndex'])->name('contents.index');
        Route::get('/dashboard', [ProviderController::class, 'dashboard'])->name('dashboard');
        Route::resource('contents', CourseController::class)->except(['index', 'show'])->middleware('sso.validate');
        Route::get('/contents/{course:slug}', [CourseController::class, 'show'])->name('contents.show');
        Route::get('/contents/{course}/lessons', [ProviderController::class, 'lessons'])->name('contents.lessons');
        Route::post('/contents/{course}/lessons', [ProviderController::class, 'storeLesson'])
            ->middleware('sso.validate')
            ->name('contents.lessons.store');
        Route::get('/customers', [ProviderController::class, 'customers'])->name('customers');
        Route::get('/analytics', [ProviderController::class, 'analytics'])->name('analytics');
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
        Route::get('/payment-settings', [ProviderController::class, 'paymentSettings'])->name('payment-settings');
        Route::post('/payment-settings', [ProviderController::class, 'updatePaymentSettings'])
            ->middleware('sso.validate')
            ->name('payment-settings.update');
        
        // Lesson Resources management
        Route::post('/lessons/{lesson}/resources', [App\Http\Controllers\LessonResourceController::class, 'store'])
            ->middleware('sso.validate')
            ->name('lessons.resources.store');
        Route::put('/lessons/{lesson}/resources/{resource}', [App\Http\Controllers\LessonResourceController::class, 'update'])
            ->middleware('sso.validate')
            ->name('lessons.resources.update');
        Route::delete('/lessons/{lesson}/resources/{resource}', [App\Http\Controllers\LessonResourceController::class, 'destroy'])
            ->middleware('sso.validate')
            ->name('lessons.resources.destroy');
    });

    // Admin routes - avec validation SSO pour les actions de modification (appliqué individuellement)
    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/analytics', [AdminController::class, 'analytics'])->name('analytics');
        Route::get('/analytics/revenue-data', [AdminController::class, 'getRevenueData'])->name('analytics.revenue-data');
        Route::get('/analytics/revenue-by-category', [AdminController::class, 'getRevenueByCategory'])->name('analytics.revenue-by-category');
        Route::get('/analytics/revenue-by-course', [AdminController::class, 'getRevenueByCourse'])->name('analytics.revenue-by-course');
        Route::get('/analytics/revenue-by-provider', [AdminController::class, 'getRevenueByProvider'])->name('analytics.revenue-by-provider');
        Route::get('/statistics', [AdminController::class, 'statistics'])->name('statistics');
        Route::post('/contents/{course}/recalculate-stats', [AdminController::class, 'recalculateCourseStats'])
            ->middleware('sso.validate')
            ->name('contents.recalculate-stats');
        Route::post('/statistics/recalculate-all', [AdminController::class, 'recalculateAllStats'])
            ->middleware('sso.validate')
            ->name('statistics.recalculate-all');
        
        // Users management
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::get('/users/create', [AdminController::class, 'createUser'])->name('users.create');
        Route::post('/users', [AdminController::class, 'storeUser'])
            ->middleware('sso.validate')
            ->name('users.store');
        Route::get('/users/{user}', [AdminController::class, 'showUser'])->name('users.show');
        Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('users.edit');
        Route::put('/users/{user}', [AdminController::class, 'updateUser'])
            ->middleware('sso.validate')
            ->name('users.update');
        Route::post('/users/{user}/sync', [AdminController::class, 'syncUserFromSSO'])
            ->middleware('sso.validate')
            ->name('users.sync');
        Route::delete('/users/{user}', [AdminController::class, 'destroyUser'])
            ->middleware('sso.validate')
            ->name('users.destroy');
        
        // Gestion des accès aux cours
        Route::post('/users/{user}/grant-course-access', [AdminController::class, 'grantCourseAccess'])
            ->middleware('sso.validate')
            ->name('users.grant-course-access');
        Route::delete('/users/{user}/contents/{course}/revoke-access', [AdminController::class, 'revokeCourseAccess'])
            ->middleware('sso.validate')
            ->name('users.revoke-content-access');
        Route::delete('/users/{user}/contents/{course}/unenroll', [AdminController::class, 'unenrollUser'])
            ->middleware('sso.validate')
            ->name('users.unenroll');
        
        // Provider Applications management
        Route::get('/provider-applications', [AdminController::class, 'providerApplications'])->name('provider-applications');
        Route::get('/provider-applications/{application}', [AdminController::class, 'showProviderApplication'])->name('provider-applications.show');
        Route::put('/provider-applications/{application}/status', [AdminController::class, 'updateProviderApplicationStatus'])
            ->middleware('sso.validate')
            ->name('provider-applications.update-status');
        
        // Ambassador Applications management
        Route::get('/ambassadors/applications', [App\Http\Controllers\Admin\AmbassadorController::class, 'applications'])->name('ambassadors.applications');
        Route::get('/ambassadors/applications/{application}', [App\Http\Controllers\Admin\AmbassadorController::class, 'showApplication'])->name('ambassadors.applications.show');
        Route::put('/ambassadors/applications/{application}/status', [App\Http\Controllers\Admin\AmbassadorController::class, 'updateApplicationStatus'])
            ->middleware('sso.validate')
            ->name('ambassadors.applications.update-status');
        Route::delete('/ambassadors/applications/{application}', [App\Http\Controllers\Admin\AmbassadorController::class, 'destroyApplication'])
            ->middleware('sso.validate')
            ->name('ambassadors.applications.destroy');
        
        // Ambassadors management
        Route::get('/ambassadors', [App\Http\Controllers\Admin\AmbassadorController::class, 'index'])->name('ambassadors.index');
        Route::get('/ambassadors/{ambassador}', [App\Http\Controllers\Admin\AmbassadorController::class, 'show'])->name('ambassadors.show');
        Route::post('/ambassadors/{ambassador}/toggle-active', [App\Http\Controllers\Admin\AmbassadorController::class, 'toggleActive'])
            ->middleware('sso.validate')
            ->name('ambassadors.toggle-active');
        Route::post('/ambassadors/{ambassador}/generate-promo-code', [App\Http\Controllers\Admin\AmbassadorController::class, 'generatePromoCode'])
            ->middleware('sso.validate')
            ->name('ambassadors.generate-promo-code');
        Route::get('/ambassadors/{ambassador}/check-promo-code', [App\Http\Controllers\Admin\AmbassadorController::class, 'checkPromoCodeUnique'])
            ->middleware('sso.validate')
            ->name('ambassadors.check-promo-code');
        Route::put('/ambassadors/{ambassador}/update-promo-code', [App\Http\Controllers\Admin\AmbassadorController::class, 'updatePromoCode'])
            ->middleware('sso.validate')
            ->name('ambassadors.update-promo-code');
        Route::delete('/ambassadors/{ambassador}', [App\Http\Controllers\Admin\AmbassadorController::class, 'destroy'])
            ->middleware('sso.validate')
            ->name('ambassadors.destroy');
        
        // Ambassador Commissions management
        Route::get('/ambassadors/commissions', [App\Http\Controllers\Admin\AmbassadorController::class, 'commissions'])->name('ambassadors.commissions');
        Route::get('/ambassadors/{ambassador}/commissions', [App\Http\Controllers\Admin\AmbassadorController::class, 'commissions'])->name('ambassadors.commissions.ambassador');
        Route::post('/ambassadors/commissions/{commission}/approve', [App\Http\Controllers\Admin\AmbassadorController::class, 'approveCommission'])
            ->middleware('sso.validate')
            ->name('ambassadors.commissions.approve');
        Route::post('/ambassadors/commissions/{commission}/mark-paid', [App\Http\Controllers\Admin\AmbassadorController::class, 'markCommissionAsPaid'])
            ->middleware('sso.validate')
            ->name('ambassadors.commissions.mark-paid');
        
        // Categories management
        Route::get('/categories', [AdminController::class, 'categories'])->name('categories');
        Route::post('/categories', [AdminController::class, 'storeCategory'])
            ->middleware('sso.validate')
            ->name('categories.store');
        Route::get('/categories/{category}/edit', [AdminController::class, 'editCategory'])->name('categories.edit');
        Route::put('/categories/{category}', [AdminController::class, 'updateCategory'])
            ->middleware('sso.validate')
            ->name('categories.update');
        Route::delete('/categories/{category}', [AdminController::class, 'destroyCategory'])
            ->middleware('sso.validate')
            ->name('categories.destroy');
        
        // Courses management
        Route::get('/contents', [AdminController::class, 'courses'])->name('contents');
        Route::get('/contents/create', [AdminController::class, 'createCourse'])->name('contents.create');
        Route::post('/contents', [AdminController::class, 'storeCourse'])
            ->middleware('sso.validate')
            ->name('contents.store');
        Route::get('/contents/{course}', [AdminController::class, 'showCourse'])->name('contents.show');
        Route::get('/contents/{course}/edit', [AdminController::class, 'editCourse'])->name('contents.edit');
        Route::put('/contents/{course}', [AdminController::class, 'updateCourse'])
            ->middleware('sso.validate')
            ->name('contents.update');
        Route::delete('/contents/{course}', [AdminController::class, 'destroyCourse'])
            ->middleware('sso.validate')
            ->name('contents.destroy');
        
        // Course lessons management (disabled - legacy routes removed)
        
        // Announcements management
        Route::get('/announcements', [AdminController::class, 'announcements'])->name('announcements');
        Route::post('/announcements', [AdminController::class, 'storeAnnouncement'])
            ->middleware('sso.validate')
            ->name('announcements.store');
        Route::get('/announcements/{announcement}/preview', [AdminController::class, 'previewAnnouncement'])->name('announcements.preview');
        Route::get('/announcements/{announcement}/edit', [AdminController::class, 'editAnnouncement'])->name('announcements.edit');
        Route::put('/announcements/{announcement}', [AdminController::class, 'updateAnnouncement'])
            ->middleware('sso.validate')
            ->name('announcements.update');
        Route::delete('/announcements/{announcement}', [AdminController::class, 'destroyAnnouncement'])
            ->middleware('sso.validate')
            ->name('announcements.destroy');
        
        // Email sending from announcements
        Route::get('/announcements/send-email', [AdminController::class, 'showSendEmail'])
            ->name('announcements.send-email');
        Route::post('/announcements/send-email', [AdminController::class, 'sendEmail'])
            ->middleware('sso.validate')
            ->name('announcements.send-email.post');
        Route::get('/announcements/search-users', [AdminController::class, 'searchUsers'])
            ->name('announcements.search-users');
        Route::get('/announcements/count-users', [AdminController::class, 'countUsers'])
            ->name('announcements.count-users');
        Route::post('/announcements/upload-image', [AdminController::class, 'uploadImage'])
            ->middleware('sso.validate')
            ->name('announcements.upload-image');
        
        // WhatsApp sending from announcements
        Route::get('/announcements/send-whatsapp', [AdminController::class, 'showSendWhatsApp'])
            ->name('announcements.send-whatsapp');
        Route::post('/announcements/send-whatsapp', [AdminController::class, 'sendWhatsApp'])
            ->middleware('sso.validate')
            ->name('announcements.send-whatsapp.post');
        Route::get('/announcements/search-users-whatsapp', [AdminController::class, 'searchUsersForWhatsApp'])
            ->name('announcements.search-users-whatsapp');
        Route::get('/announcements/count-users-whatsapp', [AdminController::class, 'countUsersForWhatsApp'])
            ->name('announcements.count-users-whatsapp');
        
        // Combined sending (Email + WhatsApp) from announcements
        Route::get('/announcements/send-combined', [AdminController::class, 'showSendCombined'])
            ->name('announcements.send-combined');
        Route::post('/announcements/send-combined', [AdminController::class, 'sendCombined'])
            ->middleware('sso.validate')
            ->name('announcements.send-combined.post');
        Route::get('/whatsapp-messages/{sentWhatsAppMessage}', [AdminController::class, 'showWhatsAppMessage'])
            ->name('whatsapp-messages.show');
        Route::delete('/whatsapp-messages/{sentWhatsAppMessage}', [AdminController::class, 'destroyWhatsAppMessage'])
            ->middleware('sso.validate')
            ->name('whatsapp-messages.destroy');
        
        // Email management
        Route::get('/emails/sent', [AdminController::class, 'sentEmails'])->name('emails.sent');
        Route::get('/emails/sent/{sentEmail}', [AdminController::class, 'showSentEmail'])->name('emails.sent.show');
        Route::delete('/emails/sent/{sentEmail}', [AdminController::class, 'destroySentEmail'])
            ->middleware('sso.validate')
            ->name('emails.sent.destroy');
        Route::get('/emails/scheduled', [AdminController::class, 'scheduledEmails'])->name('emails.scheduled');
        Route::get('/emails/scheduled/{scheduledEmail}', [AdminController::class, 'showScheduledEmail'])->name('emails.scheduled.show');
        Route::post('/emails/scheduled/{scheduledEmail}/cancel', [AdminController::class, 'cancelScheduledEmail'])
            ->middleware('sso.validate')
            ->name('emails.scheduled.cancel');
        Route::delete('/emails/scheduled/{scheduledEmail}', [AdminController::class, 'destroyScheduledEmail'])
            ->middleware('sso.validate')
            ->name('emails.scheduled.destroy');
        
        // Partners management
        Route::get('/partners', [AdminController::class, 'partners'])->name('partners');
        Route::post('/partners', [AdminController::class, 'storePartner'])
            ->middleware('sso.validate')
            ->name('partners.store');
        Route::get('/partners/{partner}/edit', [AdminController::class, 'editPartner'])->name('partners.edit');
        Route::put('/partners/{partner}', [AdminController::class, 'updatePartner'])
            ->middleware('sso.validate')
            ->name('partners.update');
        Route::delete('/partners/{partner}', [AdminController::class, 'destroyPartner'])
            ->middleware('sso.validate')
            ->name('partners.destroy');
        
        // Testimonials management
        Route::get('/testimonials', [AdminController::class, 'testimonials'])->name('testimonials');
        Route::post('/testimonials', [AdminController::class, 'storeTestimonial'])
            ->middleware('sso.validate')
            ->name('testimonials.store');
        Route::get('/testimonials/{testimonial}/edit', [AdminController::class, 'editTestimonial'])->name('testimonials.edit');
        Route::put('/testimonials/{testimonial}', [AdminController::class, 'updateTestimonial'])
            ->middleware('sso.validate')
            ->name('testimonials.update');
        Route::delete('/testimonials/{testimonial}', [AdminController::class, 'destroyTestimonial'])
            ->middleware('sso.validate')
            ->name('testimonials.destroy');
        
        // Reviews management
        Route::get('/reviews', [AdminController::class, 'reviews'])->name('reviews');
        Route::post('/reviews/{review}/approve', [AdminController::class, 'approveReview'])
            ->middleware('sso.validate')
            ->name('reviews.approve');
        Route::post('/reviews/{review}/reject', [AdminController::class, 'rejectReview'])
            ->middleware('sso.validate')
            ->name('reviews.reject');
        Route::delete('/reviews/{review}', [AdminController::class, 'deleteReview'])
            ->middleware('sso.validate')
            ->name('reviews.delete');
        
        // Certificates management
        Route::get('/certificates', [AdminController::class, 'certificates'])->name('certificates');
        Route::get('/certificates/{certificate}', [AdminController::class, 'showCertificate'])->name('certificates.show');
        Route::get('/certificates/{certificate}/download', [AdminController::class, 'downloadCertificate'])->name('certificates.download');
        Route::post('/certificates/{certificate}/regenerate', [AdminController::class, 'regenerateCertificate'])
            ->middleware('sso.validate')
            ->name('certificates.regenerate');
        Route::delete('/certificates/{certificate}', [AdminController::class, 'destroyCertificate'])
            ->middleware('sso.validate')
            ->name('certificates.destroy');
        
        // Banners management
        Route::resource('banners', BannerController::class)->middleware('sso.validate');
        Route::post('/banners/{banner}/toggle-active', [BannerController::class, 'toggleActive'])
            ->middleware('sso.validate')
            ->name('banners.toggle-active');
        
        // Orders management
        Route::get('/orders', [App\Http\Controllers\OrderController::class, 'adminIndex'])->name('orders.index');
        Route::get('/orders/{order}', [App\Http\Controllers\OrderController::class, 'adminShow'])->name('orders.show');
        Route::post('/orders/{order}/confirm', [App\Http\Controllers\OrderController::class, 'confirm'])
            ->middleware('sso.validate')
            ->name('orders.confirm');
        Route::post('/orders/{order}/mark-paid', [App\Http\Controllers\OrderController::class, 'markAsPaid'])
            ->middleware('sso.validate')
            ->name('orders.mark-paid');
        Route::post('/orders/{order}/mark-completed', [App\Http\Controllers\OrderController::class, 'markAsCompleted'])
            ->middleware('sso.validate')
            ->name('orders.mark-completed');
        Route::post('/orders/{order}/cancel', [App\Http\Controllers\OrderController::class, 'cancel'])
            ->middleware('sso.validate')
            ->name('orders.cancel');
        Route::post('/orders/{order}/delete', [App\Http\Controllers\OrderController::class, 'destroy'])
            ->middleware('sso.validate')
            ->name('orders.destroy');

        // Payments (transactions) management
        Route::get('/payments', [AdminController::class, 'payments'])->name('payments');

        // Uploads (AJAX) for admin - avec validation SSO
        Route::post('/uploads/lesson-file', [AdminController::class, 'uploadLessonFile'])
            ->middleware('sso.validate')
            ->name('uploads.lesson-file');
        Route::post('/uploads/video-preview', [AdminController::class, 'uploadVideoPreview'])
            ->middleware('sso.validate')
            ->name('uploads.video-preview');
        Route::get('/orders/filter', [App\Http\Controllers\OrderController::class, 'filter'])->name('orders.filter');
        Route::get('/orders/export', [App\Http\Controllers\OrderController::class, 'export'])->name('orders.export');
        
        // Settings management - avec validation SSO pour la modification
        Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
        Route::post('/settings', [AdminController::class, 'updateSettings'])
            ->middleware('sso.validate')
            ->name('settings.update');
        
        // Provider payouts management
        Route::get('/provider-payouts', [AdminController::class, 'providerPayouts'])->name('provider-payouts');
    });

    // Profile routes - avec validation SSO pour les modifications
    Route::get('/profile', function () {
        return view('admin.profile');
    })->name('profile');
    Route::match(['put', 'patch'], '/profile', [App\Http\Controllers\ProfileController::class, 'update'])
        ->middleware('sso.validate')
        ->name('profile.update');
    Route::post('/profile/avatar', [App\Http\Controllers\ProfileController::class, 'updateAvatar'])
        ->middleware('sso.validate')
        ->name('profile.avatar');
    Route::put('/profile/password', [App\Http\Controllers\ProfileController::class, 'updatePassword'])
        ->middleware('sso.validate')
        ->name('profile.password');
    Route::delete('/profile', [App\Http\Controllers\ProfileController::class, 'destroy'])
        ->middleware('sso.validate')
        ->name('profile.destroy');

    // Affiliate routes
    Route::prefix('affiliate')->name('affiliate.')->middleware('role:affiliate')->group(function () {
        Route::get('/dashboard', [AffiliateController::class, 'dashboard'])->name('dashboard');
        Route::get('/links', [AffiliateController::class, 'links'])->name('links');
        Route::get('/earnings', [AffiliateController::class, 'earnings'])->name('earnings');
    });

    // Payment routes (désactivées - nous utilisons uniquement Moneroo)
    // Route::prefix('payments')->name('payments.')->group(function () {
    //     Route::post('/process', [PaymentController::class, 'process'])->name('process');
    //     Route::get('/success', [PaymentController::class, 'success'])->name('success');
    //     Route::get('/cancel', [PaymentController::class, 'cancel'])->name('cancel');
    //     Route::post('/webhook/stripe', [PaymentController::class, 'webhook'])->name('webhook.stripe');
    // });

    // YouTube video access routes - avec validation SSO
    Route::prefix('video')->name('video.')->group(function () {
        Route::post('/lessons/{lesson}/access-token', [YouTubeAccessController::class, 'generateAccessToken'])
            ->middleware('sso.validate')
            ->name('generate-access-token');
        Route::post('/validate-token', [YouTubeAccessController::class, 'validateToken'])
            ->middleware('sso.validate')
            ->name('validate-token');
        Route::post('/cleanup-tokens', [YouTubeAccessController::class, 'cleanupExpiredTokens'])
            ->middleware('sso.validate')
            ->name('cleanup-tokens');
    });

    // Learning routes - avec validation SSO pour les actions de progression
    Route::get('/learning/courses/{course:slug}', [LearningController::class, 'learn'])->name('learning.course');
    Route::get('/learning/courses/{course:slug}/lessons/{lesson}', [LearningController::class, 'lesson'])->name('learning.lesson');
    Route::post('/learning/courses/{course:slug}/lessons/{lesson}/start', [LearningController::class, 'startLesson'])
        ->middleware('sso.validate')
        ->name('learning.start-lesson');
    Route::post('/learning/courses/{course:slug}/lessons/{lesson}/progress', [LearningController::class, 'updateProgress'])
        ->middleware('sso.validate')
        ->name('learning.update-progress');
    Route::post('/learning/courses/{course:slug}/lessons/{lesson}/complete', [LearningController::class, 'completeLesson'])
        ->middleware('sso.validate')
        ->name('learning.complete-lesson');
    Route::post('/learning/courses/{course:slug}/lessons/{lesson}/submit', [LearningController::class, 'submitQuiz'])
        ->middleware('sso.validate')
        ->name('learning.submit-quiz');

    // Lesson Notes routes
    Route::prefix('/learning/courses/{course:slug}/lessons/{lesson}')->group(function () {
        Route::get('/notes/all', [App\Http\Controllers\LessonNoteController::class, 'all'])->name('learning.notes.all');
        Route::get('/notes', [App\Http\Controllers\LessonNoteController::class, 'index'])->name('learning.notes.index');
        Route::post('/notes', [App\Http\Controllers\LessonNoteController::class, 'store'])
            ->middleware('sso.validate')
            ->name('learning.notes.store');
        // Route GET pour gérer les accès accidentels (redirige vers la page des notes)
        Route::get('/notes/{note}', function (App\Models\Course $course, App\Models\CourseLesson $lesson) {
            return redirect()->route('learning.notes.all', ['course' => $course->slug, 'lesson' => $lesson->id]);
        })->name('learning.notes.show');
        Route::put('/notes/{note}', [App\Http\Controllers\LessonNoteController::class, 'update'])
            ->middleware('sso.validate')
            ->name('learning.notes.update');
        Route::delete('/notes/{note}', [App\Http\Controllers\LessonNoteController::class, 'destroy'])
            ->middleware('sso.validate')
            ->name('learning.notes.destroy');
    });

    // Lesson Resources routes
    Route::prefix('/learning/courses/{course:slug}/lessons/{lesson}')->group(function () {
        Route::get('/resources', [App\Http\Controllers\LessonResourceController::class, 'index'])->name('learning.resources.index');
        Route::get('/resources/{resource}/download', [App\Http\Controllers\LessonResourceController::class, 'download'])->name('learning.resources.download');
    });

    // Lesson Discussions routes
    Route::prefix('/learning/courses/{course:slug}/lessons/{lesson}')->group(function () {
        Route::get('/discussions/all', [App\Http\Controllers\LessonDiscussionController::class, 'all'])->name('learning.discussions.all');
        Route::get('/discussions', [App\Http\Controllers\LessonDiscussionController::class, 'index'])->name('learning.discussions.index');
        Route::post('/discussions', [App\Http\Controllers\LessonDiscussionController::class, 'store'])
            ->middleware('sso.validate')
            ->name('learning.discussions.store');
        Route::put('/discussions/{discussion}', [App\Http\Controllers\LessonDiscussionController::class, 'update'])
            ->middleware('sso.validate')
            ->name('learning.discussions.update');
        Route::delete('/discussions/{discussion}', [App\Http\Controllers\LessonDiscussionController::class, 'destroy'])
            ->middleware('sso.validate')
            ->name('learning.discussions.destroy');
        Route::post('/discussions/{discussion}/like', [App\Http\Controllers\LessonDiscussionController::class, 'toggleLike'])
            ->middleware('sso.validate')
            ->name('learning.discussions.like');
        Route::post('/discussions/{discussion}/pin', [App\Http\Controllers\LessonDiscussionController::class, 'togglePin'])
            ->middleware('sso.validate')
            ->name('learning.discussions.pin');
        Route::post('/discussions/{discussion}/answered', [App\Http\Controllers\LessonDiscussionController::class, 'markAsAnswered'])
            ->middleware('sso.validate')
            ->name('learning.discussions.answered');
    });

    // Download routes
    Route::get('/contents/{course:slug}/download', [DownloadController::class, 'course'])->name('contents.download');
    Route::get('/courses/{course:slug}/lesson/{lesson}/download', [DownloadController::class, 'lesson'])->name('lessons.download');


    // Messaging routes - avec validation SSO pour les actions de modification
    Route::prefix('messages')->name('messages.')->group(function () {
        Route::get('/', [MessageController::class, 'index'])->name('index');
        Route::get('/create', [MessageController::class, 'create'])->name('create');
        Route::post('/', [MessageController::class, 'store'])
            ->middleware('sso.validate')
            ->name('store');
        Route::get('/{message}', [MessageController::class, 'show'])->name('show');
        Route::post('/{message}/reply', [MessageController::class, 'reply'])
            ->middleware('sso.validate')
            ->name('reply');
        Route::post('/{message}/mark-read', [MessageController::class, 'markAsRead'])
            ->middleware('sso.validate')
            ->name('mark-read');
        Route::delete('/{message}', [MessageController::class, 'delete'])
            ->middleware('sso.validate')
            ->name('delete');
    });

    // Notification routes - avec validation SSO pour les actions de modification
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/{id}/mark-read', [NotificationController::class, 'markAsRead'])
            ->middleware('sso.validate')
            ->name('mark-read');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])
            ->middleware('sso.validate')
            ->name('mark-all-read');
        Route::delete('/{id}', [NotificationController::class, 'delete'])
            ->middleware('sso.validate')
            ->name('delete');
        Route::delete('/', [NotificationController::class, 'deleteAll'])
            ->middleware('sso.validate')
            ->name('delete-all');
        Route::get('/unread-count', [NotificationController::class, 'getUnreadCount'])->name('unread-count');
        Route::get('/recent', [NotificationController::class, 'getRecent'])->name('recent');
    });

    // Notifications – routes POST/DELETE protégées par auth
        Route::post('/notifications/{id}/mark-read', [NotificationController::class, 'markAsRead'])
            ->name('notifications.mark-read');
        Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])
            ->name('notifications.mark-all-read');
        Route::delete('/notifications/{id}', [NotificationController::class, 'delete'])
            ->name('notifications.delete');
        Route::delete('/notifications', [NotificationController::class, 'deleteAll'])
            ->name('notifications.delete-all');
});

// Notifications – routes GET publiques (gèrent gracieusement les utilisateurs non authentifiés)
Route::get('/notifications/unread-count', [NotificationController::class, 'getUnreadCount'])
    ->name('notifications.unread-count');
Route::get('/notifications/recent', [NotificationController::class, 'getRecent'])
    ->name('notifications.recent');

// Authenticated routes
Route::middleware('auth')->group(function () {
    // Affiliate routes
    Route::prefix('affiliate')->name('affiliate.')->middleware('role:affiliate')->group(function () {
        Route::get('/dashboard', [AffiliateController::class, 'dashboard'])->name('dashboard');
        Route::get('/links', [AffiliateController::class, 'links'])->name('links');
        Route::get('/earnings', [AffiliateController::class, 'earnings'])->name('earnings');
        Route::post('/generate-link', [AffiliateController::class, 'generateLink'])->name('generate-link');
        Route::post('/update-profile', [AffiliateController::class, 'updateProfile'])->name('update-profile');
        Route::post('/request-payout', [AffiliateController::class, 'requestPayout'])->name('request-payout');
    });

    // Profile routes (handled above)
});

Route::post('/provider/uploads/chunk', [ChunkUploadController::class, 'handle'])
    ->middleware('auth')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])
    ->name('provider.uploads.chunk');

Route::post('/admin/uploads/chunk', [ChunkUploadController::class, 'handle'])
    ->middleware('auth')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])
    ->name('admin.uploads.chunk');



// Moneroo routes
Route::prefix('moneroo')->name('moneroo.')->group(function () {
    // Méthodes de paiement disponibles (publiques pour permettre le chargement avant connexion)
    Route::get('/methods', [MonerooController::class, 'availableMethods'])->name('methods');
    
    // Endpoints nécessitant l'auth (depuis le checkout)
    Route::middleware('auth')->group(function () {
        Route::post('/initiate', [MonerooController::class, 'initiate'])->name('initiate');
        Route::get('/status/{paymentId}', [MonerooController::class, 'status'])->name('status');
        Route::post('/cancel/{paymentId}', [MonerooController::class, 'cancel'])->name('cancel');
        Route::post('/cancel-latest', [MonerooController::class, 'cancelLatestPending'])->name('cancel-latest');
        Route::post('/report-failure', [MonerooController::class, 'reportClientSideFailure'])->name('report-failure');
    });

    // Redirections de succès/échec (publiques car Moneroo peut rappeler sans session)
    Route::get('/success', [MonerooController::class, 'successfulRedirect'])->name('success');
    Route::get('/failed', [MonerooController::class, 'failedRedirect'])->name('failed');

    // Webhook callback from Moneroo (no CSRF)
    Route::post('/webhook', [MonerooController::class, 'webhook'])
        ->name('webhook')
        ->withoutMiddleware(['web']);
    
    // Webhook callback for wallet payouts from Moneroo (no CSRF)
    Route::post('/webhook/payout', [App\Http\Controllers\WalletController::class, 'webhookPayout'])
        ->name('webhook.payout')
        ->withoutMiddleware(['web']);
});

// Moneroo Payout routes (pour les paiements aux formateurs externes)
Route::prefix('api/moneroo/payout')->name('moneroo.payout.')->group(function () {
    // Callback pour les payouts (pas de CSRF car appelé par Moneroo)
    Route::post('/callback', [MonerooPayoutController::class, 'callback'])
        ->name('callback')
        ->withoutMiddleware(['web']);

    // Vérifier le statut d'un payout (nécessite auth pour l'admin)
    Route::middleware('auth')->group(function () {
        Route::get('/status/{payoutId}', [MonerooPayoutController::class, 'checkStatus'])
            ->name('status');
    });
});

