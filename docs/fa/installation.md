# نصب

[بازگشت به فهرست فارسی](README.md)

Zarbin SEO با auto-discovery لاراول کار می‌کند. در بیشتر پروژه‌ها بعد از نصب Composer نیازی به ثبت دستی service provider ندارید.

## نصب با Composer

```bash
composer require zarbinco/zarbin-seo
```

## انتشار config

```bash
php artisan vendor:publish --tag=zarbin-seo-config
```

فایل `config/zarbin-seo.php` جایی است که defaults، feature flagها، مدل‌ها، routeها، localization، sitemap، UI و commerce را تنظیم می‌کنید.

## migration اختیاری

```bash
php artisan vendor:publish --tag=zarbin-seo-migrations
php artisan migrate
```

این migration فقط وقتی لازم است که بخواهید manual database overrides را فعال کنید؛ یعنی عنوان SEO، description، canonical، robots یا social meta را جدا از داده اصلی مدل ذخیره کنید.

## viewهای اختیاری

```bash
php artisan vendor:publish --tag=zarbin-seo-views
```

viewها فقط وقتی لازم هستند که بخواهید UI ساده Blade یا فرم‌های embed را شخصی‌سازی کنید. UI به‌صورت پیش‌فرض غیرفعال است.

## بررسی وضعیت بعد از نصب

```bash
php artisan zarbin-seo:doctor
```

این command تنظیمات مهم را بررسی می‌کند و هشدارهای کاربردی می‌دهد، اما SEO score یا تحلیل محتوا انجام نمی‌دهد.
