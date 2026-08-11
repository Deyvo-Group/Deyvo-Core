<?php

declare(strict_types=1);

namespace Deyvo\Core\Http\Middleware;

use Closure;
use Deyvo\Core\Support\AuditLogger;
use Illuminate\Http\Request;
use Throwable;

final class AuditDashboardFailuresMiddleware
{
    public function __construct(
        private AuditLogger $audit,
    ) {
    }

    public function handle(Request $request, Closure $next): mixed
    {
        try {
            return $next($request);
        } catch (Throwable $exception) {
            $this->audit->record('dashboard.request_failed', null, [
                'exception' => $exception::class,
                'message' => mb_substr($exception->getMessage(), 0, 1000),
                'subject_label' => $request->route()?->getName(),
            ]);

            throw $exception;
        }
    }
}
