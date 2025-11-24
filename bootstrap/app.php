<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'security' => \App\Http\Middleware\SecurityMiddleware::class,
            'upload.errors' => \App\Http\Middleware\HandleUploadErrors::class,
            'sync.cart' => \App\Http\Middleware\SyncCartOnLogin::class,
            'sso.validate' => \App\Http\Middleware\ValidateSSOToken::class,
            'sso.page.load' => \App\Http\Middleware\ValidateSSOOnPageLoad::class,
        ]);
        
        // Appliquer les middlewares globalement sur les routes web
        $middleware->web(append: [
            \App\Http\Middleware\HandleUploadErrors::class,
            // Valider le token SSO à chaque chargement de page pour les utilisateurs authentifiés
            // DÉSACTIVÉ TEMPORAIREMENT pour éviter les boucles de redirection
            // \App\Http\Middleware\ValidateSSOOnPageLoad::class,
            // Tracker les visiteurs du site
            \App\Http\Middleware\TrackVisitors::class,
        ]);

        // Faire confiance aux proxies pour que HTTPS soit correctement détecté (cookies secure)
        $middleware->trustProxies(at: '*', headers: 
            Request::HEADER_X_FORWARDED_FOR
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
