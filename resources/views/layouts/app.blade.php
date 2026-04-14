<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="@yield('meta_description', __('messages.meta_description_default'))">
    <meta name="theme-color" content="#0f766e">
    <meta name="robots" content="index, follow">
    
    <title>@yield('title', __('messages.site_title'))</title>
    
    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="preconnect" href="https://api.aladhan.com">
    
    @stack('schema')
    
    <style>
        :root {
            --primary: #0f766e;
            --primary-dark: #115e59;
            --bg: #f0fdfa;
            --text: #134e4a;
            --card: #ffffff;
            --accent: #f59e0b;
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.6;
            min-height: 100vh;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 1.5rem;
        }
        
        header {
            text-align: center;
            padding: 2rem 0;
        }
        
        header h1 {
            font-size: 1.75rem;
            color: var(--primary-dark);
        }
        
        header p {
            color: #5c706e;
            margin-top: 0.5rem;
        }
        
        .lang-switcher {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 1rem;
        }
        
        .lang-switcher a {
            padding: 0.375rem 0.75rem;
            border-radius: 0.375rem;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--primary);
            background: white;
            border: 1px solid #ccfbf1;
            transition: all 0.15s;
        }
        
        .lang-switcher a:hover,
        .lang-switcher a.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        
        .card {
            background: var(--card);
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
            margin-bottom: 1.5rem;
            text-align: center;
        }
        
        .time-display {
            font-size: 4rem;
            font-weight: 700;
            color: var(--primary);
            letter-spacing: -0.02em;
        }
        
        .time-label {
            font-size: 1.125rem;
            color: #5c706e;
            margin-top: 0.5rem;
        }
        
        .countdown {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--accent);
            font-variant-numeric: tabular-nums;
            margin-top: 1rem;
        }
        
        .countdown-label {
            font-size: 1rem;
            color: #5c706e;
        }
        
        .city-form {
            display: flex;
            gap: 0.5rem;
            max-width: 400px;
            margin: 1.5rem auto 0;
        }
        
        .city-form select {
            flex: 1;
            padding: 0.75rem 1rem;
            border: 2px solid #ccfbf1;
            border-radius: 0.5rem;
            font-size: 1rem;
            background: white;
            color: var(--text);
            cursor: pointer;
        }
        
        .city-form button {
            padding: 0.75rem 1.5rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 0.5rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        
        .city-form button:hover {
            background: var(--primary-dark);
        }
        
        .cities-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 0.75rem;
            margin-top: 1.5rem;
        }
        
        .cities-grid a {
            display: block;
            padding: 0.625rem 0.75rem;
            background: white;
            border: 1px solid #ccfbf1;
            border-radius: 0.5rem;
            text-align: center;
            text-decoration: none;
            color: var(--primary);
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.15s;
        }
        
        .cities-grid a:hover {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        
        .prayer-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
            font-size: 0.875rem;
        }
        
        .prayer-table th,
        .prayer-table td {
            padding: 0.625rem 0.5rem;
            border-bottom: 1px solid #e5e7eb;
            text-align: center;
        }
        
        .prayer-table th {
            background: #f9fafb;
            font-weight: 600;
            color: #374151;
        }
        
        .prayer-table tr:hover td {
            background: #f9fafb;
        }
        
        .prayer-table .highlight {
            background: #fef3c7 !important;
            font-weight: 600;
        }
        
        footer {
            text-align: center;
            padding: 2rem 0;
            color: #5c706e;
            font-size: 0.875rem;
        }
        
        .friday-badge {
            display: inline-block;
            background: var(--accent);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            font-weight: 600;
            font-size: 0.875rem;
            margin-bottom: 1rem;
        }
        
        .widget-preview {
            border: 2px dashed #ccfbf1;
            border-radius: 0.75rem;
            padding: 1.5rem;
            margin-top: 1rem;
            background: #f9fafb;
        }
        
        .embed-code {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            font-family: monospace;
            font-size: 0.8rem;
            resize: none;
            margin-top: 0.75rem;
        }
        
        .copy-btn {
            margin-top: 0.5rem;
            padding: 0.5rem 1rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            cursor: pointer;
        }
        
        @media (max-width: 640px) {
            .time-display { font-size: 3rem; }
            .countdown { font-size: 1.75rem; }
            .cities-grid { grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); }
            .prayer-table { font-size: 0.75rem; }
            .prayer-table th, .prayer-table td { padding: 0.5rem 0.25rem; }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>🕌 {{ __('messages.site_title') }}</h1>
            <p>{{ __('messages.site_tagline') }}</p>
            <div class="lang-switcher">
                @php
                    $currentPath = request()->path();
                    $segments = explode('/', ltrim($currentPath, '/'));
                    $firstSegment = $segments[0] ?? '';
                    $isLocaleRoute = in_array($firstSegment, ['tr','en'], true);
                    
                    // Remove locale prefix if exists
                    $pathWithoutLocale = $isLocaleRoute ? implode('/', array_slice($segments, 1)) : $currentPath;
                    $pathWithoutLocale = ltrim($pathWithoutLocale, '/');
                    
                    // Convert suffix based on locale
                    $trPath = str_replace('-friday-time', '-cuma-saati', $pathWithoutLocale);
                    $enPath = str_replace('-cuma-saati', '-friday-time', $pathWithoutLocale);
                    
                    $trUrl = $trPath ? url('/' . $trPath) : url('/');
                    $enUrl = $enPath ? url('/en/' . $enPath) : url('/en');
                @endphp
                <a href="{{ $trUrl }}" class="{{ app()->getLocale() === 'tr' ? 'active' : '' }}">{{ __('messages.lang_tr') }}</a>
                <a href="{{ $enUrl }}" class="{{ app()->getLocale() === 'en' ? 'active' : '' }}">{{ __('messages.lang_en') }}</a>
            </div>
        </header>
        
        <main>
            @yield('content')
        </main>
        
        <footer>
            <p>{!! __('messages.data_source') !!}</p>
            <p>{!! __('messages.copyright', ['year' => date('Y')]) !!}</p>
        </footer>
    </div>
    
    @stack('scripts')
</body>
</html>
