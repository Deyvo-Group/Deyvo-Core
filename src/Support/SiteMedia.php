<?php

declare(strict_types=1);

namespace Deyvo\Core\Support;

use Deyvo\Core\Models\Media;
use Illuminate\Support\Facades\Storage;
use Throwable;

final class SiteMedia
{
    public static function find(string|int $idOrPath): ?Media
    {
        return Media::query()
            ->whereKey($idOrPath)
            ->orWhere('path', (string) $idOrPath)
            ->orWhere('url', (string) $idOrPath)
            ->first();
    }

    public static function url(string|int|null $idOrPath, ?string $default = null): ?string
    {
        if ($idOrPath === null || $idOrPath === '') {
            return $default;
        }

        $media = self::find($idOrPath);

        if (! $media instanceof Media) {
            return filter_var($idOrPath, FILTER_VALIDATE_URL) ? (string) $idOrPath : $default;
        }

        if (is_string($media->url) && $media->url !== '') {
            return $media->url;
        }

        if (! is_string($media->path) || $media->path === '') {
            return $default;
        }

        try {
            return Storage::disk($media->disk)->url($media->path);
        } catch (Throwable) {
            return $default;
        }
    }
}
