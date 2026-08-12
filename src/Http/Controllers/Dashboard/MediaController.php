<?php

declare(strict_types=1);

namespace Deyvo\Core\Http\Controllers\Dashboard;

use Deyvo\Core\Models\Folder;
use Deyvo\Core\Models\Media;
use Deyvo\Core\Support\AuditLogger;
use Deyvo\Core\Support\Flash;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Throwable;

final class MediaController
{
    public function __construct(
        private AuditLogger $audit,
    ) {
    }

    public function index(Request $request): View
    {
        $folder = $request->query('folder');

        return view('deyvo::dashboard.media.index', [
            'media' => Media::query()
                ->with('folder')
                ->when($folder, static fn ($query) => $query->where('folder_id', $folder))
                ->latest('updated_at')
                ->paginate(18)
                ->withQueryString(),
            'folders' => Folder::query()->orderBy('path')->get(),
            'currentFolder' => $folder ? Folder::query()->find($folder) : null,
        ]);
    }

    public function create(): View
    {
        return view('deyvo::dashboard.media.create', [
            'folders' => Folder::query()->orderBy('path')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $media = Media::query()->create($this->attributes($request));
        $this->audit->record('media.created', $media, [
            'path' => $media->path,
            'url' => $media->url,
        ]);
        Flash::success('Media-item is opgeslagen.');

        return redirect()->route('deyvo.dashboard.media.edit', $media);
    }

    public function edit(Media $media): View
    {
        return view('deyvo::dashboard.media.edit', [
            'mediaItem' => $media,
            'folders' => Folder::query()->orderBy('path')->get(),
        ]);
    }

    public function update(Request $request, Media $media): RedirectResponse
    {
        $media->fill($this->attributes($request, $media));
        $changes = array_keys($media->getDirty());
        $media->save();

        $this->audit->record('media.updated', $media, [
            'changes' => $changes,
        ]);
        Flash::success('Media-item is bijgewerkt.');

        return redirect()->route('deyvo.dashboard.media.edit', $media);
    }

    public function destroy(Media $media): RedirectResponse
    {
        $path = $media->path;
        $disk = $media->disk;
        $media->delete();

        if (config('deyvo-core.dashboard.media.delete_files', false) && is_string($path) && $path !== '') {
            try {
                Storage::disk($disk)->delete($path);
            } catch (Throwable) {
            }
        }

        $this->audit->record('media.deleted', null, [
            'subject_label' => $media->name,
            'path' => $path,
        ]);
        Flash::success('Media-item is verwijderd.');

        return redirect()->route('deyvo.dashboard.media.index');
    }

    private function attributes(Request $request, ?Media $media = null): array
    {
        $validated = Validator::validate($request->all(), [
            'folder_id' => ['nullable', 'integer', 'exists:deyvo_folders,id'],
            'name' => ['nullable', 'string', 'max:160'],
            'file' => ['nullable', 'file', 'max:10240'],
            'path' => ['nullable', 'string', 'max:500'],
            'url' => ['nullable', 'url', 'max:2048'],
            'alt' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:65535'],
        ]);

        $file = $request->file('file');
        $path = $validated['path'] ?? $media?->path;
        $url = $validated['url'] ?? $media?->url;
        $disk = $media?->disk ?? config('deyvo-core.dashboard.media.disk', 'public');
        $mimeType = $media?->mime_type;
        $size = $media?->size;

        if ($file instanceof UploadedFile) {
            $directory = trim((string) config('deyvo-core.dashboard.media.directory', 'deyvo'), '/');
            $path = $file->store($directory, $disk);
            $url = $this->url($disk, $path);
            $mimeType = $file->getClientMimeType();
            $size = $file->getSize();
        }

        if (! $media instanceof Media && ! is_string($path) && ! is_string($url)) {
            throw ValidationException::withMessages([
                'file' => 'Upload een bestand of vul een URL of pad in.',
            ]);
        }

        $name = $validated['name'] ?? null;

        if ($name === null || trim($name) === '') {
            $name = $file instanceof UploadedFile
                ? pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)
                : basename((string) ($path ?? $url));
        }

        return [
            'folder_id' => $validated['folder_id'] ?? null,
            'name' => $name,
            'disk' => $disk,
            'path' => $path,
            'url' => $url,
            'mime_type' => $mimeType,
            'size' => $size,
            'alt' => $validated['alt'] ?? null,
            'caption' => $validated['caption'] ?? null,
        ];
    }

    private function url(string $disk, string $path): ?string
    {
        try {
            return Storage::disk($disk)->url($path);
        } catch (Throwable) {
            return null;
        }
    }
}
