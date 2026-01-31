<?php

namespace App\Services;

use App\Models\MetaEventTrigger;
use App\Models\MetaPixel;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class MetaTrackingService
{
    public function getClientConfig(Request $request): array
    {
        $enabled = (bool) Setting::get('meta_tracking_enabled', false);
        if (!$enabled) {
            return [
                'enabled' => false,
                'pixels' => [],
                'triggers' => [],
                'context' => $this->getContext($request),
            ];
        }

        $context = $this->getContext($request);

        $cacheKey = 'meta_tracking_cfg:' . sha1(json_encode([
            'route' => $context['route_name'],
            'path' => $context['path'],
            'country' => $context['country_code'],
            'funnel' => $context['funnel_key'],
        ]));

        return Cache::remember($cacheKey, now()->addMinutes(2), function () use ($context) {
            $pixels = MetaPixel::query()
                ->where('is_active', true)
                ->orderByDesc('priority')
                ->orderByDesc('id')
                ->get()
                ->filter(fn (MetaPixel $p) => $this->pixelMatchesContext($p, $context))
                ->values()
                ->map(fn (MetaPixel $p) => [
                    'pixel_id' => $p->pixel_id,
                    'name' => $p->name,
                    'priority' => (int) ($p->priority ?? 0),
                ])
                ->all();

            $triggers = MetaEventTrigger::query()
                ->with(['event:id,event_name,is_active,is_standard,default_payload'])
                ->where('is_active', true)
                ->orderByDesc('priority')
                ->orderByDesc('id')
                ->get()
                ->filter(function (MetaEventTrigger $t) use ($context) {
                    if (!$t->event || !$t->event->is_active) {
                        return false;
                    }
                    return $this->triggerMatchesContext($t, $context);
                })
                ->values()
                ->map(function (MetaEventTrigger $t) {
                    $eventPayload = is_array($t->event->default_payload) ? $t->event->default_payload : [];
                    $triggerPayload = is_array($t->payload) ? $t->payload : [];

                    return [
                        'id' => $t->id,
                        'trigger_type' => $t->trigger_type,
                        'priority' => (int) ($t->priority ?? 0),
                        'match_route_name' => $t->match_route_name,
                        'match_path_pattern' => $t->match_path_pattern,
                        'css_selector' => $t->css_selector,
                        'event_name' => $t->event->event_name,
                        'payload' => array_replace_recursive($eventPayload, $triggerPayload),
                        'pixel_ids' => $t->pixel_ids ?: null,
                        'once_per_page' => (bool) $t->once_per_page,
                    ];
                })
                ->all();

            return [
                'enabled' => true,
                'pixels' => $pixels,
                'triggers' => $triggers,
                'context' => $context,
            ];
        });
    }

    private function getContext(Request $request): array
    {
        $routeName = $request->route()?->getName();
        $path = ltrim($request->path(), '/');

        return [
            'route_name' => $routeName,
            'path' => $path === '' ? '/' : $path,
            'country_code' => $this->resolveCountryCode($request),
            'funnel_key' => $this->resolveFunnelKey($request),
        ];
    }

    private function resolveFunnelKey(Request $request): ?string
    {
        // 1) Query string (prioritaire)
        $candidates = [
            $request->query('funnel'),
            $request->query('utm_campaign'),
            $request->query('utm_source'),
        ];

        foreach ($candidates as $c) {
            $c = is_string($c) ? trim($c) : null;
            if ($c !== null && $c !== '') {
                return Str::limit($c, 64, '');
            }
        }

        // 2) Session (persisté par middleware)
        $s = $request->session()->get('marketing.funnel_key');
        if (is_string($s)) {
            $s = trim($s);
            if ($s !== '') {
                return Str::limit($s, 64, '');
            }
        }

        // 3) Cookie (fallback)
        $c = $request->cookie('marketing_funnel');
        if (is_string($c)) {
            $c = trim($c);
            if ($c !== '') {
                return Str::limit($c, 64, '');
            }
        }

        return null;
    }

    private function resolveCountryCode(Request $request): ?string
    {
        // 1) Paramètre explicite (ex: flow de paiement)
        $q = $request->query('country');
        if (is_string($q)) {
            $q = strtoupper(trim($q));
            if (preg_match('/^[A-Z]{2}$/', $q)) {
                return $q;
            }
        }

        // 2) Utilisateur authentifié (si disponible)
        $user = $request->user();
        foreach (['moneroo_country', 'pawapay_country'] as $field) {
            if ($user && isset($user->{$field}) && is_string($user->{$field})) {
                $c = strtoupper(trim((string) $user->{$field}));
                if (preg_match('/^[A-Z]{2}$/', $c)) {
                    return $c;
                }
            }
        }

        // 3) Headers proxy/CDN (Cloudflare)
        $cf = $request->header('CF-IPCountry');
        if (is_string($cf)) {
            $cf = strtoupper(trim($cf));
            if (preg_match('/^[A-Z]{2}$/', $cf) && $cf !== 'XX') {
                return $cf;
            }
        }

        // 4) Fallback GeoIP léger (ip-api) avec cache (OPTIONNEL en prod)
        // Note: ce fallback dépend d'un service externe; il est désactivable via setting.
        $fallbackEnabled = (bool) Setting::get('meta_geoip_fallback_enabled', false);
        if (!$fallbackEnabled) {
            return null;
        }

        $ip = $request->ip();
        if (!$ip || $this->isLocalIp($ip)) {
            return null;
        }

        $cacheKey = 'meta_geo_country:' . md5($ip);
        return Cache::remember($cacheKey, now()->addDays(7), function () use ($ip) {
            try {
                $url = "http://ip-api.com/json/{$ip}?fields=status,countryCode";
                $raw = @file_get_contents($url, false, stream_context_create([
                    'http' => ['timeout' => 2],
                ]));

                if (!$raw) {
                    return null;
                }
                $data = json_decode($raw, true);
                if (!is_array($data) || ($data['status'] ?? null) !== 'success') {
                    return null;
                }

                $code = strtoupper(trim((string) ($data['countryCode'] ?? '')));
                return preg_match('/^[A-Z]{2}$/', $code) ? $code : null;
            } catch (\Throwable) {
                return null;
            }
        });
    }

    private function isLocalIp(string $ip): bool
    {
        if ($ip === '127.0.0.1' || $ip === '::1' || $ip === 'localhost') {
            return true;
        }
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
        }
        return false;
    }

    private function pixelMatchesContext(MetaPixel $pixel, array $context): bool
    {
        $country = $context['country_code'];
        if ($country) {
            $allowed = $this->normalizeStringArray($pixel->allowed_country_codes);
            if ($allowed && !in_array($country, $allowed, true)) {
                return false;
            }
            $excluded = $this->normalizeStringArray($pixel->excluded_country_codes);
            if ($excluded && in_array($country, $excluded, true)) {
                return false;
            }
        }

        $funnel = $context['funnel_key'];
        $funnels = $this->normalizeStringArray($pixel->funnel_keys);
        if ($funnels && (!$funnel || !in_array($funnel, $funnels, true))) {
            return false;
        }

        // Route/path allowlist
        if ($pixel->match_route_name && $context['route_name'] !== $pixel->match_route_name) {
            return false;
        }
        if ($pixel->match_path_pattern && !$this->pathMatches($context['path'], $pixel->match_path_pattern)) {
            return false;
        }

        // Route/path denylist
        $excludedRoutes = $this->normalizeStringArray($pixel->excluded_route_names);
        if ($excludedRoutes && $context['route_name'] && in_array($context['route_name'], $excludedRoutes, true)) {
            return false;
        }
        $excludedPatterns = $this->normalizeStringArray($pixel->excluded_path_patterns);
        if ($excludedPatterns) {
            foreach ($excludedPatterns as $pattern) {
                if ($this->pathMatches($context['path'], $pattern)) {
                    return false;
                }
            }
        }

        return true;
    }

    private function triggerMatchesContext(MetaEventTrigger $trigger, array $context): bool
    {
        if ($trigger->match_route_name && $context['route_name'] !== $trigger->match_route_name) {
            return false;
        }
        if ($trigger->match_path_pattern && !$this->pathMatches($context['path'], $trigger->match_path_pattern)) {
            return false;
        }

        $country = $context['country_code'];
        $countries = $this->normalizeStringArray($trigger->country_codes);
        if ($countries && (!$country || !in_array($country, $countries, true))) {
            return false;
        }

        $funnel = $context['funnel_key'];
        $funnels = $this->normalizeStringArray($trigger->funnel_keys);
        if ($funnels && (!$funnel || !in_array($funnel, $funnels, true))) {
            return false;
        }

        return true;
    }

    private function pathMatches(string $path, string $pattern): bool
    {
        $path = ltrim($path, '/');
        $pattern = ltrim($pattern, '/');

        // Compat "request()->is" style
        return Str::is($pattern, $path) || ($path === '' && ($pattern === '/' || $pattern === ''));
    }

    /**
     * @param mixed $value
     * @return array<int, string>
     */
    private function normalizeStringArray($value): array
    {
        if (!is_array($value)) {
            return [];
        }
        $out = [];
        foreach ($value as $v) {
            if (!is_string($v)) {
                continue;
            }
            $v = trim($v);
            if ($v === '') {
                continue;
            }
            $out[] = $v;
        }
        return array_values(array_unique($out));
    }
}

