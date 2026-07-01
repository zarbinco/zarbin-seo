# مرجع خلاصه config

[بازگشت به فهرست فارسی](README.md)

فایل `config/zarbin-seo.php` مرکز تنظیمات package است. این صفحه کل config را تکرار نمی‌کند؛ فقط بخش‌های مهم را توضیح می‌دهد.

## defaults

مقدارهای پیش‌فرض مثل `title`، `description`، `image`، `separator`، `robots` و `description_limit`.

## features

feature flagها برای Open Graph، Twitter، schema، sitemap، robots.txt، alternate languages، database overrides، UI و commerce.

## localization

تنظیم localeها، locale پیش‌فرض، استراتژی URL، route parameter، missing translation strategy، hreflang و `x-default`.

```php
'localization' => [
    'enabled' => true,
    'locales' => ['fa', 'en'],
    'default_locale' => 'fa',
    'url_strategy' => 'prefixed_all',
    'route_parameter' => 'locale',
],
```

`url_strategy` می‌تواند این مقدارها را داشته باشد:

- `default_without_prefix`: مثلا `/about` برای زبان پیش‌فرض و `/fa/about` برای زبان دیگر.
- `prefixed_all`: مثلا `/en/about` و `/fa/about`.
- `custom`: برای پروژه‌های خاص که URL هر زبان را با `localized_urls` یا `localized_routes` مشخص می‌کنند.

## sitemap

فعال بودن sitemap، route عمومی، pathها، pathهای جدا برای هر locale، priority/change frequency پیش‌فرض و include alternates.

```php
'sitemap' => [
    'base_url' => 'https://sunich.org',
    'content_type' => 'application/xml; charset=UTF-8',
    'path' => 'sitemap.xml',
    'index_path' => 'sitemap_index.xml',
    'localized_paths' => [
        'fa' => 'sitemap-fa.xml',
        'en' => 'sitemap-en.xml',
    ],
    'localized_route_enabled' => true,
    'include_localized_in_index' => true,
    'include_alternates' => false,
],
```

اگر `localized_paths` خالی باشد، رفتار قبلی `/sitemap.xml` باقی می‌ماند. اگر host تولید sitemap با host واقعی سایت فرق دارد، `base_url` را تنظیم کنید تا مثلا خروجی بین `localhost:3000` و `sunich.test` قاطی نشود.

اگر browser خروجی sitemap را به جای XML tree مثل متن ساده نشان می‌دهد، می‌توانید `content_type` را برای routeهای HTTP به `text/xml; charset=UTF-8` تغییر دهید. این تنظیم روی خروجی commandها اثر ندارد.

`include_alternates` به صورت پیش‌فرض `false` است. فقط وقتی آن را `true` کنید که پروژه بخواهد hreflang با `xhtml:link` داخل sitemap هم داشته باشد؛ hreflang داخل head صفحه‌ها مستقل از این تنظیم کار می‌کند.

## robots_txt

تنظیم user-agent، allow، disallow و sitemap lineها.

## database

تنظیمات table، model، route type و safe missing table برای overrideهای دیتابیسی.

## ui

UI اختیاری Blade: path، middleware، gate، route enablement و preview.

## commerce

```php
'commerce' => [
    'enabled' => true,
    'offer' => [
        'enabled' => 'auto',
        'require_price' => true,
    ],
    'default_currency' => 'IRR',
],
```

`offer.enabled` می‌تواند `auto`، `true` یا `false` باشد. مقدار پیش‌فرض `auto` با `require_price=true` باعث می‌شود Product schema بدون price، Offer نسازد. برای mappingهای محصول می‌توانید از مسیرهای relation مثل `brand.name`، مسیرهای وابسته به locale مثل `translations[locale={locale}].price` و فرم `relation` / `where` / `value` استفاده کنید.

currency پیش‌فرض، currency بر اساس locale، availability map و condition map.

## models

mapping مدل‌ها و holderها:

```php
'models' => [
    App\Models\Post::class => [
        'title' => 'title',
        'description' => ['excerpt', 'content'],
        'route' => 'posts.show',
        'route_key' => 'slug',
    ],
],
```

## routes

mapping صفحه‌های route-only:

```php
'routes' => [
    'home' => [
        'title' => 'Home',
        'description' => 'Welcome to our website.',
        'sitemap' => true,
    ],
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

`locale` یا `locales` روی route مشخص می‌کند آن URL در کدام sitemap زبانی بیاید؛ مثلا `sitemap-fa.xml` فقط URLهای فارسی را نشان دهد.
