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

        /*
         |--------------------------------------------------------------------------
         | Locale URL Strategy
         |--------------------------------------------------------------------------
         |
         | prefixed_all:
         |   /en/about
         |   /fa/about
         |
         | default_without_prefix:
         |   /about
         |   /fa/about
         |
         | custom:
         |   rely on localized_urls or localized_routes mappings.
         |
         */
        'url_strategy' => 'custom',
        // custom | prefixed_all | default_without_prefix

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

        /*
         |--------------------------------------------------------------------------
         | Sitemap Base URL
         |--------------------------------------------------------------------------
         |
         | When null, the package uses app.url/url() as before. Set this when
         | sitemap files must use a public host that differs from the current
         | request host.
         |
         | Example:
         | 'base_url' => 'https://sunich.org',
         |
         */
        'base_url' => null,

        /*
         |--------------------------------------------------------------------------
         | Sitemap HTTP Content Type
         |--------------------------------------------------------------------------
         |
         | application/xml is the default XML response type. If a browser or
         | local server renders sitemap XML as plain text instead of an XML
         | tree, you may use:
         |
         | 'content_type' => 'text/xml; charset=UTF-8',
         |
         | This setting affects HTTP sitemap endpoints only. Console command
         | output remains a plain XML string.
         |
         */
        'content_type' => 'application/xml; charset=UTF-8',

        /*
         |--------------------------------------------------------------------------
         | Localized Sitemap Paths
         |--------------------------------------------------------------------------
         |
         | Example:
         | 'localized_paths' => [
         |     'fa' => 'sitemap-fa.xml',
         |     'en' => 'sitemap-en.xml',
         | ],
         */
        'localized_paths' => [],
        'localized_route_enabled' => true,
        'include_localized_in_index' => true,

        /*
         |--------------------------------------------------------------------------
         | Sitemap Hreflang Alternates
         |--------------------------------------------------------------------------
         |
         | false is the safest default because it produces clean sitemap XML.
         | Hreflang alternates are still rendered in HTML <head> output through
         | seo()->alternates() and seo()->render().
         |
         | Set true only when you specifically want xhtml:link hreflang
         | alternates inside sitemap files. Some local browser/server
         | combinations may display sitemap XML as plain text when xhtml
         | alternates are included, even when the XML is valid.
         |
         */
        'include_alternates' => false,

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

    'commerce' => [
        'enabled' => false,

        /*
         |--------------------------------------------------------------------------
         | Offer Generation
         |--------------------------------------------------------------------------
         |
         | auto:
         |   Build Offer only when enough offer data exists, normally price.
         |
         | true:
         |   Build Offer when any offer data exists.
         |
         | false:
         |   Never build Offer.
         |
         */
        'offer' => [
            'enabled' => 'auto',
            'require_price' => true,
        ],

        'default_currency' => null,
        'currency_per_locale' => [],
        'availability_map' => [
            'in_stock' => 'https://schema.org/InStock',
            'out_of_stock' => 'https://schema.org/OutOfStock',
            'preorder' => 'https://schema.org/PreOrder',
            'backorder' => 'https://schema.org/BackOrder',
            'discontinued' => 'https://schema.org/Discontinued',
            'soldout' => 'https://schema.org/SoldOut',
        ],
        'condition_map' => [
            'new' => 'https://schema.org/NewCondition',
            'used' => 'https://schema.org/UsedCondition',
            'refurbished' => 'https://schema.org/RefurbishedCondition',
            'damaged' => 'https://schema.org/DamagedCondition',
        ],
    ],

    'rendering' => [
        'minify' => false,
        'pretty_json' => false,
    ],

    'ui' => [
        'enabled' => false,
        'mode' => 'disabled',
        'route_enabled' => false,
        'path' => 'admin/seo',
        'name' => 'zarbin-seo.ui.',
        'middleware' => ['web', 'auth'],
        'gate' => null,
        'preset' => 'unstyled',
        'database_required' => true,
        'show_preview' => true,
        'routes' => [
            'dashboard' => true,
            'route_overrides' => true,
        ],
        'route' => [
            'enabled' => false,
            'path' => 'admin/seo',
            'name' => 'zarbin-seo.ui.',
            'gate' => null,
        ],
    ],

    'models' => [
        // Model and model-backed holder mappings may be registered by class name.
    ],

    'routes' => [
        // Route-only page mappings may be registered by route name.
        // Add 'locale' => 'fa' or 'locales' => ['fa', 'en'] to scope entries to localized sitemap output.
    ],
];
