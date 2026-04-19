<?php

use App\Jobs\CleanTemporaryUploadsJob;
use App\Jobs\ProcessSubscriptionRenewalsJob;
use App\Jobs\RunMonerooPaymentMaintenanceJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule): void {
        /**
         * Exécute une tâche planifiée sans faire échouer les autres en cas d’exception.
         * withoutOverlapping() évite les doubles exécutions si un cron déborde (nécessite un driver cache fiable).
         */
        $runSafe = static function (string $name, \Closure $task): void {
            try {
                $task();
            } catch (\Throwable $e) {
                Log::error('Tâche planifiée en échec', [
                    'task' => $name,
                    'message' => $e->getMessage(),
                    'exception' => $e::class,
                ]);
            }
        };

        // Mutex (minutes) : assez long pour couvrir l’exécution normale + marge, court assez pour ne pas bloquer des heures si un worker reste coincé.
        $schedule->call(function () use ($runSafe): void {
            $runSafe('youtube-cleanup-tokens', static fn () => \App\Models\VideoAccessToken::cleanupExpired());
        })
            ->hourly()
            ->name('youtube-cleanup-tokens')
            ->withoutOverlapping(20);

        $schedule->call(function () use ($runSafe): void {
            $runSafe('youtube-monitor-activity', static fn () => app(\App\Services\VideoSecurityService::class)->monitorSuspiciousActivity());
        })
            ->everySixHours()
            ->name('youtube-monitor-activity')
            ->withoutOverlapping(90);

        $schedule->job(new ProcessSubscriptionRenewalsJob)
            ->hourly()
            ->name('subscriptions-process-renewals')
            ->withoutOverlapping(45);

        $schedule->call(function () use ($runSafe): void {
            $runSafe('moneroo-payment-maintenance', static function (): void {
                Bus::dispatchSync(new RunMonerooPaymentMaintenanceJob);
            });
        })
            ->everyTenMinutes()
            ->name('moneroo-payment-maintenance')
            ->withoutOverlapping(7);

        $schedule->job(new CleanTemporaryUploadsJob)
            ->hourly()
            ->name('clean-temporary-uploads')
            ->withoutOverlapping(45);

        // 3 exécutions / jour (7h, 15h, 23h) — voir ContentRatingReminder::CAMPAIGN_DAYS pour la durée de campagne.
        $schedule->call(function () use ($runSafe): void {
            $runSafe('content-rating-reminders', static fn () => app(\App\Services\ContentRatingReminderService::class)->sendDueReminders());
        })
            ->cron('0 7,15,23 * * *')
            ->name('content-rating-reminders')
            ->withoutOverlapping(25);

        $schedule->command('announcements:expire')
            ->hourly()
            ->name('announcements-expire')
            ->withoutOverlapping(25);
    })
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'security' => \App\Http\Middleware\SecurityMiddleware::class,
            'upload.errors' => \App\Http\Middleware\HandleUploadErrors::class,
            'sync.cart' => \App\Http\Middleware\SyncCartOnLogin::class,
            'sso.validate' => \App\Http\Middleware\ValidateSSOToken::class,
            'sso.page.load' => \App\Http\Middleware\ValidateSSOOnPageLoad::class,
            'subscription.access' => \App\Http\Middleware\EnsureSubscriptionAccess::class,
        ]);

        // Appliquer les middlewares globalement sur les routes web
        $middleware->web(append: [
            \App\Http\Middleware\HandleUploadErrors::class,
            // Capturer le contexte marketing (utm/funnel) et le persister
            \App\Http\Middleware\CaptureMarketingContext::class,
            // Valider le token SSO sur chaque requête web authentifiée (configurable, voir config/services.php)
            \App\Http\Middleware\ValidateSSOOnPageLoad::class,
            // Tracker les visiteurs du site
            \App\Http\Middleware\TrackVisitors::class,
        ]);

        // Faire confiance aux proxies pour que HTTPS soit correctement détecté (cookies secure)
        $middleware->trustProxies(at: '*', headers: Request::HEADER_X_FORWARDED_FOR
            | Request::HEADER_X_FORWARDED_HOST
            | Request::HEADER_X_FORWARDED_PORT
            | Request::HEADER_X_FORWARDED_PROTO
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Logger toutes les exceptions, même en production
        $exceptions->report(function (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Exception caught', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'request_url' => request()->fullUrl(),
                'request_method' => request()->method(),
                'user_id' => auth()->id(),
            ]);
        });

        // Pour les requêtes JSON/AJAX, retourner des erreurs JSON détaillées même en production
        $exceptions->render(function (\Throwable $e, Request $request) {
            if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                $statusCode = method_exists($e, 'getStatusCode')
                    ? $e->getStatusCode()
                    : 500;

                $message = $e->getMessage();

                // En production, ne pas exposer les détails techniques sauf pour certaines erreurs
                if (config('app.env') === 'production' && $statusCode === 500) {
                    // Logger l'erreur complète
                    \Illuminate\Support\Facades\Log::error('Production error for JSON request', [
                        'error' => $message,
                        'trace' => $e->getTraceAsString(),
                        'route' => $request->route()?->getName(),
                    ]);

                    // Retourner un message générique mais avec un code d'erreur utile
                    return response()->json([
                        'message' => 'Une erreur est survenue. Consultez les logs pour plus de détails.',
                        'error' => class_basename($e),
                        'status' => $statusCode,
                    ], $statusCode);
                }

                return response()->json([
                    'message' => $message,
                    'error' => class_basename($e),
                    'status' => $statusCode,
                ], $statusCode);
            }
        });
    })->create();
