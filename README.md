# Zarbin SEO

Zarbin SEO is a lightweight, Laravel-native SEO toolkit for applications that want predictable metadata, schema, sitemap, localization, and model-aware SEO workflows without adopting a heavy admin stack.

The package is inspired by common SEO editorial workflows and concepts popularized by tools such as Yoast SEO, but it is not a WordPress clone and is not affiliated with Yoast.

## Development Status

Pre-release. This repository currently contains the package skeleton and foundation only.

The first phase intentionally does not include renderers, sitemap generation, UI, database overrides, analytics, AI, Search Console integrations, or external SEO service integrations.

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

## Planned Direction

Future versions are expected to build around Laravel-friendly primitives:

```php
// Planned API direction only. Not implemented yet.
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
