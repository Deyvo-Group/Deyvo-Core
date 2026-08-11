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

## Page Editor

Enable the page editor in the host configuration and run the package migrations.

    'dashboard' => [
        'pages' => [
            'enabled' => true,
        ],
    ],

    php artisan migrate

Add page templates to the same JSON schema. Each template has sections and typed fields. Pages preserve every saved version as a revision. A saved draft is never visible to visitors until it is published.

    {
      "pages": [],
      "templates": [
        {
          "key": "landing",
          "label": "Landingspagina",
          "sort": 10,
          "sections": [
            {
              "key": "hero",
              "label": "Hero",
              "fields": [
                {
                  "key": "title",
                  "label": "Titel",
                  "type": "text",
                  "required": true
                },
                {
                  "key": "intro",
                  "label": "Introductie",
                  "type": "textarea"
                }
              ]
            }
          ]
        }
      ]
    }

The dashboard preview redirects to the real website. Register a resolver when the host route does not map directly to the page slug.

    use Deyvo\Core\Models\Page;
    use Deyvo\Core\Models\PageRevision;
    use Deyvo\Core\Pages\PageManager;

    app(PageManager::class)->registerPreviewUrlResolver(
        static fn (Page $page, PageRevision $revision): string => route('pages.show', $revision->slug),
    );

Use deyvo_content in public Blade views for a published page value with a fallback.

    <p>{{ deyvo_content('home.hero.intro', 'Bestaande introductie') }}</p>

Use deyvoEditable for inline preview markers. Visitors receive only the escaped published value. A dashboard preview receives an escaped field marker with edit metadata.

    <h1>@deyvoEditable('home.hero.title')</h1>

Add deyvoEditor once near the end of the host layout and include the Core JavaScript entry. The host layout needs a standard CSRF meta tag.

    <meta name="csrf-token" content="{{ csrf_token() }}">
    @deyvoEditor

When a dashboard preview is active, `@deyvoEditor` renders a fixed overlay with the page title, path, draft version, save state and actions for the dashboard or leaving editor mode. Visitors never receive the overlay.

The inline editor supports the schema field types text, textarea, email, url, select, and boolean. It saves concepts through the authenticated dashboard route. Rich text, media, menus, and legacy-data import are intentionally separate modules or migration work.

## Block Builder

The page editor also supports a WordPress/Gutenberg-style block builder. Add block definitions and an enabled builder to the dashboard schema. A builder template may use an empty sections array.

```json
{
  "pages": [],
  "blocks": [
    {
      "key": "hero",
      "label": "Hero",
      "category": "Introduction",
      "fields": [
        {
          "key": "heading",
          "label": "Heading",
          "type": "text",
          "required": true
        }
      ]
    }
  ],
  "templates": [
    {
      "key": "builder-page",
      "label": "Builder page",
      "builder": {
        "enabled": true,
        "blocks": ["hero"]
      },
      "sections": []
    }
  ]
}
```

The editor supports inserting, selecting, reordering, duplicating, removing and configuring blocks. All block data is stored on the page revision, so it follows the existing draft, preview, publish and restore workflow.

Render a page's blocks in its public Blade view.

```blade
<x-deyvo::blocks page="home" />
```

Core includes neutral views for `hero`, `text`, `quote`, `call-to-action` and `divider`. Override an individual block in the host application with `resources/views/deyvo-blocks/{block-type}.blade.php`. The view receives a `$block` array with `id`, `type` and `data`.

The package dashboard is a standalone layout. Give it the host Vite entrypoints so its Tailwind styles and builder JavaScript are loaded.

```php
'dashboard' => [
    'vite' => [
        'resources/css/app.css',
        'resources/js/app.js',
    ],
],
```

```js
import '../../vendor/deyvo/core/resources/js/core.js';
```

## Maintenance

Use the package maintenance view with Laravel's built-in maintenance mode.

```bash
php artisan down --render="deyvo::maintenance"
```
