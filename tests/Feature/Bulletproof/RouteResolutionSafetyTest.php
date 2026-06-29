<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Feature\Bulletproof;

use Illuminate\Support\Facades\Route;
use Zarbin\Seo\Tests\TestCase;

final class RouteResolutionSafetyTest extends TestCase
{
    public function test_route_config_pointing_to_missing_named_route_does_not_throw(): void
    {
        config()->set('zarbin-seo.routes', [
            'missing.route' => [
                'title' => 'Missing route',
                'route' => 'does.not.exist',
                'sitemap' => true,
            ],
        ]);

        $data = seo()->route('missing.route')->get();

        $this->assertSame('Missing route', $data->title);
        $this->assertNull($data->canonical);
        $this->assertStringContainsString('<title>Missing route', seo()->render());
    }

    public function test_model_config_with_missing_route_does_not_break_resolution_sitemap_or_check_command(): void
    {
        $model = new RouteResolutionSafetyModel('post-1');

        config()->set('zarbin-seo.models.'.RouteResolutionSafetyModel::class, [
            'route' => 'posts.missing',
            'route_key' => 'slug',
            'sitemap' => true,
            'sitemap_items' => [$model],
        ]);
        config()->set('zarbin-seo.routes', [
            'home' => [
                'title' => 'Home',
                'route' => 'home.missing',
            ],
        ]);

        $this->assertSame('Safe post', seo()->for($model)->get()->title);
        $this->assertSame([], seo()->sitemapUrls());
        $this->artisan('zarbin-seo:check', ['--route' => 'home'])->assertExitCode(0);
    }

    public function test_missing_route_parameters_are_swallowed_safely(): void
    {
        Route::get('/posts/{post}', static fn (): string => 'ok')->name('posts.show');

        config()->set('zarbin-seo.routes', [
            'posts.show' => [
                'title' => 'Post',
                'route' => 'posts.show',
                'sitemap' => true,
            ],
        ]);

        $data = seo()->route('posts.show')->get();

        $this->assertSame('Post', $data->title);
        $this->assertNull($data->canonical);
        $this->artisan('zarbin-seo:check', ['--route' => 'posts.show'])->assertExitCode(0);
    }

    public function test_invalid_route_only_sitemap_entry_is_skipped(): void
    {
        config()->set('zarbin-seo.routes', [
            'invalid.sitemap' => [
                'route' => 'invalid.sitemap',
                'sitemap' => true,
            ],
        ]);

        $this->assertSame([], seo()->sitemapUrls());
    }

    public function test_missing_localized_routes_are_skipped_for_hreflang(): void
    {
        config()->set('zarbin-seo.localization.enabled', true);
        config()->set('zarbin-seo.localization.locales', ['fa', 'en']);
        config()->set('zarbin-seo.localization.default_locale', 'fa');
        config()->set('zarbin-seo.routes', [
            'home' => [
                'title' => 'Home',
                'localized_routes' => [
                    'fa' => 'fa.home.missing',
                    'en' => 'en.home.missing',
                ],
            ],
        ]);

        $data = seo()->route('home', [], 'fa')->get();

        $this->assertSame([], $data->alternateLanguages);
    }
}

final class RouteResolutionSafetyModel
{
    public string $title = 'Safe post';

    public string $slug = 'post-1';

    public function __construct(public readonly string $key) {}

    public function getKey(): string
    {
        return $this->key;
    }
}
