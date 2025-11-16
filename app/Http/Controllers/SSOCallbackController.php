<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\User;

class SSOCallbackController extends Controller
{
    public function handle(Request $request)
    {
        $token = $request->query('token');
        $finalRedirect = $request->query('redirect', url('/'));

        if (!$token) {
            Log::info('SSO callback: no token, redirecting to SSO for token generation', [
                'final_redirect' => $finalRedirect,
            ]);
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
            Log::warning('SSO callback: token invalid, redirecting back to SSO', [
                'status' => $resp->status(),
            ]);
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

        Log::info('SSO callback: local session established, redirecting to final URL on academie', [
            'user_id' => $user->id,
            'email' => $user->email,
            'final_redirect' => $finalRedirect,
        ]);

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


