<?php

declare(strict_types=1);

namespace Deyvo\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Media extends Model
{
    protected $table = 'deyvo_media';

    protected $fillable = [
        'folder_id',
        'name',
        'disk',
        'path',
        'url',
        'mime_type',
        'size',
        'alt',
        'caption',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'size' => 'integer',
        ];
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class, 'folder_id');
    }
}
