<?php

declare(strict_types=1);

namespace Deyvo\Core\Http\Controllers\Dashboard;

use Deyvo\Core\Dashboard\DashboardManager;
use Illuminate\Contracts\View\View;

final class LayoutController
{
    public function __construct(
        private DashboardManager $dashboard,
    ) {
    }

    public function index(): View
    {
        return view('deyvo::dashboard.layouts.index', [
            'layouts' => $this->dashboard->layouts(),
        ]);
    }
}
