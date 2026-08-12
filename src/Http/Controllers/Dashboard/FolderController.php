<?php

declare(strict_types=1);

namespace Deyvo\Core\Http\Controllers\Dashboard;

use Deyvo\Core\Models\Folder;
use Deyvo\Core\Support\AuditLogger;
use Deyvo\Core\Support\Flash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class FolderController
{
    public function __construct(
        private AuditLogger $audit,
    ) {
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = Validator::validate($request->all(), [
            'name' => ['required', 'string', 'max:160'],
            'parent_id' => ['nullable', 'integer', 'exists:deyvo_folders,id'],
        ]);

        $parent = isset($validated['parent_id']) ? Folder::query()->find($validated['parent_id']) : null;
        $slug = Str::slug($validated['name']);
        $path = ltrim(($parent?->path ? $parent->path.'/' : '').$slug, '/');

        if (Folder::query()->where('path', $path)->exists()) {
            throw ValidationException::withMessages([
                'name' => 'Er bestaat al een map met dit pad.',
            ]);
        }

        $folder = Folder::query()->create([
            'parent_id' => $parent?->getKey(),
            'name' => $validated['name'],
            'slug' => $slug,
            'path' => $path,
        ]);

        $this->audit->record('folder.created', $folder, [
            'path' => $folder->path,
        ]);
        Flash::success('Map is aangemaakt.');

        return redirect()->route('deyvo.dashboard.media.index', ['folder' => $folder->getKey()]);
    }

    public function destroy(Folder $folder): RedirectResponse
    {
        if ($folder->children()->exists() || $folder->media()->exists()) {
            throw ValidationException::withMessages([
                'folder' => 'Alleen lege mappen kunnen worden verwijderd.',
            ]);
        }

        $path = $folder->path;
        $folder->delete();

        $this->audit->record('folder.deleted', null, [
            'subject_label' => $path,
            'path' => $path,
        ]);
        Flash::success('Map is verwijderd.');

        return redirect()->route('deyvo.dashboard.media.index');
    }
}
