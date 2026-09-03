<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetLocale
{
    /**
     * Idiomas soportados por el sistema.
     */
    private const SUPPORTED = ['es', 'en'];

    public function handle(Request $request, Closure $next)
    {
        $locale = config('app.locale');

        $sessionLocale = session('locale');

        if ($sessionLocale && in_array($sessionLocale, self::SUPPORTED, true)) {
            $locale = $sessionLocale;
        }

        App::setLocale($locale);

        return $next($request);
    }
}
