<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Trusts the X-Forwarded-* headers (real IP, https, host) only from the proxies listed in
 * TRUSTED_PROXIES: comma-separated IPs, or * for "whoever is connecting to me right now"
 * (right for tunnels like Cloudflare/ngrok and hosts like Render/Railway).
 * Empty = trust nobody. Read from config at request time, so the .env value is always loaded.
 */
class TrustConfiguredProxies
{
    public function handle(Request $request, Closure $next): Response
    {
        $setting = trim((string) config('app.trusted_proxies', ''));

        if ($setting !== '') {
            $proxies = $setting === '*'
                ? [(string) $request->server->get('REMOTE_ADDR')]
                : array_values(array_filter(array_map('trim', explode(',', $setting))));

            Request::setTrustedProxies(
                $proxies,
                Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_HOST
                | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO
                | Request::HEADER_X_FORWARDED_PREFIX
            );
        }

        return $next($request);
    }
}
