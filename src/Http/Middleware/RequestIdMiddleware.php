<?php

declare(strict_types=1);

namespace Deyvo\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

final class RequestIdMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $header = config('deyvo-core.request_id.header', 'X-Request-ID');
        $requestId = $request->headers->get($header);
        $requestId = is_string($requestId) && preg_match('/^[A-Za-z0-9-]{1,64}$/', $requestId) === 1
            ? $requestId
            : (string) Str::uuid();

        $request->attributes->set('deyvo.request_id', $requestId);

        $response = $next($request);
        $response->headers->set($header, $requestId);

        return $response;
    }
}
