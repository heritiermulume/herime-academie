<?php

namespace App\Providers;

use App\Models\Announcement;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

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
    }
}
