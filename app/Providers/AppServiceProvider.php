<?php

namespace App\Providers;

use App\Models\Announcement;
use App\Models\Course;
use App\Models\CourseLesson;
use App\Services\SubscriptionService;
use App\Observers\CourseLessonObserver;
use App\Events\CourseCompleted;
use App\Listeners\GenerateCertificateOnCourseCompletion;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $uploadLimits = config('app.upload_limits', []);

        if (!empty($uploadLimits['upload_max_filesize'])) {
            @ini_set('upload_max_filesize', (string) $uploadLimits['upload_max_filesize']);
        }

        if (!empty($uploadLimits['post_max_size'])) {
            @ini_set('post_max_size', (string) $uploadLimits['post_max_size']);
        }

        if (!empty($uploadLimits['max_execution_time'])) {
            @ini_set('max_execution_time', (string) $uploadLimits['max_execution_time']);
        }

        if (!empty($uploadLimits['max_input_time'])) {
            @ini_set('max_input_time', (string) $uploadLimits['max_input_time']);
        }

        // Partager la devise de base avec toutes les vues
        try {
            View::share('baseCurrency', \App\Models\Setting::getBaseCurrency());
        } catch (\Exception $e) {
            // Si la table settings n'existe pas encore, utiliser USD par défaut
            View::share('baseCurrency', 'USD');
        }

        View::composer('layouts.app', function ($view) {
            if (request()->is('admin') || request()->is('admin/*')) {
                $view->with('globalAnnouncement', null);
                return;
            }

            $announcement = Announcement::active()
                ->orderByRaw('COALESCE(starts_at, created_at) ASC')
                ->latest('created_at')
                ->first();

            $view->with('globalAnnouncement', $announcement);
        });

        // Rien à faire - FileHelper est accessible directement dans les vues via \App\Helpers\FileHelper
        
        // Enregistrer les événements
        Event::listen(
            CourseCompleted::class,
            GenerateCertificateOnCourseCompletion::class
        );

        CourseLesson::observe(CourseLessonObserver::class);

        Course::saved(function (Course $course) {
            if (! $course->is_published || $course->is_downloadable) {
                return;
            }
            if (! $course->wasRecentlyCreated && ! $course->wasChanged(['is_published', 'is_downloadable'])) {
                return;
            }
            try {
                app(SubscriptionService::class)->grantCommunityMembersAccessToCourse($course);
            } catch (\Throwable $e) {
                Log::warning('grantCommunityMembersAccessToCourse: ' . $e->getMessage(), [
                    'content_id' => $course->id,
                ]);
            }

            if ($course->lessons()->exists()) {
                try {
                    app(SubscriptionService::class)->grantPremiumSubscribersAccessToCourse($course);
                } catch (\Throwable $e) {
                    Log::warning('grantPremiumSubscribersAccessToCourse: ' . $e->getMessage(), [
                        'content_id' => $course->id,
                    ]);
                }
            }
        });
    }
}
