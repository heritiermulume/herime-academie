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
            \App\Http\Middleware\ValidateSSOOnPageLoad::class,
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
        //
    })->create();
