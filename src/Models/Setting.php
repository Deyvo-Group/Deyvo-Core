<?php

declare(strict_types=1);

namespace Deyvo\Core\Models;

use Illuminate\Database\Eloquent\Model;

final class Setting extends Model
{
    protected $table = 'deyvo_settings';

    protected $fillable = [
        'key',
        'label',
        'group',
        'type',
        'value',
        'options',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
        ];
    }

    public function typedValue(mixed $default = null): mixed
    {
        if ($this->value === null) {
            return $default;
        }

        return match ($this->type) {
            'boolean' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $this->value,
            'float' => (float) $this->value,
            'json', 'array' => json_decode($this->value, true) ?? $default,
            default => $this->value,
        };
    }
}
