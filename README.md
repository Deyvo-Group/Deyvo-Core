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

Import the package JavaScript entry after the host imports in the consuming application's Vite entry file. It loads the Core stylesheet after the website stylesheet, so Deyvo dashboard, editor and component styles retain their intended appearance.

```js
import '../../vendor/deyvo/core/resources/js/core.js';
```

Core interface styles are enabled by default. A consuming website can opt out of the additional Deyvo interface styling while keeping the package behavior active.

```php
'ui' => [
    'styles' => [
        'enabled' => false,
    ],
],
```

Use `DEYVO_CORE_STYLES_ENABLED=false` for the same setting through the environment.

## Dashboard Activity

Core uses the authenticated user supplied by the host application. It can manage the configured Laravel user model, but login, roles and permissions stay in the host application. Dashboard changes, page revisions, previews and unexpected dashboard errors are written to `deyvo_audit_logs` with an actor snapshot, request-id, request path and structured context.

Run `php artisan migrate` after updating Core. The dashboard exposes these records through **Activiteit**. Existing revisions remain available and show `Onbekend` until a new change is made.

## Dashboard Debug

Enable the **Debug** dashboard tab with one environment flag.

```env
DEYVO_DEBUG_ENABLED=true
```

The debug screen shows dashboard config, route registration, cache state, Core table counts, page/revision diagnostics, schema status and legacy table samples. Keep it disabled outside local troubleshooting.

## Error Pages

Core renders separate 404 pages for public requests and dashboard requests. Dashboard 404s are detected through the configured dashboard path and are also covered by a dashboard fallback route loaded after the dashboard and page routes.

```env
DEYVO_PUBLIC_404_VIEW=deyvo::errors.404
DEYVO_PUBLIC_404_LAYOUT_VIEW=layout.app
DEYVO_PUBLIC_404_LAYOUT_SECTION=content
DEYVO_DASHBOARD_404_VIEW=deyvo::dashboard.errors.404
```

The public 404 view renders inside `layout.app` when that host layout exists, so the website header and footer stay visible. Set `DEYVO_PUBLIC_404_LAYOUT_VIEW` to another section-based layout, or leave it empty to use Core's fallback layout. Set either 404 view value to a host view name when a website needs full custom presentation.

## Dashboard Gradient

The dashboard keeps a blue Core gradient by default. Override it per website in `config/deyvo-core.php`.

```php
'ui' => [
    'dashboard' => [
        'gradient' => 'linear-gradient(135deg, #eff6ff 0%, #dbeafe 48%, #cffafe 100%)',
    ],
],
```

Use `DEYVO_DASHBOARD_GRADIENT` for the same configuration through the environment. Set `DEYVO_AUDIT_ENABLED=false` only when activity registration must be disabled.

## Account And Logout

Core shows the current host user in the dashboard and editor top bars. It renders a logout button only when the configured named host route exists. Laravel Fortify uses `logout` by default.

```php
'dashboard' => [
    'logout_route' => 'logout',
],
```

Use `DEYVO_DASHBOARD_LOGOUT_ROUTE` when the host application uses another route name. Core does not provide a logout route or authentication logic.

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

The dashboard is available at `/deyvo` and uses the host application's `auth` middleware by default. It manages reusable content, pages, sitewide settings, SEO defaults, media, menus and users without providing authentication itself.

```php
use Deyvo\Core\Support\SiteContent;
use Deyvo\Core\Support\SiteMedia;
use Deyvo\Core\Support\SiteMenus;
use Deyvo\Core\Support\SiteSettings;

$intro = SiteContent::body('homepage.intro');
$email = SiteSettings::get('contact.email');
$header = SiteMenus::get('header');
$image = SiteMedia::url(1);
```

Seed Core's default contact, SEO and menu records when bootstrapping a site.

```bash
php artisan deyvo:seed-cms
```

Import old host-owned CMS tables after running the Core migrations.

```bash
php artisan deyvo:import-legacy-cms
php artisan deyvo:import-legacy-cms --dry-run
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

Each host application can provide a trusted JSON schema that adds dashboard pages, global layout controls, and fields. Deyvo Core reads this file locally; it does not expose a public endpoint that accepts dashboard definitions.

Publish the starter file.

    php artisan vendor:publish --tag=deyvo-dashboard-schema

Set its relative or absolute path in the host application's config/deyvo-core.php.

    'dashboard' => [
        'schema' => [
            'path' => 'resources/deyvo/dashboard.json',
        ],
    ],

The schema supports text, textarea, html, email, url, media, select, and boolean fields. Values use the existing deyvo_settings table by default. Set storage to content for text that should be available through SiteContent.

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

## Header And Footer

Use the schema's `layouts` array to add a dedicated **Layout** area to the dashboard. Each definition becomes a separate editable item, such as Header or Footer. Core stores the values in the existing content and settings tables and records each save in the activity log.

```json
{
  "layouts": [
    {
      "key": "header",
      "label": "Header",
      "description": "Beheer navigatie en de primaire actie.",
      "sort": 10,
      "fields": [
        {
          "key": "layout.header.primary_cta",
          "label": "Primaire knop",
          "type": "text",
          "storage": "content",
          "content_title": "Header primaire knop"
        }
      ]
    },
    {
      "key": "footer",
      "label": "Footer",
      "description": "Beheer de globale footerinhoud.",
      "sort": 20,
      "fields": [
        {
          "key": "layout.footer.brand_intro",
          "label": "Introductie",
          "type": "textarea",
          "storage": "content",
          "content_title": "Footer introductie"
        }
      ]
    }
  ]
}
```

Read published layout content in a host Blade template with the normal Core helper.

```blade
{{ deyvo_content('layout.header.primary_cta', 'Neem contact op') }}
{{ deyvo_content('layout.footer.brand_intro', 'Een heldere introductie.') }}
```

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

The inline editor supports the schema field types text, textarea, email, url, select, and boolean. HTML fields use the dashboard code editor. Media fields use Core media records in the dashboard forms and builder. Edits save concepts through the authenticated dashboard route.

## Media, Menus, SEO And Users

Core owns the dashboard screens, routes, models, migrations and helpers for the CMS domains that used to live in host applications.

```blade
@foreach (deyvo_menu('header') as $item)
    <a href="{{ $item['url'] }}">{{ $item['label'] }}</a>
@endforeach

<img src="{{ deyvo_media_url(deyvo_setting('homepage.hero_image')) }}" alt="">
```

SEO defaults are stored as typed settings and can be read with `deyvo_seo()`.

```blade
@php($seo = deyvo_seo('home'))
<title>{{ $seo['title'] }}</title>
<meta name="robots" content="{{ $seo['robots'] }}">
```

The users screen uses the configured Laravel user model from `DEYVO_USERS_MODEL` or `auth.providers.users.model`. Core still relies on the host application's auth middleware and permission policy.

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

### HTML Code Blocks

The builder includes an optional HTML block. It uses CodeMirror 6 with HTML completion, syntax highlighting, automatic closing tags, line numbers, and Tab indentation. CodeMirror and its HTML package are MIT licensed and the editor module is only loaded when a page form contains an HTML field.

```json
{
  "blocks": [
    {
      "key": "html",
      "label": "HTML",
      "description": "Een veilige HTML-sectie met code-editor.",
      "category": "Aangepast",
      "fields": [
        {
          "key": "html",
          "label": "HTML",
          "type": "html",
          "required": true
        }
      ]
    }
  ],
  "templates": [
    {
      "key": "builder-page",
      "label": "Builderpagina",
      "builder": {
        "enabled": true,
        "blocks": ["html"]
      },
      "sections": []
    }
  ]
}
```

The package starter schema already includes this block. HTML is cleaned on save and again before render. Core permits structural content markup and safe links, while removing scripts, style attributes, event handlers, embeds, forms, SVG, and unsafe URLs. Do not use this block for JavaScript, CSS, tracking snippets, authentication, or media embeds.

Render a page's blocks in its public Blade view.

```blade
<x-deyvo::blocks page="home" />
```

Core includes neutral views for `html`, `hero`, `image`, `text`, `quote`, `call-to-action` and `divider`. Override an individual block in the host application with `resources/views/deyvo-blocks/{block-type}.blade.php`. The view receives a `$block` array with `id`, `type` and `data`.

The package dashboard is a standalone layout. It uses Laravel's standard Vite entrypoints by default, so its Tailwind styles and builder JavaScript load without a published package config. Configure `dashboard.vite` only when the host uses other entrypoints.

```php
'dashboard' => [
    'vite' => [
        'resources/css/admin.css',
        'resources/js/admin.js',
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
