<?php

declare(strict_types=1);

return [
    'name' => env('APP_NAME', 'Deyvo'),

    'version' => env('APP_VERSION', 'dev'),

    'middleware_group' => 'web',

    'ui' => [
        'styles' => [
            'enabled' => env('DEYVO_CORE_STYLES_ENABLED', true),
        ],
        'dashboard' => [
            'gradient' => env('DEYVO_DASHBOARD_GRADIENT', 'linear-gradient(135deg, #eff6ff 0%, #f8fbff 46%, #e0f2fe 100%)'),
        ],
    ],

    'audit' => [
        'enabled' => env('DEYVO_AUDIT_ENABLED', true),
    ],

    'debug' => [
        'enabled' => env('DEYVO_DEBUG_ENABLED', false),
    ],

    'errors' => [
        'public_404_view' => env('DEYVO_PUBLIC_404_VIEW', 'deyvo::errors.404'),
        'public_404_layout_view' => env('DEYVO_PUBLIC_404_LAYOUT_VIEW', 'layout.app'),
        'public_404_layout_section' => env('DEYVO_PUBLIC_404_LAYOUT_SECTION', 'content'),
        'dashboard_404_view' => env('DEYVO_DASHBOARD_404_VIEW', 'deyvo::dashboard.errors.404'),
    ],

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
        'logout_route' => env('DEYVO_DASHBOARD_LOGOUT_ROUTE', 'logout'),
        'widgets' => [
            'content' => true,
            'settings' => true,
            'pages' => true,
            'media' => true,
            'menus' => true,
            'activity' => true,
        ],
        'schema' => [
            'path' => env('DEYVO_DASHBOARD_SCHEMA_PATH'),
        ],
        'vite' => [
            'resources/css/app.css',
            'resources/js/app.js',
        ],
        'pages' => [
            'enabled' => env('DEYVO_PAGES_ENABLED', false),
        ],
        'media' => [
            'enabled' => env('DEYVO_MEDIA_ENABLED', true),
            'disk' => env('DEYVO_MEDIA_DISK', 'public'),
            'directory' => env('DEYVO_MEDIA_DIRECTORY', 'deyvo'),
            'delete_files' => env('DEYVO_MEDIA_DELETE_FILES', false),
        ],
        'menus' => [
            'enabled' => env('DEYVO_MENUS_ENABLED', true),
        ],
        'seo' => [
            'enabled' => env('DEYVO_SEO_ENABLED', true),
        ],
        'users' => [
            'enabled' => env('DEYVO_USERS_ENABLED', true),
            'model' => env('DEYVO_USERS_MODEL'),
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

    'settings' => [
        'types' => [
            'text',
            'textarea',
            'email',
            'url',
            'boolean',
            'integer',
            'float',
            'json',
        ],
        'defaults' => [
            [
                'key' => 'contact.email',
                'label' => 'E-mailadres',
                'group' => 'Contact',
                'type' => 'email',
                'value' => null,
            ],
            [
                'key' => 'contact.phone',
                'label' => 'Telefoon',
                'group' => 'Contact',
                'type' => 'text',
                'value' => null,
            ],
            [
                'key' => 'contact.socials',
                'label' => 'Social links',
                'group' => 'Contact',
                'type' => 'json',
                'value' => [],
            ],
            [
                'key' => 'seo.title',
                'label' => 'Standaard paginatitel',
                'group' => 'SEO',
                'type' => 'text',
                'value' => null,
            ],
            [
                'key' => 'seo.description',
                'label' => 'Standaard metabeschrijving',
                'group' => 'SEO',
                'type' => 'textarea',
                'value' => null,
            ],
            [
                'key' => 'seo.indexable',
                'label' => 'Indexeer website',
                'group' => 'SEO',
                'type' => 'boolean',
                'value' => true,
            ],
            [
                'key' => 'seo.og_image',
                'label' => 'Social image',
                'group' => 'SEO',
                'type' => 'url',
                'value' => null,
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
