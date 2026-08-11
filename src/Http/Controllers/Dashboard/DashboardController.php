<?php

declare(strict_types=1);

namespace Deyvo\Core\Http\Controllers\Dashboard;

use Deyvo\Core\Models\Content;
use Deyvo\Core\Models\Page;
use Deyvo\Core\Models\Setting;
use Illuminate\Contracts\View\View;

final class DashboardController
{
    public function __invoke(): View
    {
        return view('deyvo::dashboard.index', [
            'contentCount' => Content::query()->count(),
            'publishedContentCount' => Content::query()->published()->count(),
            'settingCount' => Setting::query()->count(),
            'pageCount' => config('deyvo-core.dashboard.pages.enabled', false) ? Page::query()->count() : 0,
            'publishedPageCount' => config('deyvo-core.dashboard.pages.enabled', false) ? Page::query()->published()->count() : 0,
        ]);
    }
}
