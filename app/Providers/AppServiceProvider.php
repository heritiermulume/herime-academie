<?php

namespace App\Providers;

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
        // Partager la devise de base avec toutes les vues
        try {
            View::share('baseCurrency', \App\Models\Setting::getBaseCurrency());
        } catch (\Exception $e) {
            // Si la table settings n'existe pas encore, utiliser USD par défaut
            View::share('baseCurrency', 'USD');
        }

        // Rien à faire - FileHelper est accessible directement dans les vues via \App\Helpers\FileHelper
    }
}
