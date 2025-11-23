<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\ChunkUploadController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\InstructorApplicationController;
use App\Http\Controllers\StudentController;
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
use App\Http\Controllers\PawaPayController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\TemporaryUploadController;
use App\Http\Controllers\Auth\SSOController;
use App\Http\Controllers\SSOCallbackController;
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
Route::post('/courses/filter', [FilterController::class, 'filterCourses'])->name('courses.filter');
Route::get('/courses/filter-options', [FilterController::class, 'getFilterOptions'])->name('courses.filter-options');
Route::get('/courses/search', [FilterController::class, 'searchCourses'])->name('courses.search');

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
Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
Route::get('/courses/{course:slug}', [CourseController::class, 'show'])->name('courses.show');
Route::get('/courses/{course:slug}/preview-data', [CourseController::class, 'previewData'])->name('courses.preview-data');
Route::get('/courses/{course:slug}/lesson/{lesson}', [CourseController::class, 'lesson'])->name('courses.lesson');
Route::get('/categories/{category:slug}', [CourseController::class, 'byCategory'])->name('courses.category');
Route::get('/instructors', [InstructorController::class, 'index'])->name('instructors.index');
Route::get('/instructors/{instructor}', [InstructorController::class, 'show'])->name('instructors.show');
Route::get('/become-instructor', [InstructorApplicationController::class, 'index'])->name('instructor-application.index');

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
    // Student order routes (moved under /students/ordres)
    Route::get('/students/ordres', [App\Http\Controllers\OrderController::class, 'index'])->name('orders.index');
    Route::get('/students/ordres/{order}', [App\Http\Controllers\OrderController::class, 'show'])->name('orders.show');
});

// SSO routes (must be before auth routes)
Route::get('/sso/callback', [SSOCallbackController::class, 'handle'])->name('sso.callback');

// Authentication routes
require __DIR__.'/auth.php';

// Cart routes (accessible to all users) - avec validation SSO pour les actions de modification
Route::middleware('sync.cart')->group(function () {
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add', [CartController::class, 'add'])
        ->middleware('sso.validate')
        ->name('cart.add');
    Route::delete('/cart/remove', [CartController::class, 'remove'])
        ->middleware('sso.validate')
        ->name('cart.remove');
    Route::delete('/cart/clear', [CartController::class, 'clear'])
        ->middleware('sso.validate')
        ->name('cart.clear');
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
            'instructor' => redirect()->route('instructor.dashboard'),
            'affiliate' => redirect()->route('affiliate.dashboard'),
            default => redirect()->route('student.dashboard'),
        };
    })->name('dashboard');

    Route::match(['POST', 'DELETE'], '/uploads/temp', [TemporaryUploadController::class, 'destroy'])
        ->name('uploads.temp.destroy');

    // Student routes
    Route::prefix('student')->name('student.')->middleware('role:student')->group(function () {
        Route::get('/dashboard', [StudentController::class, 'dashboard'])->name('dashboard');
        Route::get('/courses', [StudentController::class, 'courses'])->name('courses');
        Route::get('/certificates', [StudentController::class, 'certificates'])->name('certificates');
    });
    
    // Enrollment route (accessible to all authenticated users) - avec validation SSO
    Route::post('/student/courses/{course:slug}/enroll', [StudentController::class, 'enroll'])
        ->middleware('sso.validate')
        ->name('student.courses.enroll');

    // Instructor Application routes (accessible to authenticated users) - avec validation SSO pour les POST
    Route::middleware('auth')->group(function () {
        Route::get('/instructor-application/create', [App\Http\Controllers\InstructorApplicationController::class, 'create'])->name('instructor-application.create');
        Route::post('/instructor-application/step1', [App\Http\Controllers\InstructorApplicationController::class, 'storeStep1'])
            ->middleware('sso.validate')
            ->name('instructor-application.store-step1');
        Route::get('/instructor-application/{application}/step2', [App\Http\Controllers\InstructorApplicationController::class, 'step2'])->name('instructor-application.step2');
        Route::post('/instructor-application/{application}/step2', [App\Http\Controllers\InstructorApplicationController::class, 'storeStep2'])
            ->middleware('sso.validate')
            ->name('instructor-application.store-step2');
        Route::get('/instructor-application/{application}/step3', [App\Http\Controllers\InstructorApplicationController::class, 'step3'])->name('instructor-application.step3');
        Route::post('/instructor-application/{application}/step3', [App\Http\Controllers\InstructorApplicationController::class, 'storeStep3'])
            ->middleware('sso.validate')
            ->name('instructor-application.store-step3');
        Route::get('/instructor-application/{application}/status', [App\Http\Controllers\InstructorApplicationController::class, 'status'])->name('instructor-application.status');
        Route::get('/instructor-application/{application}/cv', [App\Http\Controllers\InstructorApplicationController::class, 'downloadCv'])->name('instructor-application.download-cv');
        Route::get('/instructor-application/{application}/motivation-letter', [App\Http\Controllers\InstructorApplicationController::class, 'downloadMotivationLetter'])->name('instructor-application.download-motivation-letter');
    Route::delete('/instructor-application/{application}', [App\Http\Controllers\InstructorApplicationController::class, 'abandon'])
        ->middleware('sso.validate')
        ->name('instructor-application.abandon');
    });

    // Instructor routes (only for approved instructors) - avec validation SSO pour les POST/PUT/DELETE
    Route::prefix('instructor')->name('instructor.')->middleware('role:instructor')->group(function () {
        Route::get('/courses/list', function () {
            return redirect()->route('instructor.courses.index');
        });
        Route::get('/courses', [InstructorController::class, 'coursesIndex'])->name('courses.index');
        Route::get('/dashboard', [InstructorController::class, 'dashboard'])->name('dashboard');
        Route::resource('courses', CourseController::class)->except(['index', 'show'])->middleware('sso.validate');
        Route::get('/courses/{course}/lessons', [InstructorController::class, 'lessons'])->name('courses.lessons');
        Route::post('/courses/{course}/lessons', [InstructorController::class, 'storeLesson'])
            ->middleware('sso.validate')
            ->name('courses.lessons.store');
        Route::get('/students', [InstructorController::class, 'students'])->name('students');
        Route::get('/analytics', [InstructorController::class, 'analytics'])->name('analytics');
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
        
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
        Route::get('/analytics/revenue-by-instructor', [AdminController::class, 'getRevenueByInstructor'])->name('analytics.revenue-by-instructor');
        Route::get('/statistics', [AdminController::class, 'statistics'])->name('statistics');
        Route::post('/courses/{course}/recalculate-stats', [AdminController::class, 'recalculateCourseStats'])
            ->middleware('sso.validate')
            ->name('courses.recalculate-stats');
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
        Route::delete('/users/{user}/courses/{course}/revoke-access', [AdminController::class, 'revokeCourseAccess'])
            ->middleware('sso.validate')
            ->name('users.revoke-course-access');
        Route::delete('/users/{user}/courses/{course}/unenroll', [AdminController::class, 'unenrollUser'])
            ->middleware('sso.validate')
            ->name('users.unenroll');
        
        // Instructor Applications management
        Route::get('/instructor-applications', [AdminController::class, 'instructorApplications'])->name('instructor-applications');
        Route::get('/instructor-applications/{application}', [AdminController::class, 'showInstructorApplication'])->name('instructor-applications.show');
        Route::put('/instructor-applications/{application}/status', [AdminController::class, 'updateInstructorApplicationStatus'])
            ->middleware('sso.validate')
            ->name('instructor-applications.update-status');
        
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
        Route::get('/courses', [AdminController::class, 'courses'])->name('courses');
        Route::get('/courses/create', [AdminController::class, 'createCourse'])->name('courses.create');
        Route::post('/courses', [AdminController::class, 'storeCourse'])
            ->middleware('sso.validate')
            ->name('courses.store');
        Route::get('/courses/{course}', [AdminController::class, 'showCourse'])->name('courses.show');
        Route::get('/courses/{course}/edit', [AdminController::class, 'editCourse'])->name('courses.edit');
        Route::put('/courses/{course}', [AdminController::class, 'updateCourse'])
            ->middleware('sso.validate')
            ->name('courses.update');
        Route::delete('/courses/{course}', [AdminController::class, 'destroyCourse'])
            ->middleware('sso.validate')
            ->name('courses.destroy');
        
        // Course lessons management (disabled - legacy routes removed)
        
        // Announcements management
        Route::get('/announcements', [AdminController::class, 'announcements'])->name('announcements');
        Route::post('/announcements', [AdminController::class, 'storeAnnouncement'])
            ->middleware('sso.validate')
            ->name('announcements.store');
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

        // Payments (transactions) management
        Route::get('/payments', [AdminController::class, 'payments'])->name('payments');

        // Uploads (AJAX) for admin - avec validation SSO
        Route::post('/uploads/lesson-file', [AdminController::class, 'uploadLessonFile'])
            ->middleware('sso.validate')
            ->name('uploads.lesson-file');
        Route::post('/uploads/video-preview', [AdminController::class, 'uploadVideoPreview'])
            ->middleware('sso.validate')
            ->name('uploads.video-preview');
        Route::post('/orders/{order}/cancel', [App\Http\Controllers\OrderController::class, 'cancel'])
            ->middleware('sso.validate')
            ->name('orders.cancel');
        Route::get('/orders/filter', [App\Http\Controllers\OrderController::class, 'filter'])->name('orders.filter');
        Route::get('/orders/export', [App\Http\Controllers\OrderController::class, 'export'])->name('orders.export');
        
        // Settings management - avec validation SSO pour la modification
        Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
        Route::post('/settings', [AdminController::class, 'updateSettings'])
            ->middleware('sso.validate')
            ->name('settings.update');
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

    // Payment routes (désactivées - nous utilisons uniquement pawaPay)
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
    Route::get('/courses/{course:slug}/download', [DownloadController::class, 'course'])->name('courses.download');
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

Route::post('/instructor/uploads/chunk', [ChunkUploadController::class, 'handle'])
    ->middleware('auth')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])
    ->name('instructor.uploads.chunk');

Route::post('/admin/uploads/chunk', [ChunkUploadController::class, 'handle'])
    ->middleware('auth')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])
    ->name('admin.uploads.chunk');



// pawaPay routes
Route::prefix('pawapay')->name('pawapay.')->group(function () {
    // Endpoints nécessitant l'auth (depuis le checkout)
    Route::middleware('auth')->group(function () {
        Route::get('/active-conf', [PawaPayController::class, 'activeConf'])->name('active-conf');
        Route::post('/initiate', [PawaPayController::class, 'initiate'])->name('initiate');
        Route::get('/status/{depositId}', [PawaPayController::class, 'status'])->name('status');
        Route::post('/cancel/{depositId}', [PawaPayController::class, 'cancel'])->name('cancel');
        Route::post('/cancel-latest', [PawaPayController::class, 'cancelLatestPending'])->name('cancel-latest');
    });

    // Redirections de succès/échec (publiques car pawaPay peut rappeler sans session)
    Route::get('/success', [PawaPayController::class, 'successfulRedirect'])->name('success');
    Route::get('/failed', [PawaPayController::class, 'failedRedirect'])->name('failed');

    // Webhook callback from pawaPay (no CSRF)
    Route::post('/webhook', [PawaPayController::class, 'webhook'])
        ->name('webhook')
        ->withoutMiddleware(['web']);
});

