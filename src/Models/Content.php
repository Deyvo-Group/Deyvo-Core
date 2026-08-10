<?php

declare(strict_types=1);

namespace Deyvo\Core\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

final class Content extends Model
{
    protected $table = 'deyvo_contents';

    protected $fillable = [
        'key',
        'title',
        'body',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }
}
