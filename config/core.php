<?php

declare(strict_types=1);

return [
    'name' => env('APP_NAME', 'Deyvo'),

    'version' => env('APP_VERSION', 'dev'),

    'middleware_group' => 'web',

    'features' => [],

    'health' => [
        'enabled' => env('DEYVO_HEALTH_ENABLED', true),
        'path' => '_deyvo/health',
        'middleware' => ['web'],
    ],

    'dashboard' => [
        'enabled' => env('DEYVO_DASHBOARD_ENABLED', false),
        'path' => env('DEYVO_DASHBOARD_PATH', 'deyvo'),
        'middleware' => ['web', 'auth'],
        'schema' => [
            'path' => env('DEYVO_DASHBOARD_SCHEMA_PATH'),
        ],
        'vite' => [],
        'pages' => [
            'enabled' => env('DEYVO_PAGES_ENABLED', false),
        ],
        'navigation' => [
            [
                'label' => 'Overzicht',
                'route' => 'deyvo.dashboard.index',
                'active' => 'deyvo.dashboard.index',
                'sort' => 10,
            ],
            [
                'label' => 'Content',
                'route' => 'deyvo.dashboard.contents.index',
                'active' => 'deyvo.dashboard.contents.*',
                'sort' => 20,
            ],
            [
                'label' => 'Instellingen',
                'route' => 'deyvo.dashboard.settings.index',
                'active' => 'deyvo.dashboard.settings.*',
                'sort' => 30,
            ],
        ],
    ],

    'locale' => [
        'enabled' => env('DEYVO_LOCALE_ENABLED', true),
        'default' => env('APP_LOCALE', 'en'),
        'supported' => [],
        'query_parameter' => 'locale',
        'session_key' => 'deyvo.locale',
    ],

    'timezone' => [
        'enabled' => env('DEYVO_TIMEZONE_ENABLED', true),
        'default' => env('APP_TIMEZONE', 'UTC'),
    ],

    'request_id' => [
        'enabled' => env('DEYVO_REQUEST_ID_ENABLED', true),
        'header' => 'X-Request-ID',
    ],

    'security_headers' => [
        'enabled' => env('DEYVO_SECURITY_HEADERS_ENABLED', true),
        'headers' => [
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'SAMEORIGIN',
            'Permissions-Policy' => 'camera=(), geolocation=(), microphone=()',
        ],
    ],

    'maintenance' => [
        'view' => 'deyvo::maintenance',
    ],
];
