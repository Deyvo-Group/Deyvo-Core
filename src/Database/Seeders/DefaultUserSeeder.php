<?php

declare(strict_types=1);

namespace Deyvo\Core\Database\Seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Throwable;

final class DefaultUserSeeder extends Seeder
{
    public function run(): void
    {
        $this->seed();
    }

    public function seed(bool $force = false): ?string
    {
        if (! config('deyvo-core.dashboard.users.enabled', true) || ! config('deyvo-core.dashboard.users.seed.enabled', true)) {
            return null;
        }

        $model = $this->userModel();
        $seed = config('deyvo-core.dashboard.users.seed', []);

        if ($model === null) {
            return null;
        }

        $seed = [
            'name' => 'Dirk',
            'email' => 'dirk@dirkez.nl',
            'password' => '123123123',
            ...(is_array($seed) ? $seed : []),
        ];

        $name = $this->seedString($seed['name'] ?? null) ?? 'Dirk';
        $email = $this->seedString($seed['email'] ?? null);
        $password = $this->seedString($seed['password'] ?? null);

        if ($email === null || filter_var($email, FILTER_VALIDATE_EMAIL) === false || $password === null) {
            return null;
        }

        $user = new $model();
        $table = $user->getTable();

        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'email') || ! Schema::hasColumn($table, 'password')) {
            return null;
        }

        try {
            $existing = $model::query()->where('email', $email)->first();

            if ($existing instanceof Model && ! $force) {
                return 'bestond al';
            }

            $user = $existing instanceof Model ? $existing : new $model();
            $user->setAttribute('email', $email);

            if (Schema::hasColumn($table, 'name')) {
                $user->setAttribute('name', $name);
            }

            $user->setAttribute('password', bcrypt($password));

            if (
                $user->usesTimestamps()
                && (! Schema::hasColumn($table, $user->getCreatedAtColumn()) || ! Schema::hasColumn($table, $user->getUpdatedAtColumn()))
            ) {
                $user->timestamps = false;
            }

            $user->save();
        } catch (Throwable $exception) {
            if (isset($this->command)) {
                $this->command->warn("Standaard dashboarduser kon niet worden seeded: {$exception->getMessage()}");
            }

            return null;
        }

        return $existing instanceof Model ? 'bijgewerkt' : 'aangemaakt';
    }

    private function userModel(): ?string
    {
        $model = config('deyvo-core.dashboard.users.model')
            ?: config('auth.providers.users.model');

        if (! is_string($model) || ! class_exists($model) || ! is_subclass_of($model, Model::class)) {
            return null;
        }

        return $model;
    }

    private function seedString(mixed $value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
