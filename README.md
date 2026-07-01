# Zarbin SEO

<p align="center">
  <strong>Documentation:</strong>
  <a href="README.md">English</a>
  ·
  <a href="docs/fa/README.md">فارسی</a>
</p>

Zarbin SEO is a lightweight, Laravel-native SEO toolkit for model-aware, route-aware, and multilingual metadata workflows.

It is inspired by common SEO editorial workflows and concepts popularized by tools such as Yoast SEO, but it is not a WordPress clone and is not affiliated with Yoast.

## Development Status

Pre-release / v0.1.x. The package is ready for early testing, but the public API may still evolve before a stable release.

## Documentation

- English: [README.md](README.md)
- فارسی: [docs/fa/README.md](docs/fa/README.md)

## Features

- Fluent SEO manager available through `seo()` and the `ZarbinSeo` facade.
- Immutable-ish `SeoData` data object.
- Model, holder, route, array, and default SEO source resolution.
- Blade rendering for title, meta description, canonical, robots, Open Graph, Twitter/X cards, hreflang, and JSON-LD.
- Multilingual SEO with alternate language URLs, `x-default`, and missing translation strategies.
- XML sitemap, sitemap index, and robots.txt generation.
- Optional database overrides for manual SEO values.
- Optional plain Blade UI and embeddable SEO form component.
- Product and commerce schema support without ecommerce package dependencies.
- Artisan commands for install, doctor/readiness checks, source inspection, sitemap export, and robots.txt export.

## Requirements

- PHP `^8.2`
- Laravel/Illuminate `^10.0`, `^11.0`, `^12.0`, or `^13.0`

## Installation

```bash
composer require zarbinco/zarbin-seo
```

## Publish Config

```bash
php artisan vendor:publish --tag=zarbin-seo-config
```

Optional resources:

```bash
php artisan vendor:publish --tag=zarbin-seo-migrations
php artisan vendor:publish --tag=zarbin-seo-views
```

The migration is only needed when you enable database-backed manual overrides. Views are only needed when you want to customize the optional Blade UI or form components.

## Quick Start

Render the current request SEO tags in your layout:

```blade
{!! seo()->render() !!}
```

Resolve SEO data in a controller:

```php
public function show(Post $post)
{
    seo()->for($post);

    return view('posts.show', compact('post'));
}
```

Or compose SEO data directly:

```php
seo()
    ->title('About Us')
    ->description('Learn more about our company.')
    ->canonical(route('about'))
    ->render();
```

## Model-Aware SEO

Models can implement `Seoable` and use `HasSeo`:

```php
use Illuminate\Database\Eloquent\Model;
use Zarbin\Seo\Concerns\HasSeo;
use Zarbin\Seo\Contracts\Seoable;

class Post extends Model implements Seoable
{
    use HasSeo;

    public function seoTitle(?string $locale = null): ?string
    {
        return $this->title;
    }

    public function seoDescription(?string $locale = null): ?string
    {
        return $this->excerpt;
    }

    public function seoCanonicalUrl(?string $locale = null): ?string
    {
        return route('posts.show', $this);
    }
}
```

You can also configure mappings for normal Laravel or Eloquent-like models:

```php
'models' => [
    App\Models\Post::class => [
        'title' => 'title',
        'description' => ['excerpt', 'content'],
        'image' => 'cover_image_url',
        'route' => 'posts.show',
        'route_key' => 'slug',
        'type' => 'Article',
    ],
],
```

`seo()->for($post)->get()` resolves fallback defaults, configured mappings, `Seoable` values, multilingual state, optional database overrides, and optional commerce data.

## Holder Pages

Model-backed holder pages such as `ProductHolder`, `BlogHolder`, or `HomePage` work like any other `Seoable` model:

```php
class ProductHolder extends Model implements Seoable
{
    use HasSeo;

    public function seoTitle(?string $locale = null): ?string
    {
        return $this->title ?: 'Products';
    }

    public function seoType(?string $locale = null): ?string
    {
        return 'CollectionPage';
    }
}
```

Route-only holder pages can use route mappings instead.

## Route-Only SEO

Configure SEO data for pages that do not have a model:

```php
'routes' => [
    'home' => [
        'title' => 'Home',
        'description' => 'Welcome to our website.',
        'canonical' => 'https://example.com',
        'schema' => 'WebPage',
        'sitemap' => true,
    ],
],
```

Use it in controllers, layouts, or middleware:

```php
seo()->route('home')->render();
```

## Rendering

Full render:

```blade
{!! seo()->render() !!}
```

Segmented render methods:

```blade
{!! seo()->meta() !!}
{!! seo()->openGraph() !!}
{!! seo()->twitter() !!}
{!! seo()->jsonLd() !!}
{!! seo()->alternates() !!}
```

Blade component:

```blade
<x-zarbin-seo::meta />
<x-zarbin-seo::meta :source="$post" locale="fa" />
```

## Multilingual SEO

Enable localization and configure supported locales:

```php
'localization' => [
    'enabled' => true,
    'locales' => ['fa', 'en'],
    'default_locale' => 'fa',
    'url_strategy' => 'prefixed_all',
    'route_parameter' => 'locale',
    'missing_translation_strategy' => 'hide',
    'generate_hreflang' => true,
    'x_default' => 'fa',
],
```

Locale URL strategies keep generated route URLs predictable:

- `default_without_prefix`: use URLs like `/about` for the default locale and `/fa/about` for other locales.
- `prefixed_all`: use URLs like `/en/about` and `/fa/about`.
- `custom`: do not infer prefixes; use `localized_urls`, `localized_routes`, model methods, or canonical URLs for special projects.

Models can implement `LocalizableSeo` when they know which languages exist:

```php
use Zarbin\Seo\Contracts\LocalizableSeo;

class Post extends Model implements Seoable, LocalizableSeo
{
    use HasSeo;

    public function seoLocales(): array
    {
        return ['fa', 'en'];
    }

    public function hasSeoLocale(string $locale): bool
    {
        return filled($this->getTranslation('title', $locale));
    }

    public function seoUrlForLocale(string $locale): ?string
    {
        return route('posts.show', ['locale' => $locale, 'post' => $this->slug]);
    }
}
```

Missing translation strategies:

- `hide`: mark the current SEO data as unavailable in `extra` and skip unavailable alternates.
- `fallback`: use fallback URLs where possible and mark fallback usage in `extra`.
- `noindex`: add `noindex` to robots for unavailable current locale content.

When alternates are available, Zarbin SEO renders:

```html
<link rel="alternate" hreflang="fa" href="https://example.com/fa/posts/hello">
<link rel="alternate" hreflang="en" href="https://example.com/en/posts/hello">
<link rel="alternate" hreflang="x-default" href="https://example.com/fa/posts/hello">
```

## Sitemap And Robots.txt

Public package routes are registered by default:

```text
/sitemap.xml
/sitemap_index.xml
/robots.txt
```

Projects that publish separate sitemap files per language can configure localized sitemap paths:

```php
'localization' => [
    'enabled' => true,
    'locales' => ['fa', 'en'],
    'default_locale' => 'fa',
    'url_strategy' => 'prefixed_all',
    'route_parameter' => 'locale',
],

'sitemap' => [
    'base_url' => 'https://example.com',
    'content_type' => 'application/xml; charset=UTF-8',
    'localized_paths' => [
        'fa' => 'sitemap-fa.xml',
        'en' => 'sitemap-en.xml',
    ],
    'include_alternates' => false,
],
```

With that config, `/sitemap-fa.xml` renders `fa` URLs, `/sitemap-en.xml` renders `en` URLs, and `/sitemap_index.xml` lists both localized sitemap files. Localized sitemap routes return XML responses with `application/xml` content type. `localized_paths` controls the sitemap file paths. `sitemap.base_url` controls the host used for sitemap index URLs and robots.txt auto sitemap links, which is useful when the current request host differs from the public site host.

Sitemap hreflang alternates are disabled by default for cleaner sitemap XML output. Hreflang in the HTML `<head>` remains supported through normal rendering with `seo()->alternates()` and `seo()->render()`.

Enable sitemap alternates only when you specifically want hreflang inside sitemap files:

```php
'sitemap' => [
    'include_alternates' => true,
],
```

When enabled, sitemap hreflang alternates are rendered as XML-safe `xhtml:link` elements. If a local browser/server displays sitemap XML as plain text with `xhtml` alternates enabled, keep `include_alternates` disabled or rely on HTML head hreflang.

If your local server or browser displays sitemap XML as plain text, switch the HTTP response type:

```php
'sitemap' => [
    'content_type' => 'text/xml; charset=UTF-8',
],
```

This affects HTTP sitemap routes only; Artisan command output remains a plain XML string.
It is not a workaround for malformed XML; the sitemap XML itself must still parse cleanly.

Use `locale` or `locales` on route entries to control which localized sitemap includes each URL:

```php
'routes' => [
    'products.fa' => [
        'locale' => 'fa',
        'canonical' => 'https://example.com/fa/products',
        'sitemap' => true,
    ],
    'products.en' => [
        'locale' => 'en',
        'canonical' => 'https://example.com/en/products',
        'sitemap' => true,
    ],
],
```

If a route entry has no `locale` or `locales`, it remains included as before for backward compatibility. When `robots_txt.sitemaps` is not manually configured, robots.txt points to the sitemap index.

Route sitemap entry:

```php
'routes' => [
    'home' => [
        'title' => 'Home',
        'canonical' => 'https://example.com',
        'sitemap' => true,
        'priority' => 1.0,
        'change_frequency' => 'daily',
    ],
],
```

Model sitemap source:

```php
'models' => [
    App\Models\Post::class => [
        'route' => 'posts.show',
        'route_key' => 'slug',
        'sitemap' => true,
        'sitemap_source' => fn () => App\Models\Post::query()
            ->where('published', true)
            ->cursor(),
        'priority' => 0.7,
        'change_frequency' => 'weekly',
    ],
],
```

Models may implement `Sitemapable` or expose matching methods:

```php
public function shouldBeInSitemap(?string $locale = null): bool
{
    return $this->published;
}

public function sitemapUrl(?string $locale = null): ?string
{
    return route('posts.show', $this);
}

public function sitemapLastModified(?string $locale = null): mixed
{
    return $this->updated_at;
}
```

Commands:

```bash
php artisan zarbin-seo:sitemap
php artisan zarbin-seo:sitemap --locale=fa
php artisan zarbin-seo:sitemap --index
php artisan zarbin-seo:sitemap --output=public/sitemap.xml
php artisan zarbin-seo:sitemap --count

php artisan zarbin-seo:robots
php artisan zarbin-seo:robots --output=public/robots.txt
```

Commands do not write files unless `--output` is provided.

## Optional Database Overrides

Publish and run the migration only when you want database-backed manual SEO overrides:

```bash
php artisan vendor:publish --tag=zarbin-seo-migrations
php artisan migrate
```

Enable both flags:

```php
'features' => [
    'database_overrides' => true,
],

'database' => [
    'enabled' => true,
],
```

Save model overrides:

```php
seo()->saveOverride($post, [
    'title' => 'Custom SEO title',
    'description' => 'Custom SEO description',
    'canonical' => 'https://example.com/custom-post',
    'robots' => ['index', 'follow'],
], 'fa');
```

Save route overrides:

```php
seo()->saveOverride('home', [
    'title' => 'Custom homepage title',
    'description' => 'Custom homepage description',
], 'en');
```

Eloquent models can use `HasSeoMeta`:

```php
use Zarbin\Seo\Concerns\HasSeoMeta;

class Post extends Model implements Seoable
{
    use HasSeo;
    use HasSeoMeta;
}
```

The package still works normally when database overrides are disabled or the table is missing.

## Optional Plain Blade UI

The UI is disabled by default. It edits database override records and has no Livewire, Filament, Nova, Inertia, Tailwind, or Bootstrap dependency.

```php
'features' => [
    'database_overrides' => true,
    'ui' => true,
],

'database' => [
    'enabled' => true,
],

'ui' => [
    'enabled' => true,
    'route_enabled' => true,
    'path' => 'admin/seo',
    'middleware' => ['web', 'auth'],
    'gate' => 'viewZarbinSeo',
    'completion' => [
        'required' => ['title', 'description', 'canonical', 'robots'],
        'recommended' => ['image'],
    ],
],
```

Dedicated route UI:

```text
GET /admin/seo
GET /admin/seo/routes
```

The route UI lists configured route-only pages and shows completion status. `✓` means all required SEO fields are present; `×` means one or more required fields are missing. Required and recommended fields are configurable under `ui.completion`, and recommended misses appear as warnings only. The robots field is a dropdown with common presets from `ui.robots_options`; published Blade views can still be customized.

Embeddable form for your own admin panel:

```blade
<x-zarbin-seo::form :source="$post" locale="fa" />
<x-zarbin-seo::form source="home" locale="en" action="{{ route('admin.seo.save') }}" standalone />
```

## Product / Commerce Schema

Commerce support is disabled by default and does not depend on WooCommerce, Cashier, Bagisto, Aimeos, Vanilo, Stripe, Paddle, or any ecommerce package.

```php
'features' => [
    'commerce' => true,
],

'commerce' => [
    'enabled' => true,
    'offer' => [
        'enabled' => 'auto',
        'require_price' => true,
    ],
    'default_currency' => 'IRR',
    'currency_per_locale' => [
        'fa' => 'IRR',
        'en' => 'USD',
    ],
],
```

Offer generation is optional. The default `auto` mode builds an `Offer` only when real priced offer data exists, so corporate/catalog product pages can render valid Product schema without pretending to sell online. HTML/head SEO and commerce schema do not require an ecommerce package.

Model mapping:

```php
'models' => [
    App\Models\Product::class => [
        'route' => 'products.show',
        'route_key' => 'slug',
        'type' => 'Product',
        'commerce' => [
            'enabled' => true,
            'name' => 'name',
            'description' => ['short_description', 'description'],
            'image' => 'image_url',
            'price' => 'price',
            'currency' => 'currency',
            'sku' => 'sku',
            'brand' => 'brand.name',
            'availability' => 'stock_status',
            'condition' => 'condition',
        ],
    ],
],
```

Commerce mappings can read nested relations and locale-aware collections. This is useful when price or copy lives on translations, discounts, active offers, variants, or price rows.

Catalog product without Offer:

```php
'commerce' => [
    'enabled' => true,
    'name' => ['translations[locale={locale}].title', 'title'],
    'description' => ['translations[locale={locale}].description', 'description'],
    'brand' => 'brand.name',
],
```

Ecommerce product with translated price:

```php
'commerce' => [
    'enabled' => true,
    'name' => ['translations[locale={locale}].title', 'title'],
    'price' => ['translations[locale={locale}].price', 'activeOffer.price', 'discount.price'],
    'currency' => 'literal:IRR',
],
```

Relation filter form:

```php
'price' => [
    'relation' => 'translations',
    'where' => ['locale' => '{locale}'],
    'value' => 'price',
],
```

Fallback arrays return the first non-empty value and preserve `0` / `"0"`. Callables are also supported for project-specific lookups.

Contract-based product data:

```php
use Zarbin\Seo\Contracts\CommerceSeo;
use Zarbin\Seo\Data\CommerceData;

class Product extends Model implements Seoable, CommerceSeo
{
    use HasSeo;

    public function toCommerceData(?string $locale = null): CommerceData|array|null
    {
        return CommerceData::make([
            'name' => $this->name,
            'price' => $this->price,
            'currency' => 'IRR',
            'availability' => $this->stock > 0 ? 'in_stock' : 'out_of_stock',
            'brand' => $this->brand?->name,
            'sku' => $this->sku,
        ]);
    }
}
```

Fluent commerce data:

```php
seo()
    ->title('Product title')
    ->commerce([
        'price' => 120000,
        'currency' => 'IRR',
        'availability' => 'in_stock',
    ])
    ->render();
```

Product holder and listing pages should usually remain `CollectionPage`. ItemList schema may be added in a future phase.

## Artisan Commands

Install:

```bash
php artisan zarbin-seo:install
php artisan zarbin-seo:install --all
php artisan zarbin-seo:install --migrations --run-migrations
```

Doctor/readiness checks:

```bash
php artisan zarbin-seo:doctor
php artisan zarbin-seo:doctor --strict
php artisan zarbin-seo:doctor --json
```

Inspect resolved SEO data:

```bash
php artisan zarbin-seo:check
php artisan zarbin-seo:check --route=home
php artisan zarbin-seo:check --route=home --locale=fa --render
php artisan zarbin-seo:check --model="App\Models\Post" --id=1 --json
```

Export sitemap or robots output:

```bash
php artisan zarbin-seo:sitemap --output=public/sitemap.xml
php artisan zarbin-seo:robots --output=public/robots.txt
```

Commands are safe by default: no model crawling unless explicitly requested, no migrations unless `--run-migrations` is provided, and no file writes unless `--output` is provided.

## Configuration Reference

Important config areas:

- `defaults`: fallback title, description, image, separator, robots, and description limit.
- `features`: toggle Open Graph, Twitter, schema, sitemap, robots.txt, alternates, database overrides, UI, and commerce.
- `localization`: locales, default locale, URL strategy, route parameter, missing translation strategy, hreflang, and `x-default`.
- `sitemap`: public route, default/index paths, localized paths, defaults, alternates, and cache placeholders.
- `robots_txt`: public route, user-agent, allow/disallow, and sitemap lines.
- `database`: optional override table/model settings.
- `ui`: optional Blade UI route, middleware, gate, and preview settings.
- `commerce`: optional Offer generation, default currency, locale currencies, availability map, and condition map.
- `models`: model and holder mappings.
- `routes`: route-only SEO mappings.

## Testing

```bash
composer test
composer format:test
```

## Consumer App Smoke Test

Before releasing a new tag, you can run a real Laravel consumer-app smoke test:

```bash
php scripts/e2e-consumer-app.php
```

The script creates a temporary Laravel app, installs this package through a Composer path repository, and verifies package discovery, publish tags, commands, sitemap/robots routes, and Blade rendering. See [docs/e2e.md](docs/e2e.md).

## Hardening / Bulletproof Tests

The test suite includes bulletproof coverage for broken config, disabled features, missing database tables, invalid routes, malformed localization, UI/database mismatch, commerce edge cases, and rendering safety. See [docs/hardening.md](docs/hardening.md).

## Changelog

See [CHANGELOG.md](CHANGELOG.md).

## Security

If you discover a security issue, please open a private/security report through GitHub if enabled, or contact the maintainer through the repository.

## Credits

Zarbin / zarbinco

## License

The MIT License (MIT). See [LICENSE.md](LICENSE.md).
