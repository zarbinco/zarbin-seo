# Holder Pageها

[بازگشت به فهرست فارسی](README.md)

Holder page یعنی صفحه‌ای که خودش یک مدل یا record است، اما نقش یک صفحه مادر یا landing را دارد؛ مثل `HomePage`، `ProductHolder`، `BlogHolder`، `CategoryHolder` یا `LandingPage`.

اگر holder شما مدل‌محور است، می‌تواند مثل هر مدل دیگری `Seoable` باشد. اگر فقط یک route ثابت دارد، می‌توانید آن را در `routes` config تعریف کنید.

## مثال ProductHolder

```php
use Illuminate\Database\Eloquent\Model;
use Zarbin\Seo\Concerns\HasSeo;
use Zarbin\Seo\Contracts\Seoable;

class ProductHolder extends Model implements Seoable
{
    use HasSeo;

    public function seoTitle(?string $locale = null): ?string
    {
        return $this->title ?: 'Products';
    }

    public function seoType(?string $locale = null): ?string
    {
        return 'CollectionPage';
    }
}
```

برای صفحه لیست محصولات، معمولا `CollectionPage` مناسب‌تر از `Product` است. Product schema برای صفحه جزئیات محصول استفاده می‌شود. ItemList schema برای لیست‌ها می‌تواند در فازهای بعدی اضافه شود.

## model-backed یا route-only؟

- اگر عنوان، description، slug یا تنظیمات صفحه در دیتابیس ذخیره می‌شود، holder مدل‌محور بهتر است.
- اگر صفحه فقط یک route ثابت مثل `home` یا `about` است، route-only mapping ساده‌تر است.
