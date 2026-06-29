# مرجع خلاصه config

[بازگشت به فهرست فارسی](README.md)

فایل `config/zarbin-seo.php` مرکز تنظیمات package است. این صفحه کل config را تکرار نمی‌کند؛ فقط بخش‌های مهم را توضیح می‌دهد.

## defaults

مقدارهای پیش‌فرض مثل `title`، `description`، `image`، `separator`، `robots` و `description_limit`.

## features

feature flagها برای Open Graph، Twitter، schema، sitemap، robots.txt، alternate languages، database overrides، UI و commerce.

## localization

تنظیم localeها، locale پیش‌فرض، route parameter، missing translation strategy، hreflang و `x-default`.

## sitemap

فعال بودن sitemap، route عمومی، pathها، priority/change frequency پیش‌فرض و include alternates.

## robots_txt

تنظیم user-agent، allow، disallow و sitemap lineها.

## database

تنظیمات table، model، route type و safe missing table برای overrideهای دیتابیسی.

## ui

UI اختیاری Blade: path، middleware، gate، route enablement و preview.

## commerce

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
],
```
