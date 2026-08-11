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
        'blocks',
        'seo',
        'created_by_id',
        'created_by_name',
        'created_by_email',
        'updated_by_id',
        'updated_by_name',
        'updated_by_email',
    ];

    protected function casts(): array
    {
        return [
            'sections' => 'array',
            'blocks' => 'array',
            'seo' => 'array',
        ];
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class, 'page_id');
    }
}
