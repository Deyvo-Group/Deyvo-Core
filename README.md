# Deyvo Core

Deyvo Core is the shared Laravel foundation for Deyvo packages and applications.

## Installation

```bash
composer require deyvo/core
```

Publish the package configuration when application-level defaults need to change.

```bash
php artisan vendor:publish --tag=deyvo-config
```

Add the package sources to the consuming application's Tailwind CSS entry file.

```css
@source "../../vendor/deyvo/core/resources/views/**/*.blade.php";
@source "../../vendor/deyvo/core/src/**/*.php";
```

Import the package JavaScript entry in the consuming application's Vite entry file when using dismissible alerts or modals.

```js
import '../../vendor/deyvo/core/resources/js/core.js';
```

## Components

```blade
<x-deyvo::button>Save</x-deyvo::button>
<x-deyvo::alert type="success">Saved</x-deyvo::alert>
<x-deyvo::badge variant="success">Active</x-deyvo::badge>
<x-deyvo::card>Content</x-deyvo::card>
<x-deyvo::empty-state title="No items" />
<x-deyvo::form.input name="name" label="Name" />
```

## Flash Messages

```php
use Deyvo\Core\Support\Flash;

Flash::success('Saved');
```

Render the messages with `<x-deyvo::flash />`. The Deyvo layout includes this component automatically.

## Features

```php
'features' => [
    'dashboard' => true,
],
```

```blade
@deyvoFeature('dashboard')
    <span>Dashboard enabled</span>
@enddeyvoFeature
```

## Health Check

The health endpoint is available at `/_deyvo/health` by default and returns `{"status":"ok"}`.

## Dashboard

The dashboard is disabled by default. Enable it and run the package migrations in the consuming application.

```env
DEYVO_DASHBOARD_ENABLED=true
```

```bash
php artisan migrate
```

The dashboard is available at `/deyvo` and uses the host application's `auth` middleware by default. It manages reusable content and sitewide settings without providing authentication itself.

```php
use Deyvo\Core\Support\SiteContent;
use Deyvo\Core\Support\SiteSettings;

$intro = SiteContent::body('homepage.intro');
$email = SiteSettings::get('contact.email');
```

Additional packages can register their own dashboard navigation items from a service provider.

```php
use Deyvo\Core\Dashboard\DashboardManager;

app(DashboardManager::class)->registerNavigation(
    'Reports',
    'reports.index',
    'reports.*',
    40
);
```

## Custom Dashboard Schema

Each host application can provide a trusted JSON schema that adds dashboard pages and fields. Deyvo Core reads this file locally; it does not expose a public endpoint that accepts dashboard definitions.

Publish the starter file.

    php artisan vendor:publish --tag=deyvo-dashboard-schema

Set its relative or absolute path in the host application's config/deyvo-core.php.

    'dashboard' => [
        'schema' => [
            'path' => 'resources/deyvo/dashboard.json',
        ],
    ],

The schema supports text, textarea, email, url, select, and boolean fields. Values use the existing deyvo_settings table by default. Set storage to content for text that should be available through SiteContent.

    {
      "pages": [
        {
          "key": "website",
          "label": "Website",
          "description": "Pas algemene websitegegevens aan.",
          "sort": 40,
          "fields": [
            {
              "key": "contact.email",
              "label": "E-mailadres",
              "type": "email",
              "required": true
            },
            {
              "key": "homepage.intro",
              "label": "Introductie",
              "type": "textarea",
              "storage": "content",
              "content_title": "Homepage introductie"
            },
            {
              "key": "website.status",
              "label": "Status",
              "type": "select",
              "options": [
                {
                  "value": "concept",
                  "label": "Concept"
                },
                {
                  "value": "live",
                  "label": "Live"
                }
              ]
            }
          ]
        }
      ]
    }

For generated JSON, the host application can register the schema during boot.

    use Deyvo\Core\Dashboard\DashboardManager;

    app(DashboardManager::class)->registerSchema(
        (string) file_get_contents(resource_path('deyvo/dashboard.json')),
    );

## Maintenance

Use the package maintenance view with Laravel's built-in maintenance mode.

```bash
php artisan down --render="deyvo::maintenance"
```
