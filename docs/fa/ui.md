# UI اختیاری Blade

[بازگشت به فهرست فارسی](README.md)

Zarbin SEO یک UI ساده Blade دارد، اما به‌صورت پیش‌فرض غیرفعال است. این UI وابسته به Livewire، Filament، Nova، Inertia، Tailwind یا Bootstrap نیست.

UI برای ویرایش رکوردهای database override استفاده می‌شود. اگر دیتابیس یا table آماده نباشد، باید هشدار دوستانه نشان دهد و crash نکند.

## config نمونه

```php
'features' => [
    'database_overrides' => true,
    'ui' => true,
],

'database' => [
    'enabled' => true,
],

'ui' => [
    'enabled' => true,
    'route_enabled' => true,
    'path' => 'admin/seo',
    'middleware' => ['web', 'auth'],
    'gate' => 'viewZarbinSeo',
    'completion' => [
        'required' => ['title', 'description', 'canonical', 'robots'],
        'recommended' => ['image'],
    ],
],
```

## route UI

Route UI فعلا برای مدیریت overrideهای routeهای تعریف‌شده در config مناسب است؛ مثلا `home` یا `about`.

لیست routeها می‌تواند وضعیت کامل یا ناقص بودن SEO را نشان دهد. علامت `✓` یعنی fieldهای required کامل هستند و علامت `×` یعنی حداقل یک field لازم کم است. fieldهای required و recommended از `ui.completion` قابل تنظیم هستند؛ recommendedها فقط warning می‌دهند و آیتم را ناقص نمی‌کنند.

field مربوط به robots به صورت dropdown با presetهای رایج نمایش داده می‌شود. گزینه‌ها از `ui.robots_options` می‌آیند و viewهای Blade همچنان قابل publish و سفارشی‌سازی هستند.

## فرم قابل embed

برای مدل‌ها، holderها یا routeها می‌توانید فرم را داخل admin panel خودتان قرار دهید.

```blade
<x-zarbin-seo::form :source="$post" locale="fa" />
```

```blade
<x-zarbin-seo::form source="home" locale="en" action="{{ route('admin.seo.save') }}" standalone />
```

## انتشار viewها

```bash
php artisan vendor:publish --tag=zarbin-seo-views
```

بعد از publish می‌توانید markup را مطابق پنل داخلی خودتان تغییر دهید.
