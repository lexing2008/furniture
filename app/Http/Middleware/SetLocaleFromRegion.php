<?php

namespace App\Http\Middleware;

use App\Models\Region;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * The language of a site and of the account is the language of the country of the current region.
 * Admin pages keep the default locale.
 */
class SetLocaleFromRegion
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->is('admin', 'admin/*')) {
            $region = Region::fromHost($request->getHost());
            $locale = $region?->language() ?? $region?->fallbackLanguage();
            $supported = config('app.supported_locales', ['ru', 'en']);

            if ($locale !== null) {
                if (! in_array($locale, $supported, true)) {
                    $locale = $region?->fallbackLanguage();
                }

                if (! in_array($locale, $supported, true)) {
                    $locale = config('app.fallback_locale', 'en');
                }

                App::setLocale($locale);
            }
        }

        return $next($request);
    }
}
