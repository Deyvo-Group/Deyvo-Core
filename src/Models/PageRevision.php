<?php

declare(strict_types=1);

namespace Deyvo\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PageRevision extends Model
{
    protected $table = 'deyvo_page_revisions';

    protected $fillable = [
        'page_id',
        'version',
        'title',
        'slug',
        'template',
        'sections',
        'seo',
    ];

    protected function casts(): array
    {
        return [
            'sections' => 'array',
            'seo' => 'array',
        ];
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class, 'page_id');
    }
}
