<?php

declare(strict_types=1);

return [
    'navigation' => [
        'dashboard' => 'Dashboard',
        'routes' => 'Routes',
        'models' => 'Models',
        'back_to_routes' => 'Back to routes',
        'back_to_models' => 'Back to models',
        'aria' => 'SEO navigation',
    ],

    'layout' => [
        'standalone_title' => 'Zarbin SEO',
        'host_missing' => 'Host layout is not available. Falling back to the package layout.',
    ],

    'direction' => [
        'rtl' => 'Right to left',
        'ltr' => 'Left to right',
    ],

    'dashboard' => [
        'title' => 'Zarbin SEO',
        'description' => 'Manage SEO readiness and route overrides.',
        'status' => 'Status',
        'database_ready' => 'Database ready',
        'database_not_ready' => 'Database is not ready',
        'routes_total' => 'Routes',
        'routes_complete' => 'Complete',
        'routes_incomplete' => 'Incomplete',
        'models_total' => 'Models',
        'models_complete' => 'Complete',
        'models_incomplete' => 'Incomplete',
        'route_overrides' => 'Route Overrides',
        'route_overrides_description' => 'Edit manual SEO overrides for configured route-only pages.',
        'manage_route_overrides' => 'Manage route overrides',
        'model_overrides' => 'Model Overrides',
        'model_overrides_description' => 'Edit manual SEO overrides for explicitly configured model inventory items.',
        'manage_model_overrides' => 'Manage model overrides',
        'yes' => 'Yes',
        'no' => 'No',
        'status_items' => [
            'ui_enabled' => 'UI enabled',
            'database_overrides_enabled' => 'Database overrides enabled',
            'table_exists' => 'SEO table exists',
            'sitemap_enabled' => 'Sitemap enabled',
            'robots_enabled' => 'Robots.txt enabled',
            'localization_enabled' => 'Localization enabled',
        ],
    ],

    'routes' => [
        'title' => 'SEO Routes',
        'edit_title' => 'Edit SEO Route',
        'description' => 'Configured route-only SEO pages.',
        'empty' => 'No SEO routes are configured.',
        'key' => 'Key',
        'label' => 'Label',
        'locale' => 'Locale',
        'status' => 'Status',
        'missing' => 'Missing',
        'warnings' => 'Warnings',
        'actions' => 'Actions',
        'edit' => 'Edit',
        'edit_unavailable' => 'Edit route unavailable',
        'complete' => 'Complete',
        'incomplete' => 'Incomplete',
    ],

    'models' => [
        'title' => 'SEO Models',
        'edit_title' => 'Edit SEO Model',
        'description' => 'Explicitly configured model and holder SEO inventory.',
        'empty' => 'No model SEO items are configured.',
        'disabled' => 'Model inventory is disabled. Enable ui.inventory.models.enabled and configure explicit model UI sources to list model items.',
        'class' => 'Model',
        'item' => 'Item',
        'key' => 'Key',
        'edit' => 'Edit',
        'not_found' => 'The requested model SEO item was not found.',
    ],

    'form' => [
        'legend' => 'SEO Override',
        'save' => 'Save SEO',
        'save_override' => 'Save override',
        'delete' => 'Delete override',
        'reset' => 'Reset',
        'database_warning' => 'Database overrides are not ready. Saving is disabled.',
        'database_preview_warning' => 'Database overrides are not ready. The form is shown for preview, but saving is disabled.',
        'database_setup_warning' => 'SEO database overrides are not ready. Publish and run the migration, then enable database overrides.',
        'saved' => 'SEO override saved.',
        'deleted' => 'SEO override deleted.',
        'not_saved' => 'SEO override could not be saved.',
        'not_deleted' => 'SEO override could not be deleted.',
        'model_saved' => 'Model SEO override saved.',
        'model_deleted' => 'Model SEO override deleted.',
        'model_not_saved' => 'Model SEO override could not be saved.',
        'model_not_deleted' => 'Model SEO override could not be deleted.',
        'validation_errors' => 'There were validation errors.',
    ],

    'fields' => [
        'title' => [
            'label' => 'SEO Title',
            'hint' => 'Used for the page title and search result title.',
        ],
        'description' => [
            'label' => 'Meta Description',
            'hint' => 'A concise description for search results.',
        ],
        'canonical' => [
            'label' => 'Canonical URL',
            'hint' => 'The preferred URL for this page.',
        ],
        'robots' => [
            'label' => 'Robots',
            'hint' => 'Choose how search engines should index and follow links.',
        ],
        'image' => [
            'label' => 'Image URL',
            'hint' => 'Used for social sharing previews.',
        ],
        'og_title' => [
            'label' => 'Open Graph Title',
            'hint' => 'Optional social sharing title.',
        ],
        'og_description' => [
            'label' => 'Open Graph Description',
            'hint' => 'Optional social sharing description.',
        ],
        'og_image' => [
            'label' => 'Open Graph Image',
            'hint' => 'Optional social sharing image.',
        ],
        'twitter_title' => [
            'label' => 'Twitter/X Title',
            'hint' => 'Optional Twitter/X card title.',
        ],
        'twitter_description' => [
            'label' => 'Twitter/X Description',
            'hint' => 'Optional Twitter/X card description.',
        ],
        'twitter_image' => [
            'label' => 'Twitter/X Image',
            'hint' => 'Optional Twitter/X card image.',
        ],
        'schema_type' => [
            'label' => 'Schema Type',
            'hint' => 'Example: WebPage, CollectionPage, Product, Article.',
        ],
        'extra' => [
            'label' => 'Extra JSON',
            'hint' => 'Advanced JSON metadata.',
        ],
    ],

    'preview' => [
        'title' => 'Preview',
        'search_result' => 'Search result preview',
        'raw_html' => 'Raw HTML preview',
        'no_title' => 'Untitled page',
        'no_description' => 'No meta description available.',
        'no_url' => 'No canonical URL available.',
        'approximation' => 'Visual approximation only. Actual search snippets may differ.',
        'warnings' => [
            'missing_title' => 'Missing SEO title.',
            'missing_url' => 'Missing canonical URL.',
            'missing_description' => 'Missing meta description.',
            'long_title' => 'SEO title may be too long.',
            'long_description' => 'Meta description may be too long.',
        ],
    ],

    'status' => [
        'complete_symbol' => '✓',
        'incomplete_symbol' => '×',
    ],
];
