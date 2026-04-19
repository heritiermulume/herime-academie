<?php

namespace App\Http\Middleware;

use App\Http\Controllers\CartController;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

/**
 * Autorise le checkout Moneroo pour un utilisateur connecté en session,
 * ou pour un invité ayant validé e-mail / téléphone (compte existant) via cart_guest_pay_*.
 *
 * Ne s’appuie pas sur Auth::onceUsingId (fragile avec la pile middleware / fetch).
 */
class AuthenticateMonerooCheckout
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            return $next($request);
        }

        if (! Session::get(CartController::GUEST_PAY_READY_KEY)) {
            return $this->deny($request);
        }

        $id = (int) Session::get(CartController::GUEST_PAY_USER_ID_KEY);
        if ($id <= 0 || ! User::query()->whereKey($id)->exists()) {
            return $this->deny($request);
        }

        return $next($request);
    }

    protected function deny(Request $request): Response
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'Vous devez être connecté pour procéder au paiement.',
            ], 401);
        }

        return redirect()->guest(route('login'));
    }
}
