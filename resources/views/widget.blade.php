<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $city }} {{ __('messages.friday_prayer_time') }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #ffffff;
            color: #134e4a;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 1rem;
        }
        .widget {
            text-align: center;
            width: 100%;
        }
        .city {
            font-size: 1rem;
            font-weight: 600;
            color: #115e59;
            margin-bottom: 0.25rem;
        }
        .time {
            font-size: 2.5rem;
            font-weight: 700;
            color: #0f766e;
            line-height: 1;
        }
        .label {
            font-size: 0.75rem;
            color: #5c706e;
            margin-top: 0.25rem;
        }
        .countdown {
            font-size: 1.125rem;
            font-weight: 600;
            color: #f59e0b;
            margin-top: 0.5rem;
            font-variant-numeric: tabular-nums;
        }
        a {
            display: inline-block;
            margin-top: 0.5rem;
            font-size: 0.7rem;
            color: #0f766e;
            text-decoration: none;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="widget">
        <div class="city">{{ $city }}</div>
        <div class="time">{{ $fridayTime ?: '--:--' }}</div>
        <div class="label">{{ __('messages.friday_prayer') }}</div>
        @if($nextFriday)
        <div class="countdown" id="cd">--:--:--</div>
        @endif
        @php
            $link = app()->getLocale() === 'en' 
                ? url('/en/' . $normalizedCity . '-friday-time') 
                : url('/' . $normalizedCity . '-cuma-saati');
        @endphp
        <a href="{{ $link }}" target="_blank">cumayanekadarkaldi.com</a>
    </div>

    @if($nextFriday)
    <script>
    (function(){
        const el = document.getElementById('cd');
        const target = new Date('{{ $nextFriday->toIso8601String() }}');
        const fridayArrived = @json(__('messages.friday_time_arrived'));
        function u(){
            let d = target - new Date();
            if(d<0){el.textContent=fridayArrived;return;}
            const days = Math.floor(d/86400000); d%=86400000;
            const h = Math.floor(d/3600000); d%=3600000;
            const m = Math.floor(d/60000); d%=60000;
            const s = Math.floor(d/1000);
            const p=[];
            if(days>0) p.push(days+'g');
            p.push(String(h).padStart(2,'0'),String(m).padStart(2,'0'),String(s).padStart(2,'0'));
            el.textContent=p.join(':');
        }
        u(); setInterval(u,1000);
    })();
    </script>
    @endif
</body>
</html>
