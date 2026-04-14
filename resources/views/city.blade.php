@extends('layouts.app')

@section('title', $fridayTime 
    ? $city . ' ' . __('messages.friday_prayer_time') . ': ' . $fridayTime . ' | ' . __('messages.site_title')
    : $city . ' ' . __('messages.friday_prayer_time') . ' | ' . __('messages.site_title'))

@section('meta_description', $fridayTime 
    ? __('messages.today_friday_time_is', ['city' => $city, 'time' => $fridayTime])
    : $city . ' ' . strtolower(__('messages.friday_prayer_time')) . ' ve ' . strtolower(__('messages.time_remaining')) . '. ')

@push('schema')
@if($fridayTime && $schemaDate)
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "Event",
  "name": "{{ $city }} {{ __('messages.friday_prayer') }}",
  "startDate": "{{ $schemaDate }}",
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
@php
    $suffix = app()->getLocale() === 'en' ? '-friday-time' : '-cuma-saati';
    $widgetUrl = app()->getLocale() === 'en' ? url('/en/widget/' . $normalizedCity) : url('/widget/' . $normalizedCity);
@endphp

<div class="card">
    <h1 style="font-size: 1.75rem; margin-bottom: 0.5rem; color: var(--primary-dark);">
        {{ __('messages.friday_prayer_time_for', ['city' => $city]) }}: {{ $fridayTime ?: '---' }}
    </h1>
    
    <p style="font-size: 1.125rem; color: #374151; margin-bottom: 1.5rem;">
        {{ __('messages.today_friday_time_is', ['city' => $city, 'time' => $fridayTime ?: '---']) }}
    </p>
    
    @if($fridayTime)
        <div class="time-display">{{ $fridayTime }}</div>
        <div class="time-label">{{ __('messages.prayer_time_for', ['city' => $city]) }}</div>
        
        <div class="countdown" id="countdown" data-target="{{ $nextFriday?->toIso8601String() }}">
            --:--:--
        </div>
        <div class="countdown-label">{{ __('messages.time_remaining_for', ['city' => $city]) }}</div>
    @else
        <p style="color: #dc2626; margin-top: 1rem;">{{ __('messages.unable_to_fetch') }}</p>
    @endif
    
    <form class="city-form" action="{{ app()->getLocale() === 'en' ? url('/en') : url('/') }}" method="GET">
        <select name="city" required>
            <option value="">{{ __('messages.select_other_city') }}</option>
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
    <h2 style="font-size: 1.25rem; margin-bottom: 1rem;">{{ __('messages.widget_title', ['city' => $city]) }}</h2>
    <div class="widget-preview">
        <iframe src="{{ $widgetUrl }}" width="100%" height="120" style="border:none; border-radius:0.5rem;"></iframe>
    </div>
    <textarea class="embed-code" rows="3" readonly id="embedCode">&lt;iframe src=&quot;{{ $widgetUrl }}&quot; width=&quot;100%&quot; height=&quot;120&quot; style=&quot;border:none; border-radius:0.5rem;&quot;&gt;&lt;/iframe&gt;</textarea>
    <button class="copy-btn" onclick="copyEmbed()">{{ __('messages.copy') }}</button>
</div>

<div class="card">
    <h2 style="font-size: 1.25rem; margin-bottom: 1rem;">{{ __('messages.about_city', ['city' => $city]) }}</h2>
    <p style="color: #5c706e; text-align: left;">
        {{ __('messages.city_description', ['city' => $city, 'time' => $fridayTime ?: '---']) }}
    </p>
</div>

<div class="card">
    <h2 style="font-size: 1.25rem; margin-bottom: 1rem;">{{ __('messages.popular_cities') }}</h2>
    <div class="cities-grid">
        @php
        $popular = [
            'istanbul' => 'İstanbul', 'ankara' => 'Ankara', 'izmir' => 'İzmir', 'bursa' => 'Bursa',
            'antalya' => 'Antalya', 'konya' => 'Konya', 'adana' => 'Adana', 'gaziantep' => 'Gaziantep',
            'kocaeli' => 'Kocaeli', 'sakarya' => 'Sakarya', 'trabzon' => 'Trabzon', 'samsun' => 'Samsun',
            'mersin' => 'Mersin', 'eskisehir' => 'Eskişehir', 'diyarbakir' => 'Diyarbakır'
        ];
        @endphp
        @foreach($popular as $slug => $name)
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
})();

function copyEmbed() {
    const ta = document.getElementById('embedCode');
    ta.select();
    navigator.clipboard.writeText(ta.value).then(function() {
        const btn = document.querySelector('.copy-btn');
        const original = btn.textContent;
        btn.textContent = @json(__('messages.copied'));
        setTimeout(function() { btn.textContent = original; }, 2000);
    });
}
</script>
@endpush
