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
        $weeklyTimes = $this->getWeeklyTimes($city);
        $nextFriday = $this->getNextFridayDate($city);

        foreach ($weeklyTimes as $day) {
            if ($day['date'] === $nextFriday->toDateString()) {
                return $day['dhuhr'];
            }
        }

        return null;
    }

    public function getNextFridayDateTime(string $city): ?Carbon
    {
        $time = $this->getFridayTime($city);

        if (!$time) {
            return null;
        }

        $now = Carbon::now('Europe/Istanbul');
        $friday = $this->getNextFridayDate($city);
        [$hour, $minute] = explode(':', $time);
        $friday->setTime((int) $hour, (int) $minute, 0);

        return $friday;
    }

    private function getNextFridayDate(string $city): Carbon
    {
        $now = Carbon::now('Europe/Istanbul');
        $friday = $now->copy()->next(Carbon::FRIDAY);

        if ($now->isFriday()) {
            $friday = $now->copy();
        }

        return $friday;
    }

    public function getWeeklyTimes(string $city): array
    {
        $normalizedCity = $this->normalizeCity($city);
        $cacheKey = "weekly_times_{$normalizedCity}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($normalizedCity) {
            return $this->fetchWeeklyFromDiyanet($normalizedCity);
        });
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

    public function getCityIds(): array
    {
        return [
            'adana' => 9146, 'adiyaman' => 9158, 'afyonkarahisar' => 9167, 'agri' => 9185,
            'aksaray' => 9193, 'amasya' => 9198, 'ankara' => 9206, 'antalya' => 9225,
            'ardahan' => 9238, 'artvin' => 9246, 'aydin' => 9252, 'balikesir' => 9270,
            'bartin' => 9285, 'batman' => 9288, 'bayburt' => 9295, 'bilecik' => 9297,
            'bingol' => 9303, 'bitlis' => 9311, 'bolu' => 9315, 'burdur' => 9327,
            'bursa' => 9335, 'canakkale' => 9352, 'cankiri' => 9359, 'corum' => 9370,
            'denizli' => 9392, 'diyarbakir' => 9402, 'duzce' => 9414, 'edirne' => 9419,
            'elazig' => 9432, 'erzincan' => 9440, 'erzurum' => 9451, 'eskisehir' => 9470,
            'gaziantep' => 9479, 'giresun' => 9494, 'gumushane' => 9501, 'hakkari' => 9507,
            'hatay' => 20089, 'igdir' => 9522, 'isparta' => 9528, 'istanbul' => 9541,
            'izmir' => 9560, 'kahramanmaras' => 9577, 'karabuk' => 9581, 'karaman' => 9587,
            'kars' => 9594, 'kastamonu' => 9609, 'kayseri' => 9620, 'kilis' => 9629,
            'kirikkale' => 9635, 'kirklareli' => 9638, 'kirsehir' => 9646, 'kocaeli' => 9654,
            'konya' => 9676, 'kutahya' => 9689, 'malatya' => 9703, 'manisa' => 9716,
            'mardin' => 9726, 'mersin' => 9737, 'mugla' => 9747, 'mus' => 9755,
            'nevsehir' => 9760, 'nigde' => 9766, 'ordu' => 9782, 'osmaniye' => 9788,
            'rize' => 9799, 'sakarya' => 9807, 'samsun' => 9819, 'sanliurfa' => 9831,
            'siirt' => 9839, 'sinop' => 9847, 'sirnak' => 9854, 'sivas' => 9868,
            'tekirdag' => 9879, 'tokat' => 9887, 'trabzon' => 9905, 'tunceli' => 9914,
            'usak' => 9919, 'van' => 9930, 'yalova' => 9935, 'yozgat' => 9949,
            'zonguldak' => 9955,
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

    public function getCityId(string $city): ?int
    {
        $normalized = $this->normalizeCity($city);
        $ids = $this->getCityIds();
        
        return $ids[$normalized] ?? null;
    }

    private function fetchWeeklyFromDiyanet(string $city): array
    {
        $cityId = $this->getCityId($city);
        
        if (!$cityId) {
            Log::error('Diyanet city ID not found', ['city' => $city]);
            return [];
        }

        try {
            $response = Http::timeout(15)
                ->get("https://ezanvakti.imsakiyem.com/api/prayer-times/{$cityId}/monthly");

            if ($response->successful()) {
                $data = $response->json();
                
                if (!($data['success'] ?? false) || empty($data['data'])) {
                    Log::warning('Diyanet API empty response', ['city' => $city, 'cityId' => $cityId]);
                    return [];
                }

                $result = [];
                foreach ($data['data'] as $day) {
                    $date = Carbon::parse($day['date'])->setTimezone('Europe/Istanbul');
                    $times = $day['times'] ?? [];
                    
                    $result[] = [
                        'date' => $date->toDateString(),
                        'day_name' => $date->translatedFormat('l'),
                        'imsak' => $times['imsak'] ?? '--:--',
                        'fajr' => $times['imsak'] ?? '--:--',
                        'sunrise' => $times['gunes'] ?? '--:--',
                        'dhuhr' => $times['ogle'] ?? '--:--',
                        'asr' => $times['ikindi'] ?? '--:--',
                        'maghrib' => $times['aksam'] ?? '--:--',
                        'isha' => $times['yatsi'] ?? '--:--',
                    ];
                }

                return $result;
            }
        } catch (\Exception $e) {
            Log::error('Diyanet API error', ['city' => $city, 'cityId' => $cityId, 'error' => $e->getMessage()]);
        }

        return [];
    }
}
