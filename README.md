# Zarbin SEO

Zarbin SEO is a lightweight, Laravel-native SEO toolkit for applications that want predictable metadata, schema, sitemap, localization, and model-aware SEO workflows without adopting a heavy admin stack.

The package is inspired by common SEO editorial workflows and concepts popularized by tools such as Yoast SEO, but it is not a WordPress clone and is not affiliated with Yoast.

## Development Status

Pre-release. This repository currently contains the package skeleton, configuration foundation, lightweight SEO data layer, source resolvers, HTML rendering layer, multilingual hreflang support, and sitemap/robots.txt generation.

The package intentionally does not include UI, database overrides, analytics, AI, Search Console integrations, or external SEO service integrations yet.

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

UI and database overrides are planned for upcoming phases.

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

Database overrides and UI are not part of this phase.

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

Database overrides and UI are not part of this phase.

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

Database overrides and UI are not part of this phase. Artisan commands are planned for a later developer-experience phase.

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
