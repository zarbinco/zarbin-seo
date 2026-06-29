# Zarbin SEO

Zarbin SEO is a lightweight, Laravel-native SEO toolkit for applications that want predictable metadata, schema, sitemap, localization, and model-aware SEO workflows without adopting a heavy admin stack.

The package is inspired by common SEO editorial workflows and concepts popularized by tools such as Yoast SEO, but it is not a WordPress clone and is not affiliated with Yoast.

## Development Status

Pre-release. This repository currently contains the package skeleton, configuration foundation, and lightweight SEO data layer.

The first phase intentionally does not include full HTML renderers, sitemap generation, UI, database overrides, analytics, AI, Search Console integrations, or external SEO service integrations.

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

HTML rendering, sitemap generation, UI, and database overrides are planned for upcoming phases.

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

HTML rendering will be added in a later phase. Sitemap generation will be added in a later phase. Database overrides and UI are not part of this phase.

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
