<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->route('locale');
        $supported = ['tr', 'en'];
        
        if ($locale && in_array($locale, $supported, true)) {
            app()->setLocale($locale);
            session(['locale' => $locale]);
        } else {
            // Root routes (e.g. /, /bursa-cuma-saati) default to TR
            app()->setLocale('tr');
            session(['locale' => 'tr']);
        }

        return $next($request);
    }
}
