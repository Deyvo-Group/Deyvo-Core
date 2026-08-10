<?php

declare(strict_types=1);

use Deyvo\Core\Http\Controllers\HealthController;
use Illuminate\Support\Facades\Route;

Route::middleware(config('deyvo-core.health.middleware', ['web']))
    ->get(config('deyvo-core.health.path', '_deyvo/health'), HealthController::class)
    ->name('deyvo.health');
