<?php

declare(strict_types=1);

namespace Deyvo\Core\Http\Controllers\Dashboard;

use Deyvo\Core\Models\Content;
use Deyvo\Core\Support\AuditLogger;
use Deyvo\Core\Support\Flash;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

final class ContentController
{
    public function __construct(
        private AuditLogger $audit,
    ) {
    }

    public function index(): View
    {
        return view('deyvo::dashboard.contents.index', [
            'contents' => Content::query()->latest('updated_at')->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('deyvo::dashboard.contents.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $content = Content::query()->create($this->validated($request));
        $this->audit->record('content.created', $content, [
            'key' => $content->key,
            'published' => $content->is_published,
        ]);
        Flash::success('Content is opgeslagen.');

        return redirect()->route('deyvo.dashboard.contents.index');
    }

    public function edit(Content $content): View
    {
        return view('deyvo::dashboard.contents.edit', [
            'content' => $content,
        ]);
    }

    public function update(Request $request, Content $content): RedirectResponse
    {
        $content->fill($this->validated($request, $content));
        $changes = array_keys($content->getDirty());
        $content->save();
        $this->audit->record('content.updated', $content, [
            'key' => $content->key,
            'changes' => $changes,
            'published' => $content->is_published,
        ]);
        Flash::success('Content is bijgewerkt.');

        return redirect()->route('deyvo.dashboard.contents.index');
    }

    public function destroy(Content $content): RedirectResponse
    {
        $content->delete();
        $this->audit->record('content.deleted', $content, [
            'key' => $content->key,
        ]);
        Flash::success('Content is verwijderd.');

        return redirect()->route('deyvo.dashboard.contents.index');
    }

    private function validated(Request $request, ?Content $content = null): array
    {
        $key = Rule::unique('deyvo_contents', 'key');

        if ($content) {
            $key->ignore($content->getKey());
        }

        $validated = Validator::validate($request->all(), [
            'key' => ['required', 'string', 'max:120', 'regex:/^[a-z0-9][a-z0-9._-]*$/', $key],
            'title' => ['required', 'string', 'max:160'],
            'body' => ['nullable', 'string', 'max:65535'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $validated['is_published'] = $request->boolean('is_published');

        return $validated;
    }
}
