<?php

declare(strict_types=1);

namespace Deyvo\Core\Http\Controllers\Dashboard;

use Deyvo\Core\Models\Page;
use Deyvo\Core\Models\PageRevision;
use Deyvo\Core\Pages\PageManager;
use Deyvo\Core\Pages\PagePreviewState;
use Illuminate\Http\RedirectResponse;

final class PagePreviewController
{
    public function __construct(
        private PageManager $pages,
        private PagePreviewState $preview,
    ) {
    }

    public function __invoke(Page $page): RedirectResponse
    {
        $revision = $this->pages->draft($page);

        abort_unless($revision instanceof PageRevision, 404);

        $this->preview->start($page, $revision);

        return redirect()->to($this->pages->previewUrl($page, $revision));
    }
}
