<?php

declare(strict_types=1);

use Deyvo\Core\Http\Controllers\Dashboard\ContentController;
use Deyvo\Core\Http\Controllers\Dashboard\CustomPageController;
use Deyvo\Core\Http\Controllers\Dashboard\DashboardController;
use Deyvo\Core\Http\Controllers\Dashboard\SettingController;
use Illuminate\Support\Facades\Route;

Route::prefix(trim((string) config('deyvo-core.dashboard.path', 'deyvo'), '/'))
    ->middleware(config('deyvo-core.dashboard.middleware', ['web', 'auth']))
    ->as('deyvo.dashboard.')
    ->group(function (): void {
        Route::get('/', DashboardController::class)->name('index');

        Route::get('content', [ContentController::class, 'index'])->name('contents.index');
        Route::get('content/create', [ContentController::class, 'create'])->name('contents.create');
        Route::post('content', [ContentController::class, 'store'])->name('contents.store');
        Route::get('content/{content}/edit', [ContentController::class, 'edit'])->name('contents.edit');
        Route::put('content/{content}', [ContentController::class, 'update'])->name('contents.update');
        Route::delete('content/{content}', [ContentController::class, 'destroy'])->name('contents.destroy');

        Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
        Route::get('settings/create', [SettingController::class, 'create'])->name('settings.create');
        Route::post('settings', [SettingController::class, 'store'])->name('settings.store');
        Route::get('settings/{setting}/edit', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings/{setting}', [SettingController::class, 'update'])->name('settings.update');
        Route::delete('settings/{setting}', [SettingController::class, 'destroy'])->name('settings.destroy');

        Route::get('custom/{page}', [CustomPageController::class, 'show'])
            ->where('page', '[a-z0-9][a-z0-9-]*')
            ->name('custom.show');
        Route::put('custom/{page}', [CustomPageController::class, 'update'])
            ->where('page', '[a-z0-9][a-z0-9-]*')
            ->name('custom.update');
    });
