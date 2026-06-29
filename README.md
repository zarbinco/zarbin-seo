# Zarbin SEO

Zarbin SEO is a lightweight, Laravel-native SEO toolkit for applications that want predictable metadata, schema, sitemap, localization, and model-aware SEO workflows without adopting a heavy admin stack.

The package is inspired by common SEO editorial workflows and concepts popularized by tools such as Yoast SEO, but it is not a WordPress clone and is not affiliated with Yoast.

## Development Status

Pre-release. This repository currently contains the package skeleton, configuration foundation, lightweight SEO data layer, source resolvers, HTML rendering layer, multilingual hreflang support, sitemap/robots.txt generation, and optional database SEO overrides.

The package intentionally does not include UI, analytics, AI, Search Console integrations, or external SEO service integrations.

## Installation

```bash
composer require zarbinco/zarbin-seo
```

## Publishing The Config

```bash
php artisan vendor:publish --tag="zarbin-seo-config"
```

## Current Usage

```php
use Zarbin\Seo\Facades\ZarbinSeo;

ZarbinSeo::name(); // zarbin-seo
ZarbinSeo::version();
```

You can also resolve the package service directly:

```php
$seo = app('zarbin-seo');

$seo->name();
$seo->version();
```

## Phase 1 Usage

Use the fluent manager to compose SEO data for the current request:

```php
$data = seo()
    ->reset()
    ->title('Product title')
    ->description('Concise search result description')
    ->canonical(route('products.show', $product))
    ->robots('index, follow')
    ->get();

$data->toArray();
```

Create a data object directly when you already have normalized values:

```php
use Zarbin\Seo\Data\SeoData;

$data = SeoData::make([
    'title' => 'About Zarbin',
    'description' => 'A short description for search results.',
    'robots' => ['index', 'follow'],
]);

$data->robotsContent(); // index, follow
```

Models can opt into SEO data with the contract and trait:

```php
use Zarbin\Seo\Concerns\HasSeo;
use Zarbin\Seo\Contracts\Seoable;

final class Product implements Seoable
{
    use HasSeo;

    public function seoTitle(?string $locale = null): ?string
    {
        return $this->name;
    }

    public function seoDescription(?string $locale = null): ?string
    {
        return $this->summary;
    }
}
```

Advanced admin editing workflows are planned for future phases.

## Phase 2 Source Resolution

Zarbin SEO can now resolve `SeoData` from models, model-backed holder pages, route-only pages, raw arrays, existing `SeoData` objects, and defaults.

### Resolving SEO From A Model

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
}

$data = seo()->for($post)->get();
```

### Resolving SEO From A Model-Backed Holder

```php
use Illuminate\Database\Eloquent\Model;
use Zarbin\Seo\Concerns\HasSeo;
use Zarbin\Seo\Contracts\Seoable;

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

$data = seo()->for($productHolder)->get();
```

### Resolving SEO From A Route-Only Page

```php
$data = seo()->route('home')->get();
```

### Configuring Model Mappings

```php
'models' => [
    Product::class => [
        'title' => 'name',
        'description' => ['excerpt', 'description', 'content'],
        'image' => ['image_url', 'cover_image_url'],
        'route' => 'products.show',
        'route_key' => 'slug',
        'type' => 'Product',
    ],
],
```

### Configuring Route Mappings

```php
'routes' => [
    'home' => [
        'title' => 'Home',
        'description' => 'Welcome to our website',
        'schema' => 'WebPage',
        'sitemap' => true,
    ],
    'products.index' => [
        'title' => 'Products',
        'description' => 'Browse our products',
        'schema' => 'CollectionPage',
    ],
],
```

### Resolver Priority

Resolution starts with defaults and common attributes, then applies config mappings, then applies non-empty `Seoable::toSeoData()` values last. That means explicit model SEO methods beat config mappings, while config and defaults still fill any missing values.

## Phase 3 HTML Rendering

Render the current SEO state directly inside your layout `<head>`:

```blade
{!! seo()->render() !!}
```

Or render individual segments when your layout needs tighter control:

```blade
{!! seo()->meta() !!}
{!! seo()->openGraph() !!}
{!! seo()->twitter() !!}
{!! seo()->jsonLd() !!}
```

Use the Blade component when you prefer component syntax:

```blade
<x-zarbin-seo::meta />
<x-zarbin-seo::meta :source="$post" />
```

In controllers or page actions, resolve the current page before returning the view:

```php
public function show(Post $post)
{
    seo()->for($post);

    return view('posts.show', compact('post'));
}
```

The renderer currently outputs title, meta description, canonical, robots, hreflang alternate links, Open Graph, Twitter/X Card, and basic JSON-LD tags.

UI and database-backed editing screens are not part of this phase.

## Phase 4 Multilingual SEO

Enable lightweight locale-aware resolution and hreflang rendering in the package config:

```php
'localization' => [
    'enabled' => true,
    'locales' => ['fa', 'en'],
    'default_locale' => 'fa',
    'route_parameter' => 'locale',
    'missing_translation_strategy' => 'hide',
    'generate_hreflang' => true,
    'x_default' => 'fa',
],
```

### Missing Translation Strategy

`hide` skips unavailable locales in alternate links and marks the resolved data with `available_for_locale => false`.

`fallback` can point unavailable locales at the default locale URL and marks the resolved data with `used_locale_fallback => true`.

`noindex` skips unavailable locales in alternate links and changes the current robots value to `noindex, follow`.

### Model-Backed Multilingual Pages And Holders

Models and holder pages can opt into the optional `LocalizableSeo` contract without requiring any translation package:

```php
use Illuminate\Database\Eloquent\Model;
use Zarbin\Seo\Concerns\HasSeo;
use Zarbin\Seo\Contracts\LocalizableSeo;
use Zarbin\Seo\Contracts\Seoable;

class ProductHolder extends Model implements Seoable, LocalizableSeo
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
        return route('products.index', ['locale' => $locale]);
    }
}
```

Render the localized page:

```php
seo()->for($holder, 'fa')->render();
```

### Route-Only Multilingual Pages

Route-only pages can use route mappings or localized URL mappings:

```php
'routes' => [
    'home' => [
        'title' => 'Home',
        'localized_routes' => [
            'fa' => 'fa.home',
            'en' => 'en.home',
        ],
    ],
],
```

Resolve and render a route-only page:

```php
seo()->route('home', [], 'en')->render();
```

### Rendering Hreflang Tags

Hreflang tags are included in the full renderer and Blade component:

```blade
{!! seo()->render() !!}
<x-zarbin-seo::meta :source="$post" locale="fa" />
```

You can also render only alternates:

```blade
{!! seo()->alternates() !!}
```

### x-default

`x_default` can be a locale code, an explicit URL, or `true` to use the default locale URL when available.

UI and database-backed editing screens are not part of this phase.

## Phase 5 Sitemap And Robots.txt

Zarbin SEO can generate lightweight XML sitemaps and robots.txt output from route mappings, model-backed pages, and holder pages.

### Sitemap Generation

Render the sitemap directly:

```php
seo()->sitemap();
seo()->sitemapIndex();
seo()->robotsTxt();
```

The package also registers public routes when enabled:

```text
/sitemap.xml
/sitemap_index.xml
/robots.txt
```

### Route-Only Sitemap Entries

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

### Model-Backed Sitemap Entries

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

### Holder Page Sitemap Entries

Holder pages such as `ProductHolder`, `BlogHolder`, or `HomePage` can be returned from `sitemap_items` or `sitemap_source` the same way as normal models:

```php
'models' => [
    App\Models\ProductHolder::class => [
        'sitemap' => true,
        'sitemap_items' => [app(App\Models\ProductHolder::class)],
        'priority' => 0.9,
        'change_frequency' => 'daily',
    ],
],
```

### Multilingual Sitemap Output

When localization is enabled, sitemap generation can emit one URL per configured locale and include alternate language links where URLs are available:

```php
seo()->sitemap('fa');
seo()->sitemap('en');
seo()->sitemap(); // all configured locales
```

### Model Sitemap Methods

Models may implement the optional `Sitemapable` contract, or simply expose matching methods:

```php
public function shouldBeInSitemap(?string $locale = null): bool
{
    return $this->published;
}

public function sitemapUrl(?string $locale = null): ?string
{
    return route('posts.show', $this->slug);
}

public function sitemapUrlForLocale(string $locale): ?string
{
    return route('posts.show', ['locale' => $locale, 'post' => $this->slug]);
}

public function sitemapPriority(?string $locale = null): float
{
    return $this->is_featured ? 0.9 : 0.6;
}

public function sitemapChangeFrequency(?string $locale = null): string
{
    return 'weekly';
}

public function sitemapLastModified(?string $locale = null): mixed
{
    return $this->updated_at;
}
```

### Robots.txt Generation

```php
'robots_txt' => [
    'enabled' => true,
    'route_enabled' => true,
    'path' => 'robots.txt',
    'user_agent' => '*',
    'allow' => ['/'],
    'disallow' => ['/admin'],
    'sitemaps' => [],
],
```

When no sitemap URL is configured, robots.txt will point to the generated sitemap index when possible.

UI and database-backed editing screens are not part of this phase. Artisan commands are planned for a later developer-experience phase.

## Phase 6 Optional Database Overrides

Database SEO overrides are optional. The package works normally without the table, and it will not query the database unless both `features.database_overrides` and `database.enabled` are true.

### Publishing And Running The Migration

```bash
php artisan vendor:publish --tag=zarbin-seo-migrations
php artisan migrate
```

### Enabling Database Overrides

```php
'features' => [
    'database_overrides' => true,
],

'database' => [
    'enabled' => true,
],
```

If the feature is enabled before the migration is run, `ignore_missing_table` keeps normal SEO resolution working without crashing the app.

### Model-Backed Page Overrides

```php
seo()->saveOverride($post, [
    'title' => 'Custom SEO title',
    'description' => 'Custom SEO description',
    'canonical' => 'https://example.com/custom-post',
    'robots' => ['index', 'follow'],
], 'fa');

seo()->for($post, 'fa')->render();
```

### Holder Page Overrides

Holder models such as `ProductHolder`, `BlogHolder`, and `HomePage` use the same API:

```php
seo()->saveOverride($productHolder, [
    'title' => 'Custom products title',
    'schema_type' => 'CollectionPage',
], 'en');
```

### Route-Only Page Overrides

```php
seo()->saveOverride('home', [
    'title' => 'Custom homepage title',
    'description' => 'Custom homepage description',
], 'en');

seo()->route('home', [], 'en')->render();
```

### Locale-Specific Overrides

The locale is stored alongside the target, so each model or route can have separate manual SEO values per language:

```php
seo()->saveOverride($post, ['title' => 'عنوان فارسی'], 'fa');
seo()->saveOverride($post, ['title' => 'English title'], 'en');
```

### Social Overrides

Open Graph and Twitter/X card values can be stored in dedicated columns or developer-friendly nested arrays:

```php
seo()->saveOverride($post, [
    'open_graph' => [
        'title' => 'Custom share title',
        'description' => 'Custom share description',
        'image' => 'https://example.com/share.jpg',
    ],
    'twitter' => [
        'title' => 'Custom X title',
        'description' => 'Custom X description',
        'image' => 'https://example.com/x.jpg',
    ],
    'schema_type' => 'Article',
    'extra' => [
        'editor_note' => 'Reviewed manually',
    ],
]);
```

### Optional Model Trait

Eloquent models can use `HasSeoMeta` for local helpers:

```php
use Illuminate\Database\Eloquent\Model;
use Zarbin\Seo\Concerns\HasSeo;
use Zarbin\Seo\Concerns\HasSeoMeta;
use Zarbin\Seo\Contracts\Seoable;

class Post extends Model implements Seoable
{
    use HasSeo;
    use HasSeoMeta;
}
```

```php
$post->saveSeoMeta(['title' => 'Manual SEO title'], 'fa');
$post->seoMetaForLocale('fa');
$post->deleteSeoMeta('fa');
```

Advanced UI workflows such as bulk editing and model discovery will be added in later phases.

## Phase 7 Optional UI Layer

Zarbin SEO includes an optional plain Blade UI for editing database-backed SEO overrides. It is disabled by default and does not depend on Livewire, Filament, Nova, Inertia, Tailwind, Bootstrap, or any admin panel stack.

### Enabling UI

Enable database overrides first, then opt into the UI:

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
],
```

The UI route is active only when both `features.ui` and `ui.enabled` are true. The dedicated route UI also requires `ui.route_enabled`.

### Dedicated Route UI

The package can register a small route UI:

```text
GET /admin/seo
GET /admin/seo/routes
GET /admin/seo/routes/edit?route=home
```

The route UI currently manages configured route-only overrides from `config('zarbin-seo.routes')`.

### Gate And Middleware Protection

Use normal Laravel middleware and gates:

```php
'ui' => [
    'middleware' => ['web', 'auth'],
    'gate' => 'viewZarbinSeo',
],
```

When a gate is configured, access is denied with HTTP 403 unless `Gate::allows($gate)` returns true.

### Embeddable Form Component

Embed the form inside any existing admin screen:

```blade
<x-zarbin-seo::form :source="$post" locale="fa" />
```

Use it for route-only pages too:

```blade
<x-zarbin-seo::form source="home" locale="en" action="{{ route('admin.seo.save') }}" standalone />
```

Model and holder records are edited by embedding the form in your own admin panel. The package does not crawl models or provide a model list UI in this phase.

### Publishing Views

```bash
php artisan vendor:publish --tag=zarbin-seo-views
```

The UI and form views are intentionally plain Blade so they can be customized to match your existing admin area.

### Database Requirement

The UI edits manual override records, so it requires the Phase 6 migration and database override feature flags. If the UI is enabled before the table is ready, it shows a warning instead of crashing.

## Planned Direction

Future versions are expected to build around Laravel-friendly primitives:

```php
$post->seoTitle();
$post->seoDescription();

seo()
    ->title('Product title')
    ->description('Concise search result description')
    ->canonical(route('products.show', $product));
```

The long-term goal is to support model-aware metadata, Open Graph, Twitter cards, JSON-LD schema, sitemaps, robots.txt, breadcrumbs, and localization hooks while keeping all heavier pieces optional.

## Testing

```bash
composer test
composer format:test
```

## License

The MIT License (MIT). See [LICENSE.md](LICENSE.md).
