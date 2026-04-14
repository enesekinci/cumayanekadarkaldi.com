<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PrayerTimeService
{
    private const CACHE_TTL = 86400; // 24 hours
    private const FALLBACK_CITY = 'Istanbul';

    public function getFridayTime(string $city): ?string
    {
        $normalizedCity = $this->normalizeCity($city);
        $cacheKey = "friday_time_{$normalizedCity}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($normalizedCity) {
            return $this->fetchFromApi($normalizedCity);
        });
    }

    public function getNextFridayDateTime(string $city): ?Carbon
    {
        $time = $this->getFridayTime($city);

        if (!$time) {
            return null;
        }

        $now = Carbon::now('Europe/Istanbul');
        $friday = $now->copy()->next(Carbon::FRIDAY);

        [$hour, $minute] = explode(':', $time);
        $friday->setTime((int) $hour, (int) $minute, 0);

        // If we're on Friday and the time hasn't passed yet, use today
        if ($now->isFriday() && $now->lt($friday->copy()->setDateFrom($now))) {
            $friday = $now->copy()->setTime((int) $hour, (int) $minute, 0);
        }

        return $friday;
    }

    public function getCityList(): array
    {
        return array_keys($this->getCityMapping());
    }

    public function getCityMapping(): array
    {
        return [
            'adana' => 'Adana',
            'adiyaman' => 'Adıyaman',
            'afyonkarahisar' => 'Afyonkarahisar',
            'agri' => 'Ağrı',
            'amasya' => 'Amasya',
            'ankara' => 'Ankara',
            'antalya' => 'Antalya',
            'artvin' => 'Artvin',
            'aydin' => 'Aydın',
            'balikesir' => 'Balıkesir',
            'bilecik' => 'Bilecik',
            'bingol' => 'Bingöl',
            'bitlis' => 'Bitlis',
            'bolu' => 'Bolu',
            'burdur' => 'Burdur',
            'bursa' => 'Bursa',
            'canakkale' => 'Çanakkale',
            'cankiri' => 'Çankırı',
            'corum' => 'Çorum',
            'denizli' => 'Denizli',
            'diyarbakir' => 'Diyarbakır',
            'edirne' => 'Edirne',
            'elazig' => 'Elazığ',
            'erzincan' => 'Erzincan',
            'erzurum' => 'Erzurum',
            'eskisehir' => 'Eskişehir',
            'gaziantep' => 'Gaziantep',
            'giresun' => 'Giresun',
            'gumushane' => 'Gümüşhane',
            'hakkari' => 'Hakkari',
            'hatay' => 'Hatay',
            'isparta' => 'Isparta',
            'mersin' => 'Mersin',
            'istanbul' => 'İstanbul',
            'izmir' => 'İzmir',
            'kars' => 'Kars',
            'kastamonu' => 'Kastamonu',
            'kayseri' => 'Kayseri',
            'kirklareli' => 'Kırklareli',
            'kirsehir' => 'Kırşehir',
            'kocaeli' => 'Kocaeli',
            'konya' => 'Konya',
            'kutahya' => 'Kütahya',
            'malatya' => 'Malatya',
            'manisa' => 'Manisa',
            'kahramanmaras' => 'Kahramanmaraş',
            'mardin' => 'Mardin',
            'mugla' => 'Muğla',
            'mus' => 'Muş',
            'nevsehir' => 'Nevşehir',
            'nigde' => 'Niğde',
            'ordu' => 'Ordu',
            'rize' => 'Rize',
            'sakarya' => 'Sakarya',
            'samsun' => 'Samsun',
            'siirt' => 'Siirt',
            'sinop' => 'Sinop',
            'sivas' => 'Sivas',
            'tekirdag' => 'Tekirdağ',
            'tokat' => 'Tokat',
            'trabzon' => 'Trabzon',
            'tunceli' => 'Tunceli',
            'sanliurfa' => 'Şanlıurfa',
            'usak' => 'Uşak',
            'van' => 'Van',
            'yozgat' => 'Yozgat',
            'zonguldak' => 'Zonguldak',
            'aksaray' => 'Aksaray',
            'bayburt' => 'Bayburt',
            'karaman' => 'Karaman',
            'kirikkale' => 'Kırıkkale',
            'batman' => 'Batman',
            'sirnak' => 'Şırnak',
            'bartin' => 'Bartın',
            'ardahan' => 'Ardahan',
            'igdir' => 'Iğdır',
            'yalova' => 'Yalova',
            'karabuk' => 'Karabük',
            'kilis' => 'Kilis',
            'osmaniye' => 'Osmaniye',
            'duzce' => 'Düzce',
        ];
    }

    public function normalizeCity(string $city): string
    {
        $normalized = mb_strtolower(trim($city), 'UTF-8');
        $normalized = str_replace(['ı', 'ğ', 'ü', 'ş', 'ö', 'ç', 'İ', 'Ğ', 'Ü', 'Ş', 'Ö', 'Ç'], 
                                  ['i', 'g', 'u', 's', 'o', 'c', 'i', 'g', 'u', 's', 'o', 'c'], 
                                  $normalized);
        $normalized = preg_replace('/[^a-z]/', '', $normalized);

        $mapping = [
            'afyon' => 'afyonkarahisar',
            'icel' => 'mersin',
            'maras' => 'kahramanmaras',
            'urfa' => 'sanliurfa',
        ];

        return $mapping[$normalized] ?? $normalized;
    }

    public function formatCityName(string $city): string
    {
        $normalized = $this->normalizeCity($city);
        $mapping = $this->getCityMapping();
        
        return $mapping[$normalized] ?? ucfirst($normalized);
    }

    public function isValidCity(string $city): bool
    {
        return array_key_exists($this->normalizeCity($city), $this->getCityMapping());
    }

    public function getWeeklyTimes(string $city): array
    {
        $normalizedCity = $this->normalizeCity($city);
        $cacheKey = "weekly_times_{$normalizedCity}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($normalizedCity) {
            return $this->fetchWeeklyFromApi($normalizedCity);
        });
    }

    private function fetchFromApi(string $city): ?string
    {
        $displayCity = $this->formatCityName($city);

        try {
            $response = Http::timeout(10)
                ->get('https://api.aladhan.com/v1/timingsByCity', [
                    'city' => $displayCity,
                    'country' => 'Turkey',
                    'method' => 13,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['data']['timings']['Dhuhr'] ?? null;
            }
        } catch (\Exception $e) {
            Log::error('AlAdhan API error', ['city' => $city, 'error' => $e->getMessage()]);
        }

        return null;
    }

    private function fetchWeeklyFromApi(string $city): array
    {
        $displayCity = $this->formatCityName($city);
        $now = Carbon::now('Europe/Istanbul');

        try {
            $response = Http::timeout(15)
                ->get("https://api.aladhan.com/v1/calendarByCity/{$now->year}/{$now->month}", [
                    'city' => $displayCity,
                    'country' => 'Turkey',
                    'method' => 13,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $days = $data['data'] ?? [];
                $result = [];

                foreach ($days as $day) {
                    $date = Carbon::parse($day['date']['gregorian']['date'], 'Europe/Istanbul');
                    $timings = $day['timings'];
                    
                    $result[] = [
                        'date' => $date->toDateString(),
                        'day_name' => $date->translatedFormat('l'),
                        'imsak' => $this->cleanTime($timings['Imsak'] ?? ''),
                        'fajr' => $this->cleanTime($timings['Fajr'] ?? ''),
                        'sunrise' => $this->cleanTime($timings['Sunrise'] ?? ''),
                        'dhuhr' => $this->cleanTime($timings['Dhuhr'] ?? ''),
                        'asr' => $this->cleanTime($timings['Asr'] ?? ''),
                        'maghrib' => $this->cleanTime($timings['Maghrib'] ?? ''),
                        'isha' => $this->cleanTime($timings['Isha'] ?? ''),
                    ];
                }

                return $result;
            }
        } catch (\Exception $e) {
            Log::error('AlAdhan weekly API error', ['city' => $city, 'error' => $e->getMessage()]);
        }

        return [];
    }

    private function cleanTime(string $time): string
    {
        return trim(explode(' ', $time)[0]);
    }
}
