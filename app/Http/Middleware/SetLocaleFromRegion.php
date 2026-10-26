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
            $locale = Region::fromHost($request->getHost())?->language();

            if ($locale !== null) {
                // An unsupported country language falls back to the application fallback locale
                App::setLocale(
                    in_array($locale, config('app.supported_locales', ['ru', 'en']), true)
                        ? $locale
                        : config('app.fallback_locale', 'en')
                );
            }
        }

        return $next($request);
    }
}
