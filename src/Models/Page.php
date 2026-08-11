<?php

declare(strict_types=1);

namespace Deyvo\Core\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Page extends Model
{
    protected $table = 'deyvo_pages';

    protected $fillable = [
        'key',
        'published_slug',
        'published_revision_id',
        'draft_revision_id',
    ];

    public function revisions(): HasMany
    {
        return $this->hasMany(PageRevision::class, 'page_id');
    }

    public function publishedRevision(): BelongsTo
    {
        return $this->belongsTo(PageRevision::class, 'published_revision_id');
    }

    public function draftRevision(): BelongsTo
    {
        return $this->belongsTo(PageRevision::class, 'draft_revision_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_revision_id');
    }
}
