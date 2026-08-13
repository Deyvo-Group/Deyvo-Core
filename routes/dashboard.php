<?php

declare(strict_types=1);

use Deyvo\Core\Http\Controllers\Dashboard\ContentController;
use Deyvo\Core\Http\Controllers\Dashboard\ActivityLogController;
use Deyvo\Core\Http\Controllers\Dashboard\CustomPageController;
use Deyvo\Core\Http\Controllers\Dashboard\DashboardController;
use Deyvo\Core\Http\Controllers\Dashboard\DebugController;
use Deyvo\Core\Http\Controllers\Dashboard\FolderController;
use Deyvo\Core\Http\Controllers\Dashboard\LayoutController;
use Deyvo\Core\Http\Controllers\Dashboard\MediaController;
use Deyvo\Core\Http\Controllers\Dashboard\MenuController;
use Deyvo\Core\Http\Controllers\Dashboard\SeoController;
use Deyvo\Core\Http\Controllers\Dashboard\SettingController;
use Deyvo\Core\Http\Controllers\Dashboard\UserController;
use Deyvo\Core\Http\Middleware\AuditDashboardFailuresMiddleware;
use Illuminate\Support\Facades\Route;

Route::prefix(trim((string) config('deyvo-core.dashboard.path', 'deyvo'), '/'))
    ->middleware([...config('deyvo-core.dashboard.middleware', ['web', 'auth']), AuditDashboardFailuresMiddleware::class])
    ->as('deyvo.dashboard.')
    ->group(function (): void {
        Route::get('/', DashboardController::class)->name('index');
        Route::get('debug', DebugController::class)->name('debug.index');

        Route::get('activity', [ActivityLogController::class, 'index'])->name('activity.index');
        Route::get('activity/{activity}', [ActivityLogController::class, 'show'])->name('activity.show');

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

        if (config('deyvo-core.dashboard.media.enabled', true)) {
            Route::get('media', [MediaController::class, 'index'])->name('media.index');
            Route::get('media/create', [MediaController::class, 'create'])->name('media.create');
            Route::post('media', [MediaController::class, 'store'])->name('media.store');
            Route::get('media/{media}/edit', [MediaController::class, 'edit'])->name('media.edit');
            Route::put('media/{media}', [MediaController::class, 'update'])->name('media.update');
            Route::delete('media/{media}', [MediaController::class, 'destroy'])->name('media.destroy');
            Route::post('media/folders', [FolderController::class, 'store'])->name('media.folders.store');
            Route::delete('media/folders/{folder}', [FolderController::class, 'destroy'])->name('media.folders.destroy');
        }

        if (config('deyvo-core.dashboard.menus.enabled', true)) {
            Route::get('menus', [MenuController::class, 'index'])->name('menus.index');
            Route::get('menus/create', [MenuController::class, 'create'])->name('menus.create');
            Route::post('menus', [MenuController::class, 'store'])->name('menus.store');
            Route::get('menus/{menu}/edit', [MenuController::class, 'edit'])->name('menus.edit');
            Route::put('menus/{menu}', [MenuController::class, 'update'])->name('menus.update');
            Route::delete('menus/{menu}', [MenuController::class, 'destroy'])->name('menus.destroy');
        }

        if (config('deyvo-core.dashboard.seo.enabled', true)) {
            Route::get('seo', [SeoController::class, 'index'])->name('seo.index');
            Route::put('seo', [SeoController::class, 'update'])->name('seo.update');
        }

        if (config('deyvo-core.dashboard.users.enabled', true)) {
            Route::get('users', [UserController::class, 'index'])->name('users.index');
            Route::get('users/create', [UserController::class, 'create'])->name('users.create');
            Route::post('users', [UserController::class, 'store'])->name('users.store');
            Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
            Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
            Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        }

        Route::get('layout', [LayoutController::class, 'index'])->name('layouts.index');
        Route::get('layout/{layout}', [CustomPageController::class, 'showLayout'])
            ->where('layout', '[a-z0-9][a-z0-9-]*')
            ->name('layouts.show');
        Route::put('layout/{layout}', [CustomPageController::class, 'updateLayout'])
            ->where('layout', '[a-z0-9][a-z0-9-]*')
            ->name('layouts.update');

        Route::get('custom/{page}', [CustomPageController::class, 'show'])
            ->where('page', '[a-z0-9][a-z0-9-]*')
            ->name('custom.show');
        Route::put('custom/{page}', [CustomPageController::class, 'update'])
            ->where('page', '[a-z0-9][a-z0-9-]*')
            ->name('custom.update');
    });
