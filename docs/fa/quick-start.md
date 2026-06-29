# شروع سریع

[بازگشت به فهرست فارسی](README.md)

ساده‌ترین روش استفاده این است که خروجی SEO را در layout اصلی پروژه داخل `<head>` قرار دهید.

```blade
<head>
    {!! seo()->render() !!}
</head>
```

## استفاده در controller

در صفحه‌ای که یک مدل مثل `Post` دارید، SEO را قبل از return کردن view resolve کنید.

```php
public function show(Post $post)
{
    seo()->for($post);

    return view('posts.show', compact('post'));
}
```

## استفاده fluent

برای صفحه‌های ساده یا مواقعی که داده SEO را مستقیم می‌سازید:

```php
seo()
    ->title('About Us')
    ->description('Learn more about our company.')
    ->canonical(route('about'))
    ->render();
```

## صفحه route-only

اگر صفحه مدل ندارد، از route mapping استفاده کنید:

```php
seo()->route('home')->render();
```

## Blade component

برای layout یا صفحه‌هایی که می‌خواهید source را مستقیم به component بدهید:

```blade
<x-zarbin-seo::meta />
<x-zarbin-seo::meta :source="$post" locale="fa" />
```

اگر همزمان از پکیج SEO دیگری استفاده می‌کنید، مراقب باشید تگ‌های duplicate در `<head>` تولید نشود.
