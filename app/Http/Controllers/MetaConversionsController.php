<?php

namespace App\Http\Controllers;

use App\Models\MetaPixel;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MetaConversionsController extends Controller
{
    /**
     * Collect a browser-fired Meta event and forward it server-side to Meta Conversions API (CAPI).
     *
     * Note: When CAPI is disabled or missing token, we behave as 404 (hardening).
     */
    public function track(Request $request)
    {
        $trackingEnabled = (bool) Setting::get('meta_tracking_enabled', false);
        $capiEnabled = (bool) Setting::get('meta_capi_enabled', false);
        $token = trim((string) Setting::get('meta_capi_access_token', ''));

        if (!$trackingEnabled || !$capiEnabled || $token === '') {
            abort(404);
        }

        $payload = $request->validate([
            'event_name' => 'required|string|max:64',
            'event_id' => 'nullable|string|max:128',
            'event_source_url' => 'nullable|string|max:2048',
            'payload' => 'nullable|array',
            'pixel_ids' => 'nullable|array',
            'pixel_ids.*' => 'string|max:64',
        ]);

        $eventName = trim((string) $payload['event_name']);
        if ($eventName === '') {
            return response()->json(['ok' => false, 'message' => 'event_name required'], 422);
        }

        $eventId = isset($payload['event_id']) ? trim((string) $payload['event_id']) : '';
        $eventSourceUrl = isset($payload['event_source_url']) ? trim((string) $payload['event_source_url']) : '';
        $customData = isset($payload['payload']) && is_array($payload['payload']) ? $payload['payload'] : [];

        // Active pixels are the only allowed targets
        $activePixelIds = MetaPixel::query()
            ->where('is_active', true)
            ->pluck('pixel_id')
            ->filter(fn ($v) => is_string($v) && trim($v) !== '')
            ->map(fn ($v) => trim((string) $v))
            ->values()
            ->all();

        if (empty($activePixelIds)) {
            // Nothing to send
            return response()->noContent();
        }

        $targetPixelIds = $activePixelIds;
        if (isset($payload['pixel_ids']) && is_array($payload['pixel_ids']) && count($payload['pixel_ids']) > 0) {
            $requested = array_values(array_filter(array_map(fn ($v) => trim((string) $v), $payload['pixel_ids'])));
            $targetPixelIds = array_values(array_intersect($activePixelIds, $requested));
            if (empty($targetPixelIds)) {
                $targetPixelIds = $activePixelIds;
            }
        }

        // Build user_data for better match quality (safe minimal set)
        $userData = [
            'client_ip_address' => $request->ip(),
            'client_user_agent' => $request->userAgent() ?: '',
        ];

        $fbp = (string) $request->cookie('_fbp', '');
        $fbc = (string) $request->cookie('_fbc', '');
        if (trim($fbp) !== '') {
            $userData['fbp'] = trim($fbp);
        }
        if (trim($fbc) !== '') {
            $userData['fbc'] = trim($fbc);
        }

        if (auth()->check() && auth()->user()) {
            $email = (string) (auth()->user()->email ?? '');
            $email = strtolower(trim($email));
            if ($email !== '') {
                $userData['em'] = [hash('sha256', $email)];
            }

            $phone = (string) (auth()->user()->phone ?? auth()->user()->phone_number ?? '');
            $phone = preg_replace('/\D+/', '', $phone ?? '');
            if (is_string($phone) && $phone !== '') {
                $userData['ph'] = [hash('sha256', $phone)];
            }
        }

        $event = [
            'event_name' => $eventName,
            'event_time' => time(),
            'action_source' => 'website',
            'event_id' => $eventId !== '' ? $eventId : null,
            'event_source_url' => $eventSourceUrl !== '' ? $eventSourceUrl : null,
            'user_data' => $userData,
            'custom_data' => $customData,
        ];

        // Remove nulls (Meta accepts missing fields)
        $event = array_filter($event, fn ($v) => $v !== null);

        $testCode = trim((string) Setting::get('meta_capi_test_event_code', ''));

        foreach ($targetPixelIds as $pixelId) {
            try {
                $url = 'https://graph.facebook.com/v18.0/' . $pixelId . '/events';
                $body = [
                    'data' => [$event],
                    'access_token' => $token,
                ];
                if ($testCode !== '') {
                    $body['test_event_code'] = $testCode;
                }

                $resp = Http::timeout(3)->asJson()->post($url, $body);

                if (!$resp->successful()) {
                    \Log::warning('Meta CAPI send failed', [
                        'pixel_id' => $pixelId,
                        'status' => $resp->status(),
                        'body' => $resp->body(),
                        'event_name' => $eventName,
                        'event_id' => $eventId,
                    ]);
                }
            } catch (\Throwable $e) {
                \Log::warning('Meta CAPI send exception', [
                    'pixel_id' => $pixelId,
                    'event_name' => $eventName,
                    'event_id' => $eventId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Avoid leaking details to the browser
        return response()->noContent();
    }
}

