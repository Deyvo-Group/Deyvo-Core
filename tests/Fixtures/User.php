<?php

declare(strict_types=1);

namespace Deyvo\Core\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

final class User extends Model
{
    protected $table = 'users';

    protected $guarded = [];
}
