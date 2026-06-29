# رندر کردن تگ‌های SEO

[بازگشت به فهرست فارسی](README.md)

خروجی اصلی پکیج برای `<head>` صفحه با `render()` تولید می‌شود.

```blade
<head>
    {!! seo()->render() !!}
</head>
```

## خروجی‌های جداگانه

اگر layout شما بخش‌بندی شده است، می‌توانید خروجی‌ها را جداگانه بگیرید.

```blade
{!! seo()->meta() !!}
{!! seo()->openGraph() !!}
{!! seo()->twitter() !!}
{!! seo()->jsonLd() !!}
{!! seo()->alternates() !!}
```

## Blade component

```blade
<x-zarbin-seo::meta />
<x-zarbin-seo::meta :source="$post" locale="fa" />
```

## نکته‌های ایمنی

خروجی HTML escape می‌شود. JSON-LD برای context اسکریپت با flagهای امن encode می‌شود و مسیرهای XML هم در sitemap escape می‌شوند. تست‌های hardening برای این مسیرها وجود دارد.

اگر از package SEO دیگری هم استفاده می‌کنید، تگ‌های title، description، canonical و social meta را دوبار render نکنید.
