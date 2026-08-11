<?php

declare(strict_types=1);

namespace Deyvo\Core\Http\Controllers\Dashboard;

use Deyvo\Core\Models\Page;
use Deyvo\Core\Pages\PageManager;
use Deyvo\Core\Pages\PagePreviewState;
use Deyvo\Core\Support\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

final class PageFieldController
{
    public function __construct(
        private PageManager $pages,
        private PagePreviewState $preview,
        private AuditLogger $audit,
    ) {
    }

    public function update(Request $request, Page $page): JsonResponse
    {
        $payload = $request->validate([
            'field' => ['required', 'string', 'max:180'],
            'value' => ['nullable'],
        ]);

        try {
            $result = $this->pages->updateField($page, $payload['field'], $payload['value'] ?? null);
        } catch (InvalidArgumentException $exception) {
            $this->audit->record('page.field_update_failed', $page, [
                'page_key' => $page->key,
                'field' => $payload['field'],
                'message' => $exception->getMessage(),
            ]);

            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }

        $this->preview->start($page, $result['revision']);

        return response()->json([
            'field' => $payload['field'],
            'value' => $result['value'],
            'revision' => $result['revision']->version,
        ]);
    }
}
