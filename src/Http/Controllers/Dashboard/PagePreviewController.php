<?php

declare(strict_types=1);

namespace Deyvo\Core\Http\Controllers\Dashboard;

use Deyvo\Core\Models\Page;
use Deyvo\Core\Models\PageRevision;
use Deyvo\Core\Pages\PageManager;
use Deyvo\Core\Pages\PagePreviewState;
use Deyvo\Core\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;

final class PagePreviewController
{
    public function __construct(
        private PageManager $pages,
        private PagePreviewState $preview,
        private AuditLogger $audit,
    ) {
    }

    public function __invoke(Page $page): RedirectResponse
    {
        $revision = $this->pages->draft($page);

        abort_unless($revision instanceof PageRevision, 404);

        $this->preview->start($page, $revision);
        $this->audit->record('page.preview_started', $page, [
            'page_key' => $page->key,
            'revision_id' => $revision->getKey(),
            'revision_version' => $revision->version,
        ]);

        return redirect()->to($this->pages->previewUrl($page, $revision));
    }

    public function stop(Page $page): RedirectResponse
    {
        $this->preview->stop();
        $this->audit->record('page.preview_stopped', $page, [
            'page_key' => $page->key,
        ]);
        $revision = $page->publishedRevision;

        if (! $revision instanceof PageRevision) {
            return redirect()->route('deyvo.dashboard.pages.edit', $page);
        }

        return redirect()->to($this->pages->previewUrl($page, $revision));
    }
}
