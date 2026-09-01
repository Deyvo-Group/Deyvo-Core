<?php

declare(strict_types=1);

namespace Deyvo\Core\Console\Commands;

use Deyvo\Core\Database\Seeders\DefaultUserSeeder;
use Deyvo\Core\Models\Menu;
use Deyvo\Core\Support\SiteSettings;
use Illuminate\Console\Command;

final class SeedCmsCommand extends Command
{
    protected $signature = 'deyvo:seed-cms {--force : Update existing default values as well}';

    protected $description = 'Seed Deyvo Core CMS defaults for settings, SEO, menus and the default dashboard user.';

    public function handle(): int
    {
        $force = (bool) $this->option('force');
        $settings = config('deyvo-core.settings.defaults', []);
        $count = 0;

        if (is_array($settings)) {
            foreach ($settings as $setting) {
                if (! is_array($setting) || ! isset($setting['key'])) {
                    continue;
                }

                if (! $force && SiteSettings::get((string) $setting['key']) !== null) {
                    continue;
                }

                SiteSettings::put((string) $setting['key'], $setting['value'] ?? null, [
                    'label' => $setting['label'] ?? null,
                    'group' => $setting['group'] ?? 'Algemeen',
                    'type' => $setting['type'] ?? 'text',
                    'options' => $setting['options'] ?? null,
                ]);
                $count++;
            }
        }

        foreach (['header' => 'Hoofdmenu', 'footer' => 'Footermenu'] as $key => $title) {
            if (! $force && Menu::query()->where('key', $key)->exists()) {
                continue;
            }

            Menu::query()->updateOrCreate(
                ['key' => $key],
                [
                    'title' => $title,
                    'items' => [],
                    'is_active' => true,
                ],
            );
        }

        $userSeeder = new DefaultUserSeeder();
        $userSeeder->setCommand($this);
        $userStatus = $userSeeder->seed($force);

        $this->components->info("Deyvo Core CMS defaults seeded ({$count} settings).");

        if ($userStatus !== null) {
            $this->components->info("Standaard dashboarduser {$userStatus}.");
        }

        return self::SUCCESS;
    }
}
