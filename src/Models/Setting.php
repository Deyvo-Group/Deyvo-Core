<?php

declare(strict_types=1);

namespace Deyvo\Core\Models;

use Illuminate\Database\Eloquent\Model;

final class Setting extends Model
{
    protected $table = 'deyvo_settings';

    protected $fillable = [
        'key',
        'value',
    ];
}
