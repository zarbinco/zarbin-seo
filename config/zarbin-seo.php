<?php

declare(strict_types=1);
use Zarbin\Seo\Models\SeoMeta;

return [
    'defaults' => [
        'title' => null,
        'description' => null,
        'image' => null,
        'separator' => ' - ',
        'robots' => 'index,follow',
        'description_limit' => 160,
    ],

    'features' => [
        'open_graph' => true,
        'twitter' => true,
        'schema' => true,
        'sitemap' => true,
        'robots_txt' => true,
        'breadcrumbs' => true,
        'alternate_languages' => true,
        'database_overrides' => false,
        'ui' => false,
        'commerce' => false,
    ],

    'localization' => [
        'enabled' => false,
        'locales' => [],
        'default_locale' => null,
        'route_parameter' => null,
        'missing_translation_strategy' => 'hide',
        'generate_hreflang' => true,
        'x_default' => null,
    ],

    'sitemap' => [
        'enabled' => true,
        'route_enabled' => true,
        'path' => 'sitemap.xml',
        'index_path' => 'sitemap_index.xml',
        'include_alternates' => true,
        'defaults' => [
            'priority' => 0.5,
            'change_frequency' => 'weekly',
        ],
        'cache' => [
            'enabled' => false,
            'ttl' => 3600,
        ],
    ],

    'robots_txt' => [
        'enabled' => true,
        'route_enabled' => true,
        'path' => 'robots.txt',
        'user_agent' => '*',
        'allow' => [],
        'disallow' => [],
        'sitemaps' => [],
    ],

    'database' => [
        'enabled' => false,
        'table' => 'seo_meta',
        'model' => SeoMeta::class,
        'route_type' => 'route',
        'ignore_missing_table' => true,
    ],

    'rendering' => [
        'minify' => false,
        'pretty_json' => false,
    ],

    'ui' => [
        'enabled' => false,
        'mode' => 'disabled',
        'preset' => 'default',
        'middleware' => ['web'],
        'route' => [
            'enabled' => false,
            'path' => 'zarbin-seo',
            'name' => 'zarbin-seo.',
            'gate' => null,
        ],
    ],

    'models' => [
        // Model and model-backed holder mappings may be registered by class name.
    ],

    'routes' => [
        // Route-only page mappings may be registered by route name.
    ],
];
