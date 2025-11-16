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
        try {
            $token = $request->query('token');
            $finalRedirect = $request->query('redirect', url('/'));

            if (!$token) {
            Log::info('SSO callback: no token, redirecting to SSO for token generation', [
                'final_redirect' => $finalRedirect,
            ]);
            $callback = route('sso.callback', ['redirect' => $finalRedirect]);
            $loginUrl = 'https://compte.herime.com/login?redirect=' . urlencode($callback);
            return redirect()->away($loginUrl);
            }

            // Valider le token côté compte.herime.com (Passport) - endpoint recommandé /api/me
            $resp = Http::acceptJson()
                ->withToken($token)
                ->get('https://compte.herime.com/api/me');

            if (!$resp->ok()) {
                // Token invalide → relancer SSO une seule fois
                Log::warning('SSO callback: token invalid, redirecting back to SSO', [
                    'status' => $resp->status(),
                    'body' => $resp->body(),
                ]);
                $callback = route('sso.callback', ['redirect' => $finalRedirect]);
                $loginUrl = 'https://compte.herime.com/login?redirect=' . urlencode($callback);
                return redirect()->away($loginUrl);
            }

            // Succès SSO: créer une session locale fonctionnelle
            $payload = $resp->json();
            if (!is_array($payload)) {
                Log::warning('SSO callback: unexpected /api/me payload (not JSON object)', ['payload' => $resp->body()]);
                $callback = route('sso.callback', ['redirect' => $finalRedirect]);
                $loginUrl = 'https://compte.herime.com/login?redirect=' . urlencode($callback);
                return redirect()->away($loginUrl);
            }

            // Le contrat attendu: { success: true, data: { user: {...} } }
            $success = (bool) data_get($payload, 'success', false);
            $remoteUser = data_get($payload, 'data.user', []);
            if (!$success || empty($remoteUser) || !is_array($remoteUser)) {
                Log::warning('SSO callback: /api/me returned invalid structure or not success', ['payload' => $payload]);
                $callback = route('sso.callback', ['redirect' => $finalRedirect]);
                $loginUrl = 'https://compte.herime.com/login?redirect=' . urlencode($callback);
                return redirect()->away($loginUrl);
            }

            $email = data_get($remoteUser, 'email');
            $name  = data_get($remoteUser, 'name') ?? trim((string) data_get($remoteUser, 'first_name').' '.(string) data_get($remoteUser, 'last_name'));

            if (!$email) {
                Log::warning('SSO callback: missing email in /api/me response');
                // Si les données sont insuffisantes, relancer le flux SSO
                $callback = route('sso.callback', ['redirect' => $finalRedirect]);
                $loginUrl = 'https://compte.herime.com/login?redirect=' . urlencode($callback);
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
            // Conserver le token SSO pour la validation ultérieure
            try {
                $request->session()->put('sso_token', $token);
            } catch (\Throwable $e) {
                Log::debug('SSO callback: unable to store sso_token in session', ['error' => $e->getMessage()]);
            }

            Log::info('SSO callback: authenticated locally and redirecting', [
                'user_id' => $user->id,
                'email' => $user->email,
                'final_redirect' => $finalRedirect,
            ]);

            // Toujours rester sur academie.herime.com pour la redirection finale
            $finalUrl = $this->safeRedirect($finalRedirect);
            return redirect()->to($finalUrl);
        } catch (\Throwable $e) {
            Log::error('SSO callback error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return redirect()->to(url('/'));
        }
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


