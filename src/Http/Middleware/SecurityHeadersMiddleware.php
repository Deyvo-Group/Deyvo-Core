<?php

declare(strict_types=1);

namespace Deyvo\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class SecurityHeadersMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        foreach (config('deyvo-core.security_headers.headers', []) as $header => $value) {
            if (is_string($header) && is_string($value) && ! $response->headers->has($header)) {
                $response->headers->set($header, $value);
            }
        }

        return $response;
    }
}
