<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Unit;

use Zarbin\Seo\Concerns\HasSeo;
use Zarbin\Seo\Contracts\LocalizableSeo;
use Zarbin\Seo\Contracts\Seoable;
use Zarbin\Seo\Repositories\SeoMetaRepository;
use Zarbin\Seo\Resolvers\SeoSourceResolver;
use Zarbin\Seo\Tests\Concerns\CreatesSeoMetaTable;
use Zarbin\Seo\Tests\TestCase;

final class SeoSourceResolverCommerceTest extends TestCase
{
    use CreatesSeoMetaTable;

    protected function setUp(): void
    {
        parent::setUp();

        $this->enableCommerce();
    }

    public function test_resolving_product_model_adds_commerce_extra(): void
    {
        $data = (new SeoSourceResolver)->resolve(new SourceResolverCommerceProduct);

        $this->assertSame('SKU-1', $data->extra['commerce']['sku']);
        $this->assertSame(120000, $data->extra['commerce']['price']);
    }

    public function test_resolving_product_model_sets_type_product_when_type_missing(): void
    {
        $data = (new SeoSourceResolver)->resolve(new SourceResolverCommerceProduct);

        $this->assertSame('Product', $data->type);
    }

    public function test_explicit_collection_page_type_is_not_overwritten(): void
    {
        $data = (new SeoSourceResolver)->resolve(new SourceResolverCommerceCollection);

        $this->assertSame('CollectionPage', $data->type);
        $this->assertSame(1, $data->extra['commerce']['price']);
    }

    public function test_force_type_can_set_product(): void
    {
        config()->set('zarbin-seo.models.'.SourceResolverCommerceCollection::class.'.commerce.force_type', true);

        $data = (new SeoSourceResolver)->resolve(new SourceResolverCommerceCollection);

        $this->assertSame('Product', $data->type);
    }

    public function test_database_override_extra_is_preserved_when_commerce_is_added(): void
    {
        $this->enableSeoMetaDatabase();
        $this->createSeoMetaTable();
        $model = new SourceResolverCommerceProduct;

        (new SeoMetaRepository)->saveForSource($model, [
            'extra' => [
                'manual' => 'yes',
            ],
        ]);

        $data = (new SeoSourceResolver)->resolve($model);

        $this->assertSame('yes', $data->extra['manual']);
        $this->assertSame('SKU-1', $data->extra['commerce']['sku']);
    }

    public function test_alternate_languages_are_preserved_when_commerce_is_added(): void
    {
        config()->set('zarbin-seo.localization.enabled', true);
        config()->set('zarbin-seo.localization.locales', ['fa', 'en']);
        config()->set('zarbin-seo.localization.default_locale', 'fa');

        $data = (new SeoSourceResolver)->resolve(new SourceResolverCommerceLocalizedProduct, 'fa');

        $this->assertSame('SKU-L', $data->extra['commerce']['sku']);
        $this->assertSame([
            'fa' => 'https://example.com/fa/product',
            'en' => 'https://example.com/en/product',
        ], $data->alternateLanguages);
    }

    private function enableCommerce(): void
    {
        config()->set('zarbin-seo.features.commerce', true);
        config()->set('zarbin-seo.commerce.enabled', true);
    }
}

final class SourceResolverCommerceProduct
{
    public string $name = 'Product title';

    public int $price = 120000;

    public string $currency = 'irr';

    public string $sku = 'SKU-1';

    public function getKey(): string
    {
        return 'product-1';
    }
}

final class SourceResolverCommerceCollection implements Seoable
{
    use HasSeo;

    public string $name = 'Products';

    public int $price = 1;

    public function seoTitle(?string $locale = null): ?string
    {
        return 'Products';
    }

    public function seoType(?string $locale = null): ?string
    {
        return 'CollectionPage';
    }
}

final class SourceResolverCommerceLocalizedProduct implements LocalizableSeo
{
    public string $name = 'Localized product';

    public int $price = 1;

    public string $sku = 'SKU-L';

    public function seoLocales(): array
    {
        return ['fa', 'en'];
    }

    public function hasSeoLocale(string $locale): bool
    {
        return true;
    }

    public function seoUrlForLocale(string $locale): ?string
    {
        return "https://example.com/{$locale}/product";
    }
}
