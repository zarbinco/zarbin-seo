# Product / Commerce Schema

[بازگشت به فهرست فارسی](README.md)

Commerce schema برای صفحه محصول است، نه برای تبدیل پکیج به ecommerce system. این قابلیت به WooCommerce، Cashier، Bagisto، Aimeos، Stripe یا پکیج فروشگاهی دیگری وابسته نیست.

به‌صورت پیش‌فرض غیرفعال است.

```php
'features' => [
    'commerce' => true,
],

'commerce' => [
    'enabled' => true,
    'default_currency' => 'IRR',
    'currency_per_locale' => [
        'fa' => 'IRR',
        'en' => 'USD',
    ],
],
```

## mapping مدل محصول

```php
'models' => [
    App\Models\Product::class => [
        'route' => 'products.show',
        'route_key' => 'slug',
        'type' => 'Product',
        'commerce' => [
            'enabled' => true,
            'name' => 'name',
            'description' => ['short_description', 'description'],
            'image' => 'image_url',
            'price' => 'price',
            'currency' => 'currency',
            'sku' => 'sku',
            'brand' => 'brand.name',
            'availability' => 'stock_status',
            'condition' => 'condition',
        ],
    ],
],
```

## قرارداد CommerceSeo و trait HasCommerceSeo

اگر می‌خواهید داده commerce را از خود مدل بدهید، از `CommerceSeo` استفاده کنید. `HasCommerceSeo` هم متدهای پیش‌فرض امن دارد تا فقط چیزهایی را override کنید که لازم دارید.

```php
use Illuminate\Database\Eloquent\Model;
use Zarbin\Seo\Concerns\HasCommerceSeo;
use Zarbin\Seo\Concerns\HasSeo;
use Zarbin\Seo\Contracts\CommerceSeo;
use Zarbin\Seo\Contracts\Seoable;
use Zarbin\Seo\Data\CommerceData;

class Product extends Model implements Seoable, CommerceSeo
{
    use HasSeo;
    use HasCommerceSeo;

    public function toCommerceData(?string $locale = null): CommerceData|array|null
    {
        return CommerceData::make([
            'name' => $this->name,
            'price' => $this->price,
            'currency' => 'IRR',
            'availability' => $this->stock > 0 ? 'in_stock' : 'out_of_stock',
            'brand' => $this->brand?->name,
            'sku' => $this->sku,
        ]);
    }
}
```

## fluent commerce

```php
seo()
    ->title('Product title')
    ->commerce([
        'price' => 120000,
        'currency' => 'IRR',
        'availability' => 'in_stock',
    ])
    ->render();
```

## فیلدهای مهم Offer

- price
- currency
- availability
- condition
- sku
- brand
- seller
- gtin / mpn

`ProductHolder` و صفحه‌های لیست معمولا باید `CollectionPage` بمانند. Product schema برای صفحه جزئیات محصول مناسب است؛ ItemList می‌تواند بعدا اضافه شود.
