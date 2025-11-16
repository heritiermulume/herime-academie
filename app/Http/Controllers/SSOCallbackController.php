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

        // Succès SSO: créer une session locale fonctionnelle
        $remoteUser = $resp->json();
        $email = data_get($remoteUser, 'email');
        $name  = data_get($remoteUser, 'name') ?? trim((string) data_get($remoteUser, 'first_name').' '.(string) data_get($remoteUser, 'last_name'));

        if (!$email) {
            Log::warning('SSO callback: missing email in /api/user response');
            // Si les données sont insuffisantes, relancer le flux SSO
            $callback = route('sso.callback', ['redirect' => $finalRedirect]);
            $loginUrl = 'https://compte.herime.com/login?force_token=1&redirect=' . urlencode($callback);
            return redirect()->away($loginUrl);
        }

        // Upsert utilisateur local minimal
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name ?: $email,
                'password' => bcrypt(Str::random(32)),
            ]
        );

        // Connexion locale + sécurisation de session
        Auth::login($user, true);
        $request->session()->regenerate();

        Log::info('SSO callback: authenticated locally and redirecting', [
            'user_id' => $user->id,
            'email' => $user->email,
            'final_redirect' => $finalRedirect,
        ]);

        return redirect()->to($finalRedirect);
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


