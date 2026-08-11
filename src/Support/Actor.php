<?php

declare(strict_types=1);

namespace Deyvo\Core\Support;

final class Actor
{
    public function current(): array
    {
        if (! app()->bound('auth')) {
            return $this->empty();
        }

        $guard = app('auth');

        if (! is_object($guard)) {
            return $this->empty();
        }

        try {
            $user = $guard->user();
        } catch (\Throwable) {
            return $this->empty();
        }

        if (! is_object($user)) {
            return $this->empty();
        }

        $id = method_exists($user, 'getAuthIdentifier') ? $user->getAuthIdentifier() : null;
        $name = $this->value($user, 'name');
        $email = $this->value($user, 'email');

        return [
            'id' => is_scalar($id) ? (string) $id : null,
            'name' => $name,
            'email' => $email,
        ];
    }

    public function attributes(string $prefix): array
    {
        $actor = $this->current();

        return [
            $prefix.'_id' => $actor['id'],
            $prefix.'_name' => $actor['name'],
            $prefix.'_email' => $actor['email'],
        ];
    }

    public function label(): string
    {
        $actor = $this->current();

        return $actor['name'] ?? $actor['email'] ?? 'Onbekende gebruiker';
    }

    private function empty(): array
    {
        return [
            'id' => null,
            'name' => null,
            'email' => null,
        ];
    }

    private function value(object $user, string $attribute): ?string
    {
        $value = method_exists($user, 'getAttribute')
            ? $user->getAttribute($attribute)
            : ($user->{$attribute} ?? null);

        return is_scalar($value) && (string) $value !== '' ? mb_substr((string) $value, 0, $attribute === 'email' ? 255 : 160) : null;
    }
}
