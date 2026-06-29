# Sitemap و robots.txt

[بازگشت به فهرست فارسی](README.md)

پکیج می‌تواند مسیرهای عمومی زیر را ارائه کند:

- `/sitemap.xml`
- `/sitemap_index.xml`
- `/robots.txt`

## Routeهای sitemap

برای route-only pageها از config استفاده کنید:

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

## مدل‌ها و holderها

برای مدل‌ها می‌توانید `sitemap_source` تعریف کنید. برای دیتاست‌های بزرگ بهتر است از cursor یا query مناسب استفاده شود.

```php
'models' => [
    App\Models\Post::class => [
        'route' => 'posts.show',
        'route_key' => 'slug',
        'sitemap' => true,
        'sitemap_source' => fn () => App\Models\Post::query()->where('published', true)->cursor(),
        'priority' => 0.7,
        'change_frequency' => 'weekly',
    ],
],
```

## متدهای Sitemapable

مدل می‌تواند این متدها را داشته باشد:

- `shouldBeInSitemap`
- `sitemapUrl`
- `sitemapPriority`
- `sitemapChangeFrequency`
- `sitemapLastModified`

## commandها

```bash
php artisan zarbin-seo:sitemap
php artisan zarbin-seo:sitemap --locale=fa
php artisan zarbin-seo:sitemap --index
php artisan zarbin-seo:sitemap --output=public/sitemap.xml
php artisan zarbin-seo:sitemap --count

php artisan zarbin-seo:robots
php artisan zarbin-seo:robots --output=public/robots.txt
```

Commandها فایل نمی‌نویسند مگر اینکه `--output` بدهید. تست‌های bulletproof هم guard می‌کنند که خطای sitemap source باعث fatal شدن کل sitemap نشود.
