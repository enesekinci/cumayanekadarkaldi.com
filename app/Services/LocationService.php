<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LocationService
{
    private const FALLBACK_CITY = 'Istanbul';

    public function detectCity(?string $ip = null): string
    {
        $ip ??= $this->getClientIp();

        if (!$ip || $ip === '127.0.0.1' || $ip === '::1') {
            return self::FALLBACK_CITY;
        }

        // Try multiple providers in order of reliability
        $providers = [
            'detectViaIpInfo',
            'detectViaIpApiCo',
            'detectViaIpApi',
        ];

        foreach ($providers as $provider) {
            $city = $this->$provider($ip);
            if ($city) {
                Log::info('Location detected', ['ip' => $ip, 'city' => $city, 'provider' => $provider]);
                return $city;
            }
        }

        Log::warning('All location providers failed', ['ip' => $ip]);
        return self::FALLBACK_CITY;
    }

    public function getClientIp(): ?string
    {
        $request = request();
        
        // Laravel's trusted proxy aware IP detection
        $ip = $request->ip();
        
        // Fallback to manual header inspection for edge cases
        if (!$ip) {
            $headers = [
                'HTTP_X_FORWARDED_FOR',
                'HTTP_X_FORWARDED',
                'HTTP_X_CLUSTER_CLIENT_IP',
                'HTTP_FORWARDED_FOR',
                'HTTP_FORWARDED',
                'REMOTE_ADDR',
            ];

            foreach ($headers as $header) {
                if (!empty($_SERVER[$header])) {
                    $ips = explode(',', $_SERVER[$header]);
                    $candidate = trim($ips[0]);
                    if (filter_var($candidate, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                        return $candidate;
                    }
                }
            }
        }

        return $ip;
    }

    private function detectViaIpInfo(string $ip): ?string
    {
        try {
            $response = Http::timeout(5)
                ->withHeaders(['Accept' => 'application/json'])
                ->get("https://ipinfo.io/{$ip}/json")
                ->json();

            if (($response['country'] ?? '') === 'TR') {
                $city = $response['city'] ?? null;
                
                // ipinfo sometimes returns region for city
                if (!$city && !empty($response['region'])) {
                    $city = $response['region'];
                }

                return $this->normalizeTurkishCity($city);
            }
        } catch (\Exception $e) {
            Log::warning('ipinfo error', ['ip' => $ip, 'error' => $e->getMessage()]);
        }

        return null;
    }

    private function detectViaIpApiCo(string $ip): ?string
    {
        try {
            $response = Http::timeout(5)
                ->get("https://ipapi.co/{$ip}/json/")
                ->json();

            if (($response['country_code'] ?? '') === 'TR') {
                return $this->normalizeTurkishCity($response['city'] ?? null);
            }
        } catch (\Exception $e) {
            Log::warning('ipapi.co error', ['ip' => $ip, 'error' => $e->getMessage()]);
        }

        return null;
    }

    private function detectViaIpApi(string $ip): ?string
    {
        try {
            $response = Http::timeout(5)
                ->get("http://ip-api.com/json/{$ip}?fields=status,city,countryCode")
                ->json();

            if (($response['status'] ?? '') === 'success' && ($response['countryCode'] ?? '') === 'TR') {
                return $this->normalizeTurkishCity($response['city'] ?? null);
            }
        } catch (\Exception $e) {
            Log::warning('ip-api error', ['ip' => $ip, 'error' => $e->getMessage()]);
        }

        return null;
    }

    private function normalizeTurkishCity(?string $city): ?string
    {
        if (!$city) {
            return null;
        }

        $mapping = [
            'mersin (icel)' => 'Mersin',
            'icel' => 'Mersin',
            'afyon' => 'Afyonkarahisar',
            'maras' => 'Kahramanmaraş',
            'kahramanmaras' => 'Kahramanmaraş',
            'sanliurfa' => 'Şanlıurfa',
            'urfa' => 'Şanlıurfa',
            'kocaeli (izmit)' => 'Kocaeli',
            'izmit' => 'Kocaeli',
            'adapazari' => 'Sakarya',
            'sakarya (adapazari)' => 'Sakarya',
        ];

        $normalized = mb_strtolower(trim($city), 'UTF-8');
        $normalized = str_replace(['ı', 'ğ', 'ü', 'ş', 'ö', 'ç', 'İ', 'Ğ', 'Ü', 'Ş', 'Ö', 'Ç'], 
                                  ['i', 'g', 'u', 's', 'o', 'c', 'i', 'g', 'u', 's', 'o', 'c'], 
                                  $normalized);

        if (isset($mapping[$normalized])) {
            return $mapping[$normalized];
        }

        return $city;
    }
}
