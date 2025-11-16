<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Models\User;

class SSOCallbackController extends Controller
{
    public function handle(Request $request)
    {
        $token = $request->query('token');
        $finalRedirect = $request->query('redirect', url('/'));

        if (!$token) {
            $callback = route('sso.callback', ['redirect' => $finalRedirect]);
            $loginUrl = 'https://compte.herime.com/login?force_token=1&redirect=' . urlencode($callback);
            return redirect()->away($loginUrl);
        }

        // Valider le token côté compte.herime.com (Passport)
        $resp = Http::acceptJson()
            ->withToken($token)
            ->get('https://compte.herime.com/api/user');

        if (!$resp->ok()) {
            // Token invalide → relancer SSO une seule fois
            $callback = route('sso.callback', ['redirect' => $finalRedirect]);
            $loginUrl = 'https://compte.herime.com/login?force_token=1&redirect=' . urlencode($callback);
            return redirect()->away($loginUrl);
        }

        $remoteUser = $resp->json();
        $email = data_get($remoteUser, 'email');
        $name  = data_get($remoteUser, 'name') ?? trim(data_get($remoteUser, 'first_name').' '.data_get($remoteUser, 'last_name'));

        if (!$email) {
            abort(403, 'SSO: email manquant');
        }

        // Upsert utilisateur local
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name ?: $email,
                'password' => bcrypt(Str::random(32)),
                'provider' => 'herime-account',
                'provider_id' => data_get($remoteUser, 'id'),
            ]
        );

        // Optionnel: maj champs
        $user->forceFill([
            'name' => $name ?: $user->name,
            'provider' => 'herime-account',
            'provider_id' => data_get($remoteUser, 'id') ?? $user->provider_id,
        ])->save();

        // Ouvrir la session locale
        Auth::login($user, true);

        // Redirection finale: rester sur academie.herime.com
        return redirect()->to($this->safeRedirect($finalRedirect));
    }

    private function safeRedirect(string $url): string
    {
        $parsed = parse_url($url);
        if (!$parsed || empty($parsed['host'])) {
            return url('/'); // relatif
        }
        $host = strtolower(preg_replace('/^www\./', '', $parsed['host']));
        if ($host === 'academie.herime.com') {
            return $url;
        }
        return url('/'); // fallback sûr
    }
}


