# مستندات فارسی Zarbin SEO

> مستندات انگلیسی: [README.md](../../README.md)

این پکیج دارای مستندات فارسی و انگلیسی است.

Zarbin SEO یک پکیج سبک و Laravel-native برای مدیریت SEO در پروژه‌های لاراولی است. هدف پکیج این است که متادیتا، canonical، Open Graph، Twitter/X Card، JSON-LD، hreflang، sitemap و robots.txt را با مدل‌ها، routeها و صفحه‌های holder هماهنگ کند، بدون اینکه پروژه را به یک سیستم سنگین یا پنل خاص وابسته کند.

این پکیج از جریان‌های رایج SEO الهام گرفته، اما کپی وردپرس یا Yoast نیست و هیچ وابستگی یا ارتباطی با Yoast ندارد.

## مناسب چه کسانی است؟

- توسعه‌دهنده‌های Laravel که می‌خواهند SEO را نزدیک به مدل‌ها و routeهای خودشان نگه دارند.
- پروژه‌هایی که صفحه‌های مدل‌محور مثل Post، Product، Category یا Page دارند.
- پروژه‌هایی که صفحه‌های holder مثل HomePage، ProductHolder، BlogHolder یا LandingPage دارند.
- تیم‌هایی که می‌خواهند override دستی SEO، UI ساده Blade، sitemap، robots.txt و تست‌های سخت‌گیرانه داشته باشند، اما وابسته به Filament، Nova، Livewire یا سرویس خارجی نشوند.

## قابلیت‌های اصلی

- SEO آگاه از مدل‌ها و داده‌های خود پروژه
- SEO برای routeهای مستقل مثل `home`، `about` و `products.index`
- پشتیبانی از holder pageها
- خروجی Blade برای `<head>`
- Open Graph، Twitter/X Card و JSON-LD
- پشتیبانی چندزبانه، hreflang و `x-default`
- sitemap، sitemap index و robots.txt
- override اختیاری با دیتابیس
- UI ساده Blade و فرم قابل embed، هر دو غیرفعال به‌صورت پیش‌فرض
- Product/Commerce schema بدون وابستگی به پکیج فروشگاهی
- Artisan commandهای امن برای نصب، doctor، check، sitemap و robots
- تست‌های hardening و smoke/E2E برای نصب در اپ واقعی Laravel

## نصب سریع

```bash
composer require zarbinco/zarbin-seo
php artisan vendor:publish --tag=zarbin-seo-config
php artisan zarbin-seo:doctor
```

## فهرست مستندات فارسی

- [نصب](installation.md)
- [شروع سریع](quick-start.md)
- [SEO مدل‌محور](model-aware-seo.md)
- [Holder Pageها](holder-pages.md)
- [SEO برای routeها](route-seo.md)
- [رندر کردن تگ‌های SEO](rendering.md)
- [SEO چندزبانه](multilingual.md)
- [Sitemap و robots.txt](sitemap-robots.md)
- [Overrideهای دیتابیسی](database-overrides.md)
- [UI اختیاری Blade](ui.md)
- [Product / Commerce Schema](commerce-schema.md)
- [Artisan Commandها](commands.md)
- [تست‌ها و Hardening](testing-hardening.md)
- [مرجع خلاصه config](config-reference.md)

## وضعیت انتشار

نسخه فعلی در خانواده `v0.1.x` و pre-release است. برای تست و بازخورد اولیه آماده است، اما تا قبل از نسخه پایدار ممکن است بخش‌هایی از API تغییر کند.
