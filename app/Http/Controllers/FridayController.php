<?php

namespace App\Http\Controllers;

use App\Services\LocationService;
use App\Services\PrayerTimeService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class FridayController extends Controller
{
    public function __construct(
        private PrayerTimeService $prayerTimeService,
        private LocationService $locationService,
    ) {}

    public function home(Request $request)
    {
        $city = $request->query('city');
        
        if (!$city) {
            $city = $this->locationService->detectCity();
        }

        $normalizedCity = $this->prayerTimeService->normalizeCity($city);
        $formattedCity = $this->prayerTimeService->formatCityName($city);
        $fridayTime = $this->prayerTimeService->getFridayTime($city);
        $nextFriday = $this->prayerTimeService->getNextFridayDateTime($city);
        $weeklyTimes = $this->prayerTimeService->getWeeklyTimes($city);

        return view('home', [
            'city' => $formattedCity,
            'normalizedCity' => $normalizedCity,
            'fridayTime' => $fridayTime,
            'nextFriday' => $nextFriday,
            'weeklyTimes' => $weeklyTimes,
            'cities' => $this->prayerTimeService->getCityMapping(),
            'isFriday' => $nextFriday?->isFriday() && Carbon::now('Europe/Istanbul')->isFriday(),
            'locale' => app()->getLocale(),
        ]);
    }

    public function city(Request $request)
    {
        $citySlug = $request->route('citySlug');
        $citySlug = str_replace(['-cuma-saati', '-friday-time'], '', $citySlug);
        $normalizedCity = $this->prayerTimeService->normalizeCity($citySlug);

        if (!$this->prayerTimeService->isValidCity($citySlug)) {
            abort(404);
        }

        $formattedCity = $this->prayerTimeService->formatCityName($citySlug);
        $fridayTime = $this->prayerTimeService->getFridayTime($citySlug);
        $nextFriday = $this->prayerTimeService->getNextFridayDateTime($citySlug);
        $weeklyTimes = $this->prayerTimeService->getWeeklyTimes($citySlug);
        $schemaDate = $nextFriday?->toIso8601String();

        return view('city', [
            'city' => $formattedCity,
            'normalizedCity' => $normalizedCity,
            'fridayTime' => $fridayTime,
            'nextFriday' => $nextFriday,
            'schemaDate' => $schemaDate,
            'weeklyTimes' => $weeklyTimes,
            'cities' => $this->prayerTimeService->getCityMapping(),
            'locale' => app()->getLocale(),
        ]);
    }

    public function apiFridayTime(Request $request)
    {
        $city = $request->query('city', 'Istanbul');
        $fridayTime = $this->prayerTimeService->getFridayTime($city);
        $nextFriday = $this->prayerTimeService->getNextFridayDateTime($city);
        $weeklyTimes = $this->prayerTimeService->getWeeklyTimes($city);

        return response()->json([
            'city' => $this->prayerTimeService->formatCityName($city),
            'friday_time' => $fridayTime,
            'next_friday' => $nextFriday?->toDateTimeString(),
            'weekly_times' => $weeklyTimes,
        ]);
    }

    public function widget(Request $request)
    {
        $city = $request->route('city');
        $normalizedCity = $this->prayerTimeService->normalizeCity($city);
        
        if (!$this->prayerTimeService->isValidCity($city)) {
            abort(404);
        }

        $formattedCity = $this->prayerTimeService->formatCityName($city);
        $fridayTime = $this->prayerTimeService->getFridayTime($city);
        $nextFriday = $this->prayerTimeService->getNextFridayDateTime($city);

        return view('widget', [
            'city' => $formattedCity,
            'normalizedCity' => $normalizedCity,
            'fridayTime' => $fridayTime,
            'nextFriday' => $nextFriday,
            'locale' => app()->getLocale(),
        ]);
    }
}
