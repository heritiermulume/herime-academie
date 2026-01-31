<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class CaptureMarketingContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $funnel = $this->extractFunnelKey($request);

        /** @var \Symfony\Component\HttpFoundation\Response $response */
        $response = $next($request);

        if ($funnel === null) {
            return $response;
        }

        // Persist pour navigation multi-pages (session + cookie)
        Session::put('marketing.funnel_key', $funnel);
        $cookie = Cookie::make('marketing_funnel', $funnel, 60 * 24 * 30); // 30 jours
        $response->headers->setCookie($cookie);

        return $response;
    }

    private function extractFunnelKey(Request $request): ?string
    {
        $candidates = [
            $request->query('funnel'),
            $request->query('utm_campaign'),
            $request->query('utm_source'),
            $request->query('utm_medium'),
        ];

        foreach ($candidates as $c) {
            if (!is_string($c)) {
                continue;
            }
            $c = trim($c);
            if ($c === '') {
                continue;
            }
            // limiter la taille (cookie safe)
            return mb_substr($c, 0, 64);
        }

        return null;
    }
}

