<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MetaCapiController extends Controller
{
    public function handle(Request $request)
    {
        if (!(bool) Setting::get('meta_capi_enabled', false)) {
            return response()->json(['ok' => false, 'disabled' => true], 200);
        }

        $request->validate([
            'event_name' => 'required|string|max:64',
            'event_id' => 'required|string|max:128',
            'event_source_url' => 'nullable|string|max:2048',
            'payload' => 'nullable|array',
            'pixel_ids' => 'required|array|min:1',
            'pixel_ids.*' => 'string|max:64',
        ]);

        $accessToken = trim((string) Setting::get('meta_capi_access_token', ''));
        if ($accessToken === '') {
            // Hard fail silently (do not break user actions)
            return response()->json(['ok' => false, 'error' => 'missing_token'], 200);
        }

        $testEventCode = trim((string) Setting::get('meta_capi_test_event_code', ''));

        $eventName = (string) $request->input('event_name');
        $eventId = (string) $request->input('event_id');
        $eventSourceUrl = (string) ($request->input('event_source_url') ?: $request->fullUrl());
        $payload = is_array($request->input('payload')) ? $request->input('payload') : [];
        $pixelIds = array_values(array_unique(array_filter($request->input('pixel_ids', []), fn ($v) => is_string($v) && trim($v) !== '')));

        $user = $request->user();
        $em = null;
        if ($user && isset($user->email) && is_string($user->email) && trim($user->email) !== '') {
            $em = hash('sha256', strtolower(trim($user->email)));
        }

        $fbp = $request->cookie('_fbp');
        $fbc = $request->cookie('_fbc');

        $userData = array_filter([
            'client_ip_address' => $request->ip(),
            'client_user_agent' => $request->userAgent(),
            'fbp' => is_string($fbp) ? $fbp : null,
            'fbc' => is_string($fbc) ? $fbc : null,
            'em' => $em ? [$em] : null,
        ], fn ($v) => $v !== null && $v !== '');

        $customData = $payload; // keep as-is; Meta expects standard keys for standard events

        $baseEvent = [
            'event_name' => $eventName,
            'event_time' => time(),
            'event_id' => $eventId,
            'action_source' => 'website',
            'event_source_url' => $eventSourceUrl,
            'user_data' => $userData,
            'custom_data' => $customData,
        ];

        foreach ($pixelIds as $pixelId) {
            try {
                $url = "https://graph.facebook.com/v19.0/{$pixelId}/events";
                $body = [
                    'data' => [$baseEvent],
                ];
                if ($testEventCode !== '') {
                    $body['test_event_code'] = $testEventCode;
                }

                $resp = Http::asJson()
                    ->timeout(4)
                    ->post($url, $body + ['access_token' => $accessToken]);

                if (!$resp->successful()) {
                    Log::warning('Meta CAPI send failed', [
                        'pixel_id' => $pixelId,
                        'status' => $resp->status(),
                        'body' => $resp->body(),
                    ]);
                }
            } catch (\Throwable $e) {
                Log::warning('Meta CAPI exception', [
                    'pixel_id' => $pixelId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return response()->json(['ok' => true], 200);
    }
}

