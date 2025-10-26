<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\AffiliateController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\VideoStreamController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\LearningController;
use App\Http\Controllers\WhatsAppOrderController;
use App\Http\Controllers\FilterController;
use App\Http\Controllers\MokoController;
use App\Http\Controllers\MaxiCashController;
use Illuminate\Support\Facades\Route;

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

// Test route for lessons
Route::get('/test-lessons', function() {
    $course = App\Models\Course::with('sections.lessons')->first();
    return response()->json([
        'course' => $course->title,
        'sections_count' => $course->sections->count(),
        'lessons_count' => $course->lessons->count(),
        'sections' => $course->sections->map(function($section) {
            return [
                'title' => $section->title,
                'lessons_count' => $section->lessons->count(),
                'lessons' => $section->lessons->map(function($lesson) {
                    return [
                        'title' => $lesson->title,
                        'type' => $lesson->type,
                        'is_preview' => $lesson->is_preview
                    ];
                })
            ];
        })
    ]);
});

// Test route for lessons view
Route::get('/test-lessons-view', function() {
    $course = App\Models\Course::with('sections.lessons')->first();
    return view('admin.courses.lessons.index', compact('course'));
});

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
Route::get('/courses/{course:slug}/lesson/{lesson}', [CourseController::class, 'lesson'])->name('courses.lesson');
Route::get('/categories/{category:slug}', [CourseController::class, 'byCategory'])->name('courses.category');
Route::get('/instructors', [InstructorController::class, 'index'])->name('instructors.index');
Route::get('/instructors/{instructor}', [InstructorController::class, 'show'])->name('instructors.show');

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

// WhatsApp Order routes
Route::middleware('auth')->group(function () {
    Route::post('/whatsapp-order/create', [WhatsAppOrderController::class, 'createOrder'])->name('whatsapp.order.create');
});


// Order management routes
Route::middleware('auth')->group(function () {
    // Student order routes
    Route::get('/orders', [App\Http\Controllers\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [App\Http\Controllers\OrderController::class, 'show'])->name('orders.show');
});

// Authentication routes
require __DIR__.'/auth.php';

// Cart routes (accessible to all users)
Route::middleware('sync.cart')->group(function () {
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::delete('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
    Route::delete('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');
    Route::get('/cart/count', [CartController::class, 'count'])->name('cart.count');
    Route::get('/cart/content', [CartController::class, 'getCartContent'])->name('cart.content');
    Route::get('/cart/summary', [CartController::class, 'getSummary'])->name('cart.summary');
    Route::get('/cart/recommendations', [CartController::class, 'getRecommendations'])->name('cart.recommendations');
    Route::get('/cart/checkout', [CartController::class, 'checkout'])->name('cart.checkout');
});

// Authenticated routes
Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard based on user role
    Route::get('/dashboard', function () {
        $user = auth()->user();
        return match($user->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'instructor' => redirect()->route('instructor.dashboard'),
            'affiliate' => redirect()->route('affiliate.dashboard'),
            default => redirect()->route('student.dashboard'),
        };
    })->name('dashboard');

    // Student routes
    Route::prefix('student')->name('student.')->middleware('role:student')->group(function () {
        Route::get('/dashboard', [StudentController::class, 'dashboard'])->name('dashboard');
        Route::get('/courses', [StudentController::class, 'courses'])->name('courses');
        Route::get('/courses/{course:slug}/learn', [StudentController::class, 'learn'])->name('courses.learn');
        Route::post('/courses/{course:slug}/enroll', [StudentController::class, 'enroll'])->name('courses.enroll');
        Route::get('/certificates', [StudentController::class, 'certificates'])->name('certificates');
    });

    // Instructor routes
    Route::prefix('instructor')->name('instructor.')->middleware('role:instructor')->group(function () {
        Route::get('/dashboard', [InstructorController::class, 'dashboard'])->name('dashboard');
        Route::resource('courses', CourseController::class)->except(['index', 'show']);
        Route::get('/courses/{course}/lessons', [InstructorController::class, 'lessons'])->name('courses.lessons');
        Route::post('/courses/{course}/lessons', [InstructorController::class, 'storeLesson'])->name('courses.lessons.store');
        Route::get('/students', [InstructorController::class, 'students'])->name('students');
        Route::get('/analytics', [InstructorController::class, 'analytics'])->name('analytics');
    });

    // Admin routes
    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/analytics', [AdminController::class, 'analytics'])->name('analytics');
        Route::get('/statistics', [AdminController::class, 'statistics'])->name('statistics');
        Route::post('/courses/{course}/recalculate-stats', [AdminController::class, 'recalculateCourseStats'])->name('courses.recalculate-stats');
        Route::post('/statistics/recalculate-all', [AdminController::class, 'recalculateAllStats'])->name('statistics.recalculate-all');
        
        // Users management
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::get('/users/create', [AdminController::class, 'createUser'])->name('users.create');
        Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
        Route::get('/users/{user}', [AdminController::class, 'showUser'])->name('users.show');
        Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('users.edit');
        Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
        Route::delete('/users/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');
        
        // Categories management
        Route::get('/categories', [AdminController::class, 'categories'])->name('categories');
        Route::post('/categories', [AdminController::class, 'storeCategory'])->name('categories.store');
        Route::get('/categories/{category}/edit', [AdminController::class, 'editCategory'])->name('categories.edit');
        Route::put('/categories/{category}', [AdminController::class, 'updateCategory'])->name('categories.update');
        Route::delete('/categories/{category}', [AdminController::class, 'destroyCategory'])->name('categories.destroy');
        
        // Courses management
        Route::get('/courses', [AdminController::class, 'courses'])->name('courses');
        Route::get('/courses/create', [AdminController::class, 'createCourse'])->name('courses.create');
        Route::post('/courses', [AdminController::class, 'storeCourse'])->name('courses.store');
        Route::get('/courses/{course}', [AdminController::class, 'showCourse'])->name('courses.show');
        Route::get('/courses/{course}/edit', [AdminController::class, 'editCourse'])->name('courses.edit');
        Route::put('/courses/{course}', [AdminController::class, 'updateCourse'])->name('courses.update');
        Route::delete('/courses/{course}', [AdminController::class, 'destroyCourse'])->name('courses.destroy');
        
        // Course lessons management
        Route::get('/courses/{course}/lessons', [AdminController::class, 'courseLessons'])->name('courses.lessons');
        Route::get('/courses/{course}/lessons/create', [AdminController::class, 'createLesson'])->name('courses.lessons.create');
        Route::post('/courses/{course}/lessons', [AdminController::class, 'storeLesson'])->name('courses.lessons.store');
        Route::get('/lessons/{lesson}/edit', [AdminController::class, 'editLesson'])->name('lessons.edit');
        Route::put('/lessons/{lesson}', [AdminController::class, 'updateLesson'])->name('lessons.update');
        Route::delete('/lessons/{lesson}', [AdminController::class, 'destroyLesson'])->name('lessons.destroy');
        
        // Announcements management
        Route::get('/announcements', [AdminController::class, 'announcements'])->name('announcements');
        Route::post('/announcements', [AdminController::class, 'storeAnnouncement'])->name('announcements.store');
        Route::get('/announcements/{announcement}/edit', [AdminController::class, 'editAnnouncement'])->name('announcements.edit');
        Route::put('/announcements/{announcement}', [AdminController::class, 'updateAnnouncement'])->name('announcements.update');
        Route::delete('/announcements/{announcement}', [AdminController::class, 'destroyAnnouncement'])->name('announcements.destroy');
        
        // Partners management
        Route::get('/partners', [AdminController::class, 'partners'])->name('partners');
        Route::post('/partners', [AdminController::class, 'storePartner'])->name('partners.store');
        Route::get('/partners/{partner}/edit', [AdminController::class, 'editPartner'])->name('partners.edit');
        Route::put('/partners/{partner}', [AdminController::class, 'updatePartner'])->name('partners.update');
        Route::delete('/partners/{partner}', [AdminController::class, 'destroyPartner'])->name('partners.destroy');
        
        // Testimonials management
        Route::get('/testimonials', [AdminController::class, 'testimonials'])->name('testimonials');
        Route::post('/testimonials', [AdminController::class, 'storeTestimonial'])->name('testimonials.store');
        Route::get('/testimonials/{testimonial}/edit', [AdminController::class, 'editTestimonial'])->name('testimonials.edit');
        Route::put('/testimonials/{testimonial}', [AdminController::class, 'updateTestimonial'])->name('testimonials.update');
        Route::delete('/testimonials/{testimonial}', [AdminController::class, 'destroyTestimonial'])->name('testimonials.destroy');
        
        // Orders management
        Route::get('/orders', [App\Http\Controllers\OrderController::class, 'adminIndex'])->name('orders.index');
        Route::get('/orders/{order}', [App\Http\Controllers\OrderController::class, 'adminShow'])->name('orders.show');
        Route::post('/orders/{order}/confirm', [App\Http\Controllers\OrderController::class, 'confirm'])->name('orders.confirm');
        Route::post('/orders/{order}/mark-paid', [App\Http\Controllers\OrderController::class, 'markAsPaid'])->name('orders.mark-paid');
        Route::post('/orders/{order}/mark-completed', [App\Http\Controllers\OrderController::class, 'markAsCompleted'])->name('orders.mark-completed');

        // Uploads (AJAX) for admin
        Route::post('/uploads/lesson-file', [AdminController::class, 'uploadLessonFile'])->name('uploads.lesson-file');
        Route::post('/uploads/video-preview', [AdminController::class, 'uploadVideoPreview'])->name('uploads.video-preview');
        Route::post('/orders/{order}/cancel', [App\Http\Controllers\OrderController::class, 'cancel'])->name('orders.cancel');
        Route::get('/orders/filter', [App\Http\Controllers\OrderController::class, 'filter'])->name('orders.filter');
        Route::get('/orders/export', [App\Http\Controllers\OrderController::class, 'export'])->name('orders.export');
    });

    // Profile routes
    Route::get('/profile', function () {
        return view('admin.profile');
    })->name('profile');
    Route::put('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/avatar', [App\Http\Controllers\ProfileController::class, 'updateAvatar'])->name('profile.avatar');
    Route::put('/profile/password', [App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.password');

    // Affiliate routes
    Route::prefix('affiliate')->name('affiliate.')->middleware('role:affiliate')->group(function () {
        Route::get('/dashboard', [AffiliateController::class, 'dashboard'])->name('dashboard');
        Route::get('/links', [AffiliateController::class, 'links'])->name('links');
        Route::get('/earnings', [AffiliateController::class, 'earnings'])->name('earnings');
    });

    // Payment routes
    Route::prefix('payments')->name('payments.')->group(function () {
        Route::post('/process', [PaymentController::class, 'process'])->name('process');
        Route::get('/success', [PaymentController::class, 'success'])->name('success');
        Route::get('/cancel', [PaymentController::class, 'cancel'])->name('cancel');
        Route::post('/webhook/stripe', [PaymentController::class, 'webhook'])->name('webhook.stripe');
    });

    // Video streaming routes
    Route::get('/video/{lessonId}/stream', [VideoStreamController::class, 'stream'])->name('video.stream');
    Route::get('/video/{lessonId}/download', [VideoStreamController::class, 'download'])->name('video.download');

    // Learning routes
    Route::get('/learning/courses/{course:slug}', [LearningController::class, 'learn'])->name('learning.course');
    Route::get('/learning/courses/{course:slug}/lessons/{lesson}', [LearningController::class, 'lesson'])->name('learning.lesson');
    Route::post('/learning/courses/{course:slug}/lessons/{lesson}/start', [LearningController::class, 'startLesson'])->name('learning.start-lesson');
    Route::post('/learning/courses/{course:slug}/lessons/{lesson}/progress', [LearningController::class, 'updateProgress'])->name('learning.update-progress');
    Route::post('/learning/courses/{course:slug}/lessons/{lesson}/complete', [LearningController::class, 'completeLesson'])->name('learning.complete-lesson');

    // Download routes
    Route::get('/courses/{course:slug}/download', [DownloadController::class, 'course'])->name('courses.download');
    Route::get('/courses/{course:slug}/lesson/{lesson}/download', [DownloadController::class, 'lesson'])->name('lessons.download');


    // Messaging routes
    Route::prefix('messages')->name('messages.')->group(function () {
        Route::get('/', [MessageController::class, 'index'])->name('index');
        Route::get('/create', [MessageController::class, 'create'])->name('create');
        Route::post('/', [MessageController::class, 'store'])->name('store');
        Route::get('/{message}', [MessageController::class, 'show'])->name('show');
        Route::post('/{message}/reply', [MessageController::class, 'reply'])->name('reply');
        Route::post('/{message}/mark-read', [MessageController::class, 'markAsRead'])->name('mark-read');
        Route::delete('/{message}', [MessageController::class, 'delete'])->name('delete');
    });

    // Notification routes
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/{id}/mark-read', [NotificationController::class, 'markAsRead'])->name('mark-read');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::delete('/{id}', [NotificationController::class, 'delete'])->name('delete');
        Route::delete('/', [NotificationController::class, 'deleteAll'])->name('delete-all');
        Route::get('/unread-count', [NotificationController::class, 'getUnreadCount'])->name('unread-count');
        Route::get('/recent', [NotificationController::class, 'getRecent'])->name('recent');
    });

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

// MOKO Afrika Payment Routes
Route::prefix('moko')->name('moko.')->group(function () {
    // Public routes
    Route::get('/payment', [MokoController::class, 'showPaymentForm'])->name('payment');
    Route::post('/initiate', [MokoController::class, 'initiatePayment'])->name('initiate');
    Route::get('/status/{reference}', [MokoController::class, 'checkStatus'])->name('status');
    Route::get('/success', [MokoController::class, 'success'])->name('success');
    Route::get('/failure', [MokoController::class, 'failure'])->name('failure');
    
    // Callback route (no CSRF protection needed)
    Route::post('/callback', [MokoController::class, 'handleCallback'])
        ->name('callback')
        ->withoutMiddleware(['web']);
});

// MaxiCash payment routes
Route::prefix('maxicash')->name('maxicash.')->middleware('auth')->group(function () {
    Route::post('/process', [MaxiCashController::class, 'process'])->name('process');
    Route::get('/success', [MaxiCashController::class, 'success'])->name('success');
    Route::get('/cancel', [MaxiCashController::class, 'cancel'])->name('cancel');
    Route::get('/failure', [MaxiCashController::class, 'failure'])->name('failure');
    
    // Notification route (no CSRF protection needed)
    Route::post('/notify', [MaxiCashController::class, 'notify'])
        ->name('notify')
        ->withoutMiddleware(['web']);
});

