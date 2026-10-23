<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cookies set for one host ("brest.furniture.lex") live next to the cookies set for the whole domain
 * (".furniture.lex", SESSION_DOMAIN) and the browser sends both. The server and JavaScript take the first one,
 * the old one, whose session is long gone, and every form gets 419 "Page Expired".
 *
 * The domain cookie is refreshed on every response, the old host-only one never is. So when the request carries
 * the same cookie twice, the host-only ones are expired here and the next request is clean.
 */
class ExpireStaleSessionCookies
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (config('session.domain') === null) {
            return $response;
        }

        $header = (string) $request->server->get('HTTP_COOKIE');

        foreach ([config('session.cookie'), 'XSRF-TOKEN'] as $name) {
            if (preg_match_all('/(?:^|;\s*)'.preg_quote((string) $name, '/').'=/', $header) > 1) {
                // No domain: only the cookie of this host is removed, the domain one stays
                $response->headers->setCookie(
                    new Cookie($name, null, 1, config('session.path'), null, (bool) config('session.secure'), true, false, config('session.same_site')),
                );
            }
        }

        return $response;
    }
}
