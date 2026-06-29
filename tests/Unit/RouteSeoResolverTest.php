<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Unit;

use Illuminate\Support\Facades\Route;
use Zarbin\Seo\Resolvers\RouteSeoResolver;
use Zarbin\Seo\Tests\TestCase;

final class RouteSeoResolverTest extends TestCase
{
    public function test_resolves_configured_route_title_description_and_type(): void
    {
        config()->set('zarbin-seo.routes.home', [
            'title' => 'Home',
            'description' => 'Welcome to our website',
            'schema' => 'WebPage',
        ]);

        $data = $this->resolver()->resolve('home');

        $this->assertSame('Home', $data->title);
        $this->assertSame('Welcome to our website', $data->description);
        $this->assertSame('WebPage', $data->type);
    }

    public function test_generates_canonical_url_for_existing_named_route(): void
    {
        Route::get('/about', fn (): string => 'about')->name('about');

        $data = $this->resolver()->resolve('about');

        $this->assertStringEndsWith('/about', $data->canonical);
    }

    public function test_explicit_canonical_beats_generated_route_url(): void
    {
        Route::get('/about', fn (): string => 'about')->name('about');
        config()->set('zarbin-seo.routes.about', [
            'canonical' => 'https://example.com/custom-about',
        ]);

        $data = $this->resolver()->resolve('about');

        $this->assertSame('https://example.com/custom-about', $data->canonical);
    }

    public function test_explicit_url_is_used_when_canonical_is_missing(): void
    {
        config()->set('zarbin-seo.routes.about', [
            'url' => 'https://example.com/about-url',
        ]);

        $data = $this->resolver()->resolve('about');

        $this->assertSame('https://example.com/about-url', $data->canonical);
    }

    public function test_route_generation_failure_does_not_throw(): void
    {
        $data = $this->resolver()->resolve('missing.route');

        $this->assertNull($data->canonical);
    }

    public function test_passed_parameters_override_config_parameters(): void
    {
        Route::get('/products/{product}', fn (string $product): string => $product)->name('products.show');
        config()->set('zarbin-seo.routes.products.page', [
            'route' => 'products.show',
            'parameters' => ['product' => 'old-product'],
        ]);

        $data = $this->resolver()->resolve('products.page', ['product' => 'new-product']);

        $this->assertStringEndsWith('/products/new-product', $data->canonical);
    }

    private function resolver(): RouteSeoResolver
    {
        return new RouteSeoResolver;
    }
}
