<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Unit;

use Illuminate\Support\Facades\Route;
use Zarbin\Seo\Concerns\HasSeo;
use Zarbin\Seo\Contracts\Seoable;
use Zarbin\Seo\Data\SeoData;
use Zarbin\Seo\Resolvers\SeoSourceResolver;
use Zarbin\Seo\Tests\TestCase;

final class SeoSourceResolverTest extends TestCase
{
    public function test_resolves_null_as_defaults(): void
    {
        config()->set('zarbin-seo.defaults.title', 'Default title');

        $this->assertSame('Default title', $this->resolver()->resolve()->title);
    }

    public function test_resolves_seo_data(): void
    {
        $data = SeoData::make(['title' => 'Existing']);

        $this->assertSame($data, $this->resolver()->resolve($data));
        $this->assertSame('fa', $this->resolver()->resolve($data, 'fa')->locale);
    }

    public function test_resolves_array(): void
    {
        config()->set('zarbin-seo.defaults.robots', 'index, follow');

        $data = $this->resolver()->resolve([
            'title' => 'Array title',
        ]);

        $this->assertSame('Array title', $data->title);
        $this->assertSame(['index', 'follow'], $data->robots);
    }

    public function test_resolves_seoable_object(): void
    {
        $model = new SourceSeoableModel;
        $model->title = 'Seoable title';

        $this->assertSame('Seoable title', $this->resolver()->resolve($model)->title);
    }

    public function test_resolves_normal_object(): void
    {
        $model = new SourcePlainModel;
        $model->name = 'Plain name';

        $this->assertSame('Plain name', $this->resolver()->resolve($model)->title);
    }

    public function test_resolves_string_as_route_name(): void
    {
        config()->set('zarbin-seo.routes.home', ['title' => 'Home']);

        $this->assertSame('Home', $this->resolver()->resolve('home')->title);
    }

    public function test_route_method_resolves_route(): void
    {
        Route::get('/home', fn (): string => 'home')->name('home');

        $data = $this->resolver()->route('home');

        $this->assertStringEndsWith('/home', $data->canonical);
    }

    private function resolver(): SeoSourceResolver
    {
        return new SeoSourceResolver;
    }
}

final class SourceSeoableModel implements Seoable
{
    use HasSeo;

    public ?string $title = null;

    public function seoTitle(?string $locale = null): ?string
    {
        return $this->title;
    }
}

final class SourcePlainModel
{
    public ?string $name = null;
}
