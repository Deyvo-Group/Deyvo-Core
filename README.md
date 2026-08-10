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

## Maintenance

Use the package maintenance view with Laravel's built-in maintenance mode.

```bash
php artisan down --render="deyvo::maintenance"
```
