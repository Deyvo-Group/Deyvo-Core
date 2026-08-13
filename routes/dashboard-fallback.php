<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

$dashboardPath = trim((string) config('deyvo-core.dashboard.path', 'deyvo'), '/');
$dashboardMiddleware = config('deyvo-core.dashboard.middleware', ['web', 'auth']);

if ($dashboardPath !== '') {
    Route::prefix($dashboardPath)
        ->middleware($dashboardMiddleware)
        ->as('deyvo.dashboard.')
        ->group(function () use ($dashboardPath): void {
            Route::fallback(static function () use ($dashboardPath) {
                $view = (string) config('deyvo-core.errors.dashboard_404_view', 'deyvo::dashboard.errors.404');

                if ($view === '' || ! view()->exists($view)) {
                    abort(404);
                }

                return response()->view($view, [
                    'dashboardPath' => $dashboardPath,
                    'exception' => null,
                    'request' => request(),
                ], 404);
            })->name('not-found');
        });
}
