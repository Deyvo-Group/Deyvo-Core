<?php

declare(strict_types=1);

namespace Deyvo\Core\Pages;

use Deyvo\Core\Models\Page;
use Deyvo\Core\Models\PageRevision;

final class PagePreviewState
{
    private const SessionKey = 'deyvo.page_preview';

    private array $revisions = [];

    public function start(Page $page, PageRevision $revision): void
    {
        request()->session()->put(self::SessionKey, [
            'page_id' => $page->getKey(),
            'page_key' => $page->key,
            'revision_id' => $revision->getKey(),
        ]);
        $this->revisions = [];
    }

    public function stop(): void
    {
        request()->session()->forget(self::SessionKey);
        $this->revisions = [];
    }

    public function revision(string $pageKey): ?PageRevision
    {
        $state = request()->session()->get(self::SessionKey);

        if (! is_array($state) || ($state['page_key'] ?? null) !== $pageKey) {
            return null;
        }

        if (isset($this->revisions[$pageKey])) {
            return $this->revisions[$pageKey];
        }

        $page = Page::query()
            ->whereKey($state['page_id'] ?? null)
            ->where('key', $pageKey)
            ->first();

        if (! $page instanceof Page) {
            return null;
        }

        $revision = PageRevision::query()
            ->whereKey($state['revision_id'] ?? null)
            ->where('page_id', $page->getKey())
            ->first();

        return $revision instanceof PageRevision ? $this->revisions[$pageKey] = $revision : null;
    }
}
