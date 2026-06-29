<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Unit;

use Illuminate\Support\Facades\Route;
use Zarbin\Seo\Concerns\HasSeo;
use Zarbin\Seo\Contracts\Seoable;
use Zarbin\Seo\Resolvers\ModelSeoResolver;
use Zarbin\Seo\Tests\TestCase;

final class ModelSeoResolverTest extends TestCase
{
    public function test_resolves_seoable_model_values(): void
    {
        $model = new SeoableResolverModel;
        $model->title = 'Seoable title';
        $model->description = 'Seoable description';

        $data = $this->resolver()->resolve($model);

        $this->assertSame('Seoable title', $data->title);
        $this->assertSame('Seoable description', $data->description);
    }

    public function test_resolves_config_mapped_model_values(): void
    {
        config()->set('zarbin-seo.models.'.MappedResolverModel::class, [
            'title' => 'seo_title',
            'description' => ['excerpt', 'content'],
            'image' => 'image_url',
            'type' => 'Article',
            'extra' => ['section' => 'section'],
        ]);

        $model = new MappedResolverModel;
        $model->seo_title = 'Mapped title';
        $model->content = 'Mapped content';
        $model->image_url = 'https://example.com/image.jpg';
        $model->section = 'news';

        $data = $this->resolver()->resolve($model);

        $this->assertSame('Mapped title', $data->title);
        $this->assertSame('Mapped content', $data->description);
        $this->assertSame('https://example.com/image.jpg', $data->image);
        $this->assertSame('Article', $data->type);
        $this->assertSame(['section' => 'news'], $data->extra);
    }

    public function test_seoable_values_beat_config_values_when_non_empty(): void
    {
        config()->set('zarbin-seo.models.'.SeoableResolverModel::class, [
            'title' => 'mapped_title',
            'description' => 'mapped_description',
        ]);

        $model = new SeoableResolverModel;
        $model->title = 'Seoable title';
        $model->description = 'Seoable description';
        $model->mapped_title = 'Mapped title';
        $model->mapped_description = 'Mapped description';

        $data = $this->resolver()->resolve($model);

        $this->assertSame('Seoable title', $data->title);
        $this->assertSame('Seoable description', $data->description);
    }

    public function test_config_fills_missing_seoable_values(): void
    {
        config()->set('zarbin-seo.models.'.PartialSeoableResolverModel::class, [
            'title' => 'mapped_title',
            'description' => 'mapped_description',
        ]);

        $model = new PartialSeoableResolverModel;
        $model->mapped_title = 'Mapped title';
        $model->mapped_description = 'Mapped description';

        $data = $this->resolver()->resolve($model);

        $this->assertSame('Mapped title', $data->title);
        $this->assertSame('Mapped description', $data->description);
    }

    public function test_common_attributes_work_without_config(): void
    {
        $model = new PlainResolverModel;
        $model->name = 'Plain name';
        $model->excerpt = 'Plain excerpt';

        $data = $this->resolver()->resolve($model);

        $this->assertSame('Plain name', $data->title);
        $this->assertSame('Plain excerpt', $data->description);
    }

    public function test_model_backed_holder_resolves_as_collection_page(): void
    {
        $holder = new ProductHolderResolverModel;
        $holder->title = '';

        $data = $this->resolver()->resolve($holder);

        $this->assertSame('Products', $data->title);
        $this->assertSame('CollectionPage', $data->type);
    }

    public function test_canonical_can_be_generated_from_route_config(): void
    {
        Route::get('/products/{product}', fn (string $product): string => $product)->name('products.show');

        config()->set('zarbin-seo.models.'.PlainResolverModel::class, [
            'route' => 'products.show',
            'route_key' => 'slug',
        ]);

        $model = new PlainResolverModel;
        $model->slug = 'apple';

        $data = $this->resolver()->resolve($model);

        $this->assertStringEndsWith('/products/apple', $data->canonical);
    }

    public function test_route_generation_failure_does_not_throw(): void
    {
        config()->set('zarbin-seo.models.'.PlainResolverModel::class, [
            'route' => 'missing.route',
            'route_key' => 'slug',
        ]);

        $model = new PlainResolverModel;
        $model->slug = 'apple';

        $data = $this->resolver()->resolve($model);

        $this->assertNull($data->canonical);
    }

    private function resolver(): ModelSeoResolver
    {
        return new ModelSeoResolver;
    }
}

final class SeoableResolverModel implements Seoable
{
    use HasSeo;

    public ?string $title = null;

    public ?string $description = null;

    public ?string $mapped_title = null;

    public ?string $mapped_description = null;

    public function seoTitle(?string $locale = null): ?string
    {
        return $this->title;
    }

    public function seoDescription(?string $locale = null): ?string
    {
        return $this->description;
    }
}

final class PartialSeoableResolverModel implements Seoable
{
    use HasSeo;

    public ?string $mapped_title = null;

    public ?string $mapped_description = null;
}

final class MappedResolverModel
{
    public ?string $seo_title = null;

    public ?string $excerpt = null;

    public ?string $content = null;

    public ?string $image_url = null;

    public ?string $section = null;
}

final class PlainResolverModel
{
    public ?string $name = null;

    public ?string $excerpt = null;

    public ?string $slug = null;
}

final class ProductHolderResolverModel implements Seoable
{
    use HasSeo;

    public ?string $title = null;

    public function seoTitle(?string $locale = null): ?string
    {
        return $this->title ?: 'Products';
    }

    public function seoType(?string $locale = null): ?string
    {
        return 'CollectionPage';
    }
}
