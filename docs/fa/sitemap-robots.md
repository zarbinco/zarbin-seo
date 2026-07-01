# Sitemap و robots.txt

[بازگشت به فهرست فارسی](README.md)

پکیج می‌تواند مسیرهای عمومی زیر را ارائه کند:

- `/sitemap.xml`
- `/sitemap_index.xml`
- `/robots.txt`

اگر پروژه برای هر زبان sitemap جدا دارد، می‌توانید pathهای جدا تعریف کنید:

```php
'localization' => [
    'enabled' => true,
    'locales' => ['fa', 'en'],
    'default_locale' => 'fa',
    'url_strategy' => 'prefixed_all',
    'route_parameter' => 'locale',
],

'sitemap' => [
    'base_url' => 'https://sunich.org',
    'content_type' => 'application/xml; charset=UTF-8',
    'localized_paths' => [
        'fa' => 'sitemap-fa.xml',
        'en' => 'sitemap-en.xml',
    ],
],
```

با این config، مسیرهای `/sitemap-fa.xml` و `/sitemap-en.xml` فعال می‌شوند و `/sitemap_index.xml` هر دو فایل را list می‌کند. `base_url` کمک می‌کند host در sitemap با host واقعی سایت یکی بماند و مثلا بین `localhost:3000` و `sunich.test` قاطی نشود. اگر `robots_txt.sitemaps` را دستی تنظیم نکرده باشید، robots.txt به sitemap index اشاره می‌کند.

وقتی `sitemap.include_alternates` فعال باشد، hreflangهای sitemap به صورت XML-safe و با تگ‌های `xhtml:link` ساخته می‌شوند. اگر پروژه به hreflang داخل sitemap نیاز ندارد، می‌توانید `include_alternates` را خاموش کنید.

اگر مرورگر یا server محلی sitemap را با `application/xml` مثل متن ساده نشان داد، می‌توانید فقط برای routeهای HTTP مقدار content type را تغییر دهید:

```php
'sitemap' => [
    'content_type' => 'text/xml; charset=UTF-8',
],
```

این تنظیم روی خروجی commandها اثری ندارد و commandها همچنان XML را به صورت string چاپ می‌کنند.
این گزینه جایگزین XML معتبر نیست؛ خود sitemap همچنان باید بدون خطا parse شود.

برای اینکه `sitemap-fa.xml` فقط URLهای فارسی و `sitemap-en.xml` فقط URLهای انگلیسی را نشان دهد، روی route entry مقدار `locale` یا `locales` بگذارید:

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

اگر route entry مقدار `locale` یا `locales` نداشته باشد، برای سازگاری با نسخه‌های قبلی مثل قبل در sitemapها می‌آید.

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
