<?php

declare(strict_types=1);

namespace Deyvo\Core\Http\Controllers\Dashboard;

use Deyvo\Core\Models\Content;
use Deyvo\Core\Models\AuditLog;
use Deyvo\Core\Models\Media;
use Deyvo\Core\Models\Menu;
use Deyvo\Core\Models\Page;
use Deyvo\Core\Models\Setting;
use Deyvo\Core\Support\Actor;
use Illuminate\Contracts\View\View;

final class DashboardController
{
    public function __construct(
        private Actor $actor,
    ) {
    }

    public function __invoke(): View
    {
        return view('deyvo::dashboard.index', [
            'contentCount' => Content::query()->count(),
            'publishedContentCount' => Content::query()->published()->count(),
            'settingCount' => Setting::query()->count(),
            'pageCount' => config('deyvo-core.dashboard.pages.enabled', false) ? Page::query()->count() : 0,
            'publishedPageCount' => config('deyvo-core.dashboard.pages.enabled', false) ? Page::query()->published()->count() : 0,
            'mediaCount' => config('deyvo-core.dashboard.media.enabled', true) ? Media::query()->count() : 0,
            'menuCount' => config('deyvo-core.dashboard.menus.enabled', true) ? Menu::query()->count() : 0,
            'widgets' => config('deyvo-core.dashboard.widgets', []),
            'actor' => $this->actor->current(),
            'recentActivities' => config('deyvo-core.audit.enabled', true)
                ? AuditLog::query()->latest('created_at')->limit(6)->get()
                : collect(),
        ]);
    }
}
