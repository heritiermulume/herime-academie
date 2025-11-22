<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Visitor;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class TrackVisitors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Ignorer les requêtes AJAX, les assets statiques, et les routes admin
        if ($request->ajax() || 
            $request->expectsJson() || 
            $request->is('admin/*') ||
            $request->is('api/*') ||
            $request->is('_debugbar/*') ||
            $request->is('storage/*') ||
            $request->is('*.css') ||
            $request->is('*.js') ||
            $request->is('*.jpg') ||
            $request->is('*.jpeg') ||
            $request->is('*.png') ||
            $request->is('*.gif') ||
            $request->is('*.svg') ||
            $request->is('*.ico') ||
            $request->is('*.woff') ||
            $request->is('*.woff2') ||
            $request->is('*.ttf') ||
            $request->is('*.eot')) {
            return $next($request);
        }

        try {
            // Obtenir ou créer un session_id unique pour ce visiteur
            $sessionId = Session::getId();
            
            // Vérifier si on a déjà enregistré cette visite dans cette session (éviter les doublons)
            $lastVisitKey = 'last_visit_tracked_' . $request->path();
            $lastVisitTime = Session::get($lastVisitKey);
            
            // Enregistrer une visite toutes les 5 minutes maximum pour la même page
            if ($lastVisitTime && (now()->timestamp - $lastVisitTime) < 300) {
                return $next($request);
            }

            // Extraire les informations du visiteur
            $ipAddress = $request->ip();
            $userAgent = $request->userAgent();
            $url = $request->fullUrl();
            $referer = $request->header('referer');
            
            // Détecter le type d'appareil et le navigateur
            $deviceInfo = $this->detectDevice($userAgent);
            
            // Obtenir la géolocalisation (pays et ville)
            $location = $this->getLocation($ipAddress);
            
            // Enregistrer la visite de manière asynchrone pour ne pas ralentir la réponse
            $this->trackVisit([
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'url' => $url,
                'referer' => $referer,
                'device_type' => $deviceInfo['device_type'],
                'browser' => $deviceInfo['browser'],
                'os' => $deviceInfo['os'],
                'country' => $location['country'] ?? null,
                'city' => $location['city'] ?? null,
                'user_id' => auth()->id(),
                'session_id' => $sessionId,
                'visited_at' => now(),
            ]);

            // Marquer cette visite comme enregistrée
            Session::put($lastVisitKey, now()->timestamp);
        } catch (\Exception $e) {
            // Logger l'erreur mais ne pas bloquer la requête
            Log::error('Error tracking visitor: ' . $e->getMessage());
        }

        return $next($request);
    }

    /**
     * Enregistrer la visite dans la base de données
     */
    protected function trackVisit(array $data): void
    {
        // Utiliser une queue ou un insert direct selon les besoins
        // Pour l'instant, on fait un insert direct mais on pourrait utiliser une queue
        Visitor::create($data);
    }

    /**
     * Détecter le type d'appareil, le navigateur et l'OS
     */
    protected function detectDevice(?string $userAgent): array
    {
        if (!$userAgent) {
            return [
                'device_type' => 'unknown',
                'browser' => 'unknown',
                'os' => 'unknown',
            ];
        }

        $deviceType = 'desktop';
        $browser = 'unknown';
        $os = 'unknown';

        // Détecter le type d'appareil
        if (preg_match('/mobile|android|iphone|ipad|ipod|blackberry|iemobile|opera mini/i', $userAgent)) {
            if (preg_match('/tablet|ipad|playbook|silk/i', $userAgent)) {
                $deviceType = 'tablet';
            } else {
                $deviceType = 'mobile';
            }
        }

        // Détecter le navigateur
        if (preg_match('/MSIE|Trident/i', $userAgent)) {
            $browser = 'Internet Explorer';
        } elseif (preg_match('/Edge/i', $userAgent)) {
            $browser = 'Edge';
        } elseif (preg_match('/Chrome/i', $userAgent)) {
            $browser = 'Chrome';
        } elseif (preg_match('/Safari/i', $userAgent)) {
            $browser = 'Safari';
        } elseif (preg_match('/Firefox/i', $userAgent)) {
            $browser = 'Firefox';
        } elseif (preg_match('/Opera|OPR/i', $userAgent)) {
            $browser = 'Opera';
        }

        // Détecter l'OS
        if (preg_match('/Windows NT/i', $userAgent)) {
            $os = 'Windows';
        } elseif (preg_match('/Mac OS X/i', $userAgent)) {
            $os = 'macOS';
        } elseif (preg_match('/Linux/i', $userAgent)) {
            $os = 'Linux';
        } elseif (preg_match('/Android/i', $userAgent)) {
            $os = 'Android';
        } elseif (preg_match('/iPhone|iPad|iPod/i', $userAgent)) {
            $os = 'iOS';
        }

        return [
            'device_type' => $deviceType,
            'browser' => $browser,
            'os' => $os,
        ];
    }

    /**
     * Obtenir la géolocalisation (pays et ville) à partir de l'adresse IP
     * Utilise ip-api.com (service gratuit, 45 requêtes/minute)
     */
    protected function getLocation(string $ipAddress): array
    {
        // Ignorer les IPs locales et privées
        if ($this->isLocalIp($ipAddress)) {
            return ['country' => null, 'city' => null];
        }

        // Utiliser le cache pour éviter les requêtes répétées pour la même IP
        $cacheKey = 'visitor_location_' . md5($ipAddress);
        
        return Cache::remember($cacheKey, now()->addDays(30), function () use ($ipAddress) {
            try {
                // Utiliser ip-api.com (gratuit, sans API key, 45 req/min)
                $response = Http::timeout(3)
                    ->get("http://ip-api.com/json/{$ipAddress}", [
                        'fields' => 'status,country,countryCode,city,query'
                    ]);

                if ($response->successful()) {
                    $data = $response->json();
                    
                    if (isset($data['status']) && $data['status'] === 'success') {
                        return [
                            'country' => $data['country'] ?? null,
                            'city' => $data['city'] ?? null,
                        ];
                    }
                }
            } catch (\Exception $e) {
                // Logger l'erreur mais ne pas bloquer le tracking
                Log::debug('Geolocation API error: ' . $e->getMessage(), [
                    'ip' => $ipAddress
                ]);
            }

            return ['country' => null, 'city' => null];
        });
    }

    /**
     * Vérifier si une IP est locale ou privée
     */
    protected function isLocalIp(string $ip): bool
    {
        // IPv4 localhost
        if ($ip === '127.0.0.1' || $ip === '::1' || $ip === 'localhost') {
            return true;
        }

        // IPv4 privées
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
        }

        // IPv6 localhost
        if ($ip === '::1') {
            return true;
        }

        return false;
    }
}
