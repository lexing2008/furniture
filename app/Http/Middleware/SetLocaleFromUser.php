<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * The account interface uses the language chosen by the user, if it is supported.
 */
class SetLocaleFromUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->user()?->lang;
        $supported = config('app.supported_locales', ['ru', 'en']);

        if (is_string($locale) && in_array($locale, $supported, true)) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}
