# Overrideهای دیتابیسی

[بازگشت به فهرست فارسی](README.md)

گاهی عنوان یا description مدل برای کاربر مناسب است، اما برای SEO باید متفاوت باشد. database overrides برای همین حالت است: مقدارهای دستی SEO بدون تغییر دادن داده اصلی مدل ذخیره می‌شوند.

این قابلیت اختیاری است و اگر فعال نباشد، پکیج دیتابیس را query نمی‌کند.

## نصب migration

```bash
php artisan vendor:publish --tag=zarbin-seo-migrations
php artisan migrate
```

## فعال‌سازی

```php
'features' => [
    'database_overrides' => true,
],

'database' => [
    'enabled' => true,
],
```

## override برای مدل

```php
seo()->saveOverride($post, [
    'title' => 'Custom SEO title',
    'description' => 'Custom SEO description',
    'canonical' => 'https://example.com/custom-post',
    'robots' => ['index', 'follow'],
], 'fa');
```

## override برای route

```php
seo()->saveOverride('home', [
    'title' => 'Custom homepage title',
    'description' => 'Custom homepage description',
], 'en');
```

## HasSeoMeta

اگر مدل شما Eloquent است، trait اختیاری `HasSeoMeta` helperهای رابطه و ذخیره‌سازی می‌دهد. این trait اجباری نیست.

## social overrides

فیلدهای Open Graph و Twitter/X مثل `og_title`، `og_description`، `og_image`، `twitter_title` و `twitter_image` هم پشتیبانی می‌شوند.

اگر table وجود نداشته باشد و `ignore_missing_table` برابر `true` باشد، پکیج باید بدون crash به SEO عادی برگردد. UI اختیاری هم روی همین لایه دیتابیسی کار می‌کند.
