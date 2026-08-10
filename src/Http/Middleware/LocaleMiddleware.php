<?php

declare(strict_types=1);

namespace Deyvo\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class LocaleMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $localeEnabled = config('deyvo-core.locale.enabled', true);
        $timezoneEnabled = config('deyvo-core.timezone.enabled', true);

        if (! $localeEnabled && ! $timezoneEnabled) {
            return $next($request);
        }

        $originalLocale = app()->getLocale();
        $originalTimezone = date_default_timezone_get();

        if ($localeEnabled) {
            app()->setLocale($this->resolveLocale($request, $originalLocale));
        }

        if ($timezoneEnabled) {
            date_default_timezone_set($this->resolveTimezone($originalTimezone));
        }

        try {
            return $next($request);
        } finally {
            app()->setLocale($originalLocale);
            date_default_timezone_set($originalTimezone);
        }
    }

    private function resolveLocale(Request $request, string $fallback): string
    {
        $default = config('deyvo-core.locale.default', $fallback);
        $supported = config('deyvo-core.locale.supported', []);

        if (! is_string($default) || ! is_array($supported) || $supported === []) {
            return $fallback;
        }

        $parameter = config('deyvo-core.locale.query_parameter', 'locale');
        $sessionKey = config('deyvo-core.locale.session_key', 'deyvo.locale');
        $queryLocale = $request->query($parameter);
        $sessionLocale = $request->hasSession() ? $request->session()->get($sessionKey) : null;
        $locale = is_string($queryLocale) ? $queryLocale : $sessionLocale;

        if (is_string($locale) && in_array($locale, $supported, true)) {
            if (is_string($queryLocale) && $request->hasSession()) {
                $request->session()->put($sessionKey, $locale);
            }

            return $locale;
        }

        return in_array($default, $supported, true) ? $default : $fallback;
    }

    private function resolveTimezone(string $fallback): string
    {
        $timezone = config('deyvo-core.timezone.default', $fallback);

        if (! is_string($timezone) || ! in_array($timezone, timezone_identifiers_list(), true)) {
            return $fallback;
        }

        return $timezone;
    }
}
