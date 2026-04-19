<?php

namespace App\Http\Middleware;

use App\Http\Controllers\CartController;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pour un invité qui a validé e-mail / téléphone (compte existant), authentifie
 * l’utilisateur uniquement pour la requête HTTP (sans session persistante),
 * afin que panier + Moneroo fonctionnent comme pour un client connecté.
 */
class AuthenticateGuestMonerooCart
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check() && Session::get(CartController::GUEST_PAY_READY_KEY)) {
            $id = (int) Session::get(CartController::GUEST_PAY_USER_ID_KEY);
            if ($id > 0) {
                Auth::onceUsingId($id);
            }
        }

        return $next($request);
    }
}
