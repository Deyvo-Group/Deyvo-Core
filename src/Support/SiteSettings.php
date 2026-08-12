<?php

declare(strict_types=1);

namespace Deyvo\Core\Support;

use Deyvo\Core\Models\Setting;

final class SiteSettings
{
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = Setting::query()->where('key', $key)->first();

        return $setting instanceof Setting ? $setting->typedValue($default) : $default;
    }

    public static function put(string $key, mixed $value, array $attributes = []): Setting
    {
        $type = $attributes['type'] ?? self::typeFor($value);

        return Setting::query()->updateOrCreate(
            ['key' => $key],
            [
                'label' => $attributes['label'] ?? null,
                'group' => $attributes['group'] ?? 'Algemeen',
                'type' => $type,
                'value' => self::stringValue($value, $type),
                'options' => $attributes['options'] ?? null,
            ],
        );
    }

    public static function group(string $group): array
    {
        return Setting::query()
            ->where('group', $group)
            ->orderBy('key')
            ->get()
            ->mapWithKeys(static fn (Setting $setting): array => [$setting->key => $setting->typedValue()])
            ->all();
    }

    public static function contact(): array
    {
        return [
            'email' => self::get('contact.email'),
            'phone' => self::get('contact.phone'),
            'address' => self::get('contact.address'),
            'socials' => self::get('contact.socials', []),
        ];
    }

    public static function stringValue(mixed $value, string $type = 'text'): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($type === 'boolean') {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN) ? '1' : '0';
        }

        if (in_array($type, ['json', 'array'], true)) {
            return is_string($value) ? $value : json_encode($value, JSON_THROW_ON_ERROR);
        }

        return is_scalar($value) ? (string) $value : json_encode($value, JSON_THROW_ON_ERROR);
    }

    private static function typeFor(mixed $value): string
    {
        return match (true) {
            is_bool($value) => 'boolean',
            is_int($value) => 'integer',
            is_float($value) => 'float',
            is_array($value) => 'json',
            default => 'text',
        };
    }
}
