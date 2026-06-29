# Artisan Commandها

[بازگشت به فهرست فارسی](README.md)

Commandهای Zarbin SEO برای نصب، بررسی، preview و export طراحی شده‌اند و safe by default هستند.

```bash
php artisan zarbin-seo:install
php artisan zarbin-seo:install --all
php artisan zarbin-seo:doctor
php artisan zarbin-seo:doctor --strict
php artisan zarbin-seo:check --route=home --render
php artisan zarbin-seo:sitemap --output=public/sitemap.xml
php artisan zarbin-seo:robots --output=public/robots.txt
```

## install

`zarbin-seo:install` به‌صورت پیش‌فرض config را publish می‌کند. Migration را فقط وقتی اجرا می‌کند که `--run-migrations` بدهید.

## doctor

`zarbin-seo:doctor` آماده بودن config، features، localization، database، UI، sitemap و robots را بررسی می‌کند. این command SEO score نیست.

## check

برای بررسی خروجی یک route یا مدل:

```bash
php artisan zarbin-seo:check --route=home
php artisan zarbin-seo:check --route=home --locale=fa --render
php artisan zarbin-seo:check --model="App\Models\Post" --id=1 --json
```

## sitemap و robots

این commandها فایل نمی‌نویسند مگر اینکه `--output` بدهید. هیچ crawling بزرگ یا queue job هم در این فاز انجام نمی‌شود.
مدل‌ها هم فقط وقتی query می‌شوند که خودتان `--model` و `--id` را صریح بدهید.
