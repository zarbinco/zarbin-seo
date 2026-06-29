# SEO برای routeها

[بازگشت به فهرست فارسی](README.md)

بعضی صفحه‌ها مدل ندارند: home، about، contact، pricing یا صفحه‌های ثابت دیگر. برای این‌ها می‌توانید از `config('zarbin-seo.routes')` استفاده کنید.

```php
'routes' => [
    'home' => [
        'title' => 'Home',
        'description' => 'Welcome to our website.',
        'canonical' => 'https://example.com',
        'schema' => 'WebPage',
        'sitemap' => true,
        'priority' => 1.0,
        'change_frequency' => 'daily',
    ],
],
```

## استفاده

```php
seo()->route('home')->render();
```

Route config می‌تواند برای canonical، schema type، sitemap priority و change frequency هم استفاده شود. اگر database overrides را فعال کنید، همین routeها بعدا می‌توانند از طریق دیتابیس یا UI اختیاری override شوند.
