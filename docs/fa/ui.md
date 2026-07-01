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

## inventory مدل‌ها

Inventory مدل‌ها به‌صورت پیش‌فرض غیرفعال است و فقط وقتی کار می‌کند که هم `ui.inventory.models.enabled` فعال باشد و هم برای هر مدل، `ui.enabled` و یک `source` صریح تعریف شده باشد.

پکیج هیچ model crawling خودکاری انجام نمی‌دهد و هرگز بدون تنظیم شما همه مدل‌ها را query نمی‌کند. برای مثال:

```php
'ui' => [
    'inventory' => [
        'models' => [
            'enabled' => true,
            'default_limit' => 50,
            'max_limit' => 200,
        ],
    ],
],

'models' => [
    App\Models\Product::class => [
        'title' => 'title',
        'description' => 'description',
        'ui' => [
            'enabled' => true,
            'label' => 'محصولات',
            'source' => fn () => App\Models\Product::query()->latest()->limit(50)->get(),
            'key' => 'id',
            'display' => ['title', 'name', 'slug'],
        ],
    ],
],
```

صفحه ویرایش مدل‌ها از همان database override استفاده می‌کند؛ یعنی override برای همان instance مدل و locale انتخاب‌شده ذخیره یا حذف می‌شود.

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

## ترجمه‌های UI

متن‌های UI از فایل‌های translation پکیج خوانده می‌شوند. ترجمه انگلیسی و فارسی به‌صورت پیش‌فرض داخل پکیج وجود دارد و برای تغییر متن‌ها یا اضافه کردن زبان‌های دیگر می‌توانید فایل‌ها را publish کنید:

```bash
php artisan vendor:publish --tag=zarbin-seo-translations
```

کلیدها با namespace پکیج استفاده می‌شوند؛ مثلا `zarbin-seo::ui.form.save`.

## پیش‌نمایش نتیجه جستجو

فرم ویرایش route و فرم قابل embed علاوه بر raw HTML، یک پیش‌نمایش شبیه نتیجه جستجو نشان می‌دهند: عنوان SEO، آدرس canonical و توضیحات متا. این پیش‌نمایش فقط برای کمک به ویرایش است و تضمین نمی‌کند گوگل دقیقا همان متن یا همان شکل را نمایش دهد.

raw HTML preview همچنان باقی است تا خروجی واقعی tagهای تولیدشده را ببینید.
