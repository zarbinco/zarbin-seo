<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Unit;

use Illuminate\Support\Facades\Route;
use Zarbin\Seo\Contracts\CommerceSeo;
use Zarbin\Seo\Data\CommerceData;
use Zarbin\Seo\Resolvers\CommerceDataResolver;
use Zarbin\Seo\Tests\TestCase;

final class CommerceDataResolverTest extends TestCase
{
    public function test_returns_null_when_disabled(): void
    {
        $this->assertNull((new CommerceDataResolver)->resolve(new ResolverProductModel));
    }

    public function test_resolves_commerce_seo_contract(): void
    {
        $this->enableCommerce();

        $data = (new CommerceDataResolver)->resolve(new ResolverCommerceContractProduct, 'fa');

        $this->assertSame('Contract product', $data?->name);
        $this->assertSame(1200, $data?->price);
    }

    public function test_resolves_to_commerce_data_method(): void
    {
        $this->enableCommerce();

        $data = (new CommerceDataResolver)->resolve(new ResolverCommerceMethodProduct);

        $this->assertSame('Method product', $data?->name);
        $this->assertSame('SKU-METHOD', $data?->sku);
    }

    public function test_resolves_config_mapped_fields(): void
    {
        $this->enableCommerce();
        config()->set('zarbin-seo.models.'.ResolverProductModel::class.'.commerce', [
            'enabled' => true,
            'name' => 'title',
            'description' => ['short_description', 'description'],
            'image' => 'image_url',
            'price' => 'sale_price',
            'currency' => 'IRR',
            'sku' => 'sku',
            'brand' => 'brand.name',
            'availability' => 'stock_status',
        ]);

        $product = new ResolverProductModel;
        $product->title = 'Mapped product';
        $product->short_description = '<p>Mapped description</p>';
        $product->image_url = 'https://example.com/product.jpg';
        $product->sale_price = 99;
        $product->sku = 'SKU-1';
        $product->stock_status = 'in_stock';
        $product->brand = new ResolverBrand('Zarbin');

        $data = (new CommerceDataResolver)->resolve($product, 'fa');

        $this->assertSame('Mapped product', $data?->name);
        $this->assertSame('Mapped description', $data?->description);
        $this->assertSame('https://example.com/product.jpg', $data?->image);
        $this->assertSame(99, $data?->price);
        $this->assertSame('IRR', $data?->currency);
        $this->assertSame('Zarbin', $data?->brand);
    }

    public function test_resolves_common_product_fields_without_config(): void
    {
        $this->enableCommerce();
        $product = new ResolverProductModel;
        $product->name = 'Common product';
        $product->description = 'Common description';
        $product->price = '0';
        $product->currency = 'usd';
        $product->brand = new ResolverBrand('Common brand');
        $product->category = new ResolverCategory('Shoes');

        $data = (new CommerceDataResolver)->resolve($product);

        $this->assertSame('Common product', $data?->name);
        $this->assertSame('0', $data?->price);
        $this->assertSame('USD', $data?->currency);
        $this->assertSame('Common brand', $data?->brand);
        $this->assertSame('Shoes', $data?->category);
    }

    public function test_resolves_description_fallback_and_cleans_html(): void
    {
        $this->enableCommerce();
        $product = new ResolverProductModel;
        $product->name = 'Product';
        $product->content = '<p>Hello&nbsp;world</p>';

        $data = (new CommerceDataResolver)->resolve($product);

        $this->assertSame('Hello world', $data?->description);
    }

    public function test_resolves_currency_from_locale_config(): void
    {
        $this->enableCommerce();
        config()->set('zarbin-seo.commerce.default_currency', 'irr');
        config()->set('zarbin-seo.commerce.currency_per_locale.en', 'usd');

        $data = (new CommerceDataResolver)->resolve(['name' => 'Array product'], 'en');

        $this->assertSame('USD', $data?->currency);
    }

    public function test_resolves_url_through_localized_url_resolver(): void
    {
        $this->enableCommerce();
        Route::get('/{locale}/products/{product}', fn (): string => 'product')->name('products.show');
        config()->set('zarbin-seo.localization.route_parameter', 'locale');
        config()->set('zarbin-seo.models.'.ResolverProductModel::class, [
            'route' => 'products.show',
            'route_parameters' => ['product' => 'slug'],
        ]);

        $product = new ResolverProductModel;
        $product->name = 'Product';
        $product->slug = 'shoe';

        $data = (new CommerceDataResolver)->resolve($product, 'fa');

        $this->assertStringEndsWith('/fa/products/shoe', $data?->url);
    }

    public function test_returns_null_when_no_identity_and_no_offer(): void
    {
        $this->enableCommerce();

        $this->assertNull((new CommerceDataResolver)->resolve(new ResolverProductModel));
    }

    private function enableCommerce(): void
    {
        config()->set('zarbin-seo.features.commerce', true);
        config()->set('zarbin-seo.commerce.enabled', true);
    }
}

final class ResolverCommerceContractProduct implements CommerceSeo
{
    public function toCommerceData(?string $locale = null): CommerceData|array|null
    {
        return CommerceData::make([
            'name' => 'Contract product',
            'price' => 1200,
        ]);
    }
}

final class ResolverCommerceMethodProduct
{
    public function toCommerceData(?string $locale = null): array
    {
        return [
            'name' => 'Method product',
            'sku' => 'SKU-METHOD',
        ];
    }
}

final class ResolverProductModel
{
    public ?string $name = null;

    public ?string $title = null;

    public ?string $description = null;

    public ?string $short_description = null;

    public ?string $content = null;

    public ?string $image_url = null;

    public int|string|null $price = null;

    public int|string|null $sale_price = null;

    public ?string $currency = null;

    public ?string $sku = null;

    public ?string $stock_status = null;

    public ?ResolverBrand $brand = null;

    public ?ResolverCategory $category = null;

    public ?string $slug = null;
}

final class ResolverBrand
{
    public function __construct(public string $name) {}
}

final class ResolverCategory
{
    public function __construct(public string $name) {}
}
