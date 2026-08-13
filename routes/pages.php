<?php

declare(strict_types=1);

use Deyvo\Core\Http\Controllers\Dashboard\PageController;
use Deyvo\Core\Http\Controllers\Dashboard\PageFieldController;
use Deyvo\Core\Http\Controllers\Dashboard\PagePreviewController;
use Deyvo\Core\Http\Middleware\AuditDashboardFailuresMiddleware;
use Illuminate\Support\Facades\Route;

$dashboardPath = trim((string) config('deyvo-core.dashboard.path', 'deyvo'), '/');
$dashboardMiddleware = [...config('deyvo-core.dashboard.middleware', ['web', 'auth']), AuditDashboardFailuresMiddleware::class];

Route::prefix($dashboardPath)
    ->middleware($dashboardMiddleware)
    ->as('deyvo.dashboard.pages.')
    ->group(function (): void {
        Route::get('pages', [PageController::class, 'index'])->name('index');
        Route::get('pages/create', [PageController::class, 'create'])->name('create');
        Route::post('pages', [PageController::class, 'store'])->name('store');
        Route::get('pages/{page}/edit', [PageController::class, 'edit'])->name('edit');
        Route::put('pages/{page}', [PageController::class, 'update'])->name('update');
        Route::post('pages/{page}/publish', [PageController::class, 'publish'])->name('publish');
        Route::get('pages/{page}/preview', PagePreviewController::class)->name('preview');
        Route::post('pages/{page}/preview/stop', [PagePreviewController::class, 'stop'])->name('preview.stop');
        Route::patch('pages/{page}/fields', [PageFieldController::class, 'update'])->name('fields.update');
        Route::get('pages/{page}/revisions', [PageController::class, 'revisions'])->name('revisions');
        Route::post('pages/{page}/revisions/{revision}/restore', [PageController::class, 'restore'])->name('revisions.restore');
    });

if ($dashboardPath !== '') {
    Route::middleware($dashboardMiddleware)
        ->as('deyvo.dashboard.pages.legacy.')
        ->group(function (): void {
            Route::get('pages/{page}/edit', static fn (string $page) => redirect()->route('deyvo.dashboard.pages.edit', ['page' => $page]))
                ->name('edit');
        });
}
