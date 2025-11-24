<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\SSOController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    // Rediriger toutes les tentatives de connexion/inscription vers SSO
    Route::get('register', [SSOController::class, 'redirectToSSORegister'])
        ->name('register');

    Route::post('register', [SSOController::class, 'redirectToSSORegister']);

    Route::get('login', [SSOController::class, 'redirectToSSO'])
        ->name('login');

    Route::post('login', [SSOController::class, 'redirectToSSO']);

    // Rediriger les routes de mot de passe oublié vers SSO (géré par compte.herime.com)
    Route::get('forgot-password', [SSOController::class, 'redirectToSSO'])
        ->name('password.request');

    Route::post('forgot-password', [SSOController::class, 'redirectToSSO'])
        ->name('password.email');

    Route::get('reset-password/{token}', [SSOController::class, 'redirectToSSO'])
        ->name('password.reset');

    Route::post('reset-password', [SSOController::class, 'redirectToSSO'])
        ->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
