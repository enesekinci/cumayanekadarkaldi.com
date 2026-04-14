@extends('layouts.app')

@section('title', ($fridayTime ? $city . ' ' . __('messages.friday_prayer_time') . ': ' . $fridayTime . ' | ' : '') . __('messages.site_title'))

@section('meta_description', $fridayTime 
    ? __('messages.today_friday_time_is', ['city' => $city, 'time' => $fridayTime]) 
    : __('messages.meta_description_default'))

@push('schema')
@if($fridayTime && $nextFriday)
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "Event",
  "name": "{{ $city }} {{ __('messages.friday_prayer') }}",
  "startDate": "{{ $nextFriday->toIso8601String() }}",
  "eventStatus": "https://schema.org/EventScheduled",
  "eventAttendanceMode": "https://schema.org/OfflineEventAttendanceMode",
  "location": {
    "@type": "Place",
    "name": "{{ $city }}",
    "address": {
      "@type": "PostalAddress",
      "addressLocality": "{{ $city }}",
      "addressCountry": "TR"
    }
  }
}
</script>
@endif
@endpush

@section('content')
<div class="card">
    @if($isFriday)
        <span class="friday-badge">{{ __('messages.today_is_friday') }}</span>
    @endif
    
    <h1 style="font-size: 1.5rem; margin-bottom: 1rem;">
        {{ __('messages.friday_prayer_time_for', ['city' => $city]) }}: {{ $fridayTime ?: '---' }}
    </h1>
    
    @if($fridayTime)
        <div class="time-display">{{ $fridayTime }}</div>
        <div class="time-label">{{ __('messages.prayer_time_for', ['city' => $city]) }}</div>
        
        <div class="countdown" id="countdown" data-target="{{ $nextFriday?->toIso8601String() }}">
            --:--:--
        </div>
        <div class="countdown-label">{{ __('messages.time_remaining') }}</div>
    @else
        <p style="color: #dc2626; margin-top: 1rem;">{{ __('messages.unable_to_fetch_select') }}</p>
    @endif
    
    <form class="city-form" action="{{ route('home') }}" method="GET">
        <select name="city" required>
            <option value="">{{ __('messages.select_city') }}</option>
            @foreach($cities as $slug => $name)
                <option value="{{ $slug }}" {{ $normalizedCity === $slug ? 'selected' : '' }}>
                    {{ $name }}
                </option>
            @endforeach
        </select>
        <button type="submit">{{ __('messages.show') }}</button>
    </form>
</div>

@if(!empty($weeklyTimes))
<div class="card">
    <h2 style="font-size: 1.25rem; margin-bottom: 1rem;">{{ __('messages.weekly_prayer_times') }}</h2>
    <div style="overflow-x: auto;">
        <table class="prayer-table">
            <thead>
                <tr>
                    <th>{{ __('messages.day') }}</th>
                    <th>{{ __('messages.imsak') }}</th>
                    <th>{{ __('messages.sunrise') }}</th>
                    <th>{{ __('messages.dhuhr') }}</th>
                    <th>{{ __('messages.asr') }}</th>
                    <th>{{ __('messages.maghrib') }}</th>
                    <th>{{ __('messages.isha') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($weeklyTimes as $day)
                    <tr class="{{ \Carbon\Carbon::today('Europe/Istanbul')->toDateString() === $day['date'] ? 'highlight' : '' }}">
                        <td>{{ $day['day_name'] }}</td>
                        <td>{{ $day['imsak'] }}</td>
                        <td>{{ $day['sunrise'] }}</td>
                        <td><strong>{{ $day['dhuhr'] }}</strong></td>
                        <td>{{ $day['asr'] }}</td>
                        <td>{{ $day['maghrib'] }}</td>
                        <td>{{ $day['isha'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<div class="card">
    <h2 style="font-size: 1.25rem; margin-bottom: 1rem;">{{ __('messages.other_cities') }}</h2>
    <p style="color: #5c706e; margin-bottom: 1rem;">
        {{ __('messages.click_city_to_learn') }}
    </p>
    <div class="cities-grid">
        @foreach($cities as $slug => $name)
            @php
                $suffix = app()->getLocale() === 'en' ? '-friday-time' : '-cuma-saati';
            @endphp
            <a href="{{ app()->getLocale() === 'en' ? url('/en/' . $slug . $suffix) : url('/' . $slug . $suffix) }}">
                {{ $name }}
            </a>
        @endforeach
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    const el = document.getElementById('countdown');
    if (!el) return;
    
    const target = new Date(el.dataset.target);
    const fridayArrived = @json(__('messages.friday_time_arrived'));
    
    function update() {
        const now = new Date();
        let diff = target - now;
        
        if (diff < 0) {
            el.textContent = fridayArrived;
            return;
        }
        
        const days = Math.floor(diff / 86400000);
        diff %= 86400000;
        const hours = Math.floor(diff / 3600000);
        diff %= 3600000;
        const minutes = Math.floor(diff / 60000);
        diff %= 60000;
        const seconds = Math.floor(diff / 1000);
        
        const parts = [];
        if (days > 0) parts.push(days + 'g');
        parts.push(String(hours).padStart(2, '0'));
        parts.push(String(minutes).padStart(2, '0'));
        parts.push(String(seconds).padStart(2, '0'));
        
        el.textContent = parts.join(':');
    }
    
    update();
    setInterval(update, 1000);
    
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(){}, function(){}, { timeout: 5000 });
    }
})();
</script>
@endpush
