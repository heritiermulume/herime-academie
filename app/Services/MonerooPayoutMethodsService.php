<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service centralisé pour récupérer les méthodes de payout Moneroo.
 * Toutes les données (pays, devises, opérateurs) proviennent exclusivement de l'API.
 *
 * @see https://docs.moneroo.io/payouts/available-methods
 * @see https://api.moneroo.io/utils/payout/methods
 */
class MonerooPayoutMethodsService
{
    private const CACHE_KEY = 'moneroo_payout_methods';
    private const CACHE_TTL_SECONDS = 3600; // 1 heure

    public function __construct() {}

    /**
     * Récupère les pays et opérateurs (méthodes) depuis l'API Moneroo.
     * Aucune donnée statique : en cas d'échec API, retourne des listes vides.
     *
     * @return array{countries: array<int, array{code: string, name: string, prefix: string, flag: string, currency: string}>, providers: array<int, array{code: string, name: string, country: string, currencies: array, currency: string, logo: string}>}
     */
    public function getPayoutMethods(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL_SECONDS, function () {
            return $this->fetchFromApi();
        });
    }

    /**
     * Invalide le cache (utile après changement de config ou pour forcer un rafraîchissement).
     */
    public function forgetCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Appel direct à l'API sans cache.
     *
     * @return array{countries: array, providers: array}
     */
    private function fetchFromApi(): array
    {
        $utilsBaseUrl = rtrim(config('services.moneroo.utils_base_url', 'https://api.moneroo.io'), '/');
        $apiKey = config('services.moneroo.api_key');
        $url = $utilsBaseUrl . '/utils/payout/methods';
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
        if (!empty($apiKey)) {
            $headers['Authorization'] = 'Bearer ' . $apiKey;
        }

        try {
            $response = Http::timeout(15)
                ->retry(2, 100)
                ->withHeaders($headers)
                ->get($url);

            if (!$response->successful()) {
                Log::warning('Moneroo payout methods: API error', [
                    'url' => $url,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return $this->emptyResult();
            }

            $responseData = $response->json();
            $methodsList = $responseData['data'] ?? [];

            if (!is_array($methodsList)) {
                return $this->emptyResult();
            }

            return $this->parseMethodsList($methodsList);
        } catch (\Throwable $e) {
            Log::warning('Moneroo payout methods: fetch failed', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);
            return $this->emptyResult();
        }
    }

    /**
     * @param array<int, array> $methodsList
     * @return array{countries: array, providers: array}
     */
    private function parseMethodsList(array $methodsList): array
    {
        $countries = [];
        $providers = [];

        foreach ($methodsList as $method) {
            if (empty($method['short_code']) || empty($method['is_enabled'])) {
                continue;
            }

            $methodCode = $method['short_code'];
            $methodName = $method['name'] ?? $methodCode;
            $currencyObj = $method['currency'] ?? [];
            $currencyCode = is_array($currencyObj) ? ($currencyObj['code'] ?? '') : (string) $currencyObj;
            $currenciesList = $method['currencies'] ?? null;
            if (is_array($currenciesList) && count($currenciesList) > 0) {
                $currenciesList = array_values(array_map(function ($c) {
                    return is_array($c) ? ($c['code'] ?? (string) $c) : (string) $c;
                }, $currenciesList));
            } else {
                $currenciesList = $currencyCode ? [$currencyCode] : [];
            }
            $countryList = $method['countries'] ?? [];
            $firstCountry = $countryList[0] ?? [];
            $countryCode = $firstCountry['code'] ?? $method['country'] ?? '';
            $countryName = $firstCountry['name'] ?? $countryCode;
            $dialCode = $firstCountry['dial_code'] ?? '';
            $flagUrl = $firstCountry['flag'] ?? '';

            if ($countryCode && !isset($countries[$countryCode])) {
                $countries[$countryCode] = [
                    'code' => $countryCode,
                    'name' => $countryName,
                    'prefix' => $dialCode,
                    'flag' => $flagUrl,
                    'currency' => $firstCountry['currency'] ?? $currencyCode,
                ];
            }

            $providers[] = [
                'code' => $methodCode,
                'name' => $methodName,
                'country' => $countryCode,
                'currencies' => $currenciesList,
                'currency' => $currencyCode ?: ($currenciesList[0] ?? ''),
                'logo' => $method['icon_url'] ?? '',
            ];
        }

        $countries = array_values($countries);
        usort($countries, fn ($a, $b) => strcmp($a['name'], $b['name']));
        usort($providers, fn ($a, $b) => strcmp($a['name'], $b['name']));

        return [
            'countries' => $countries,
            'providers' => $providers,
        ];
    }

    /**
     * @return array{countries: array<int, mixed>, providers: array<int, mixed>}
     */
    private function emptyResult(): array
    {
        return [
            'countries' => [],
            'providers' => [],
        ];
    }
}
