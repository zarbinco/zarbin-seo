<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Unit;

use Zarbin\Seo\Resolvers\FallbackSeoResolver;
use Zarbin\Seo\Tests\TestCase;

final class FallbackSeoResolverTest extends TestCase
{
    public function test_resolves_defaults(): void
    {
        config()->set('app.name', 'Zarbin');
        config()->set('zarbin-seo.defaults.title', 'Default title');
        config()->set('zarbin-seo.defaults.description', 'Default description');
        config()->set('zarbin-seo.defaults.image', 'https://example.com/default.jpg');
        config()->set('zarbin-seo.defaults.robots', 'index, follow');
        config()->set('zarbin-seo.defaults.separator', ' | ');

        $data = $this->resolver()->resolve();

        $this->assertSame('Default title', $data->title);
        $this->assertSame('Default description', $data->description);
        $this->assertSame('https://example.com/default.jpg', $data->image);
        $this->assertSame(['index', 'follow'], $data->robots);
        $this->assertSame(' | ', $data->separator);
        $this->assertSame('Zarbin', $data->siteName);
    }

    public function test_resolves_common_title_and_name_fields(): void
    {
        $this->assertSame('Source title', $this->resolver()->resolve(['title' => 'Source title'])->title);
        $this->assertSame('Source name', $this->resolver()->resolve(['name' => 'Source name'])->title);
    }

    public function test_resolves_app_name_when_default_title_is_missing(): void
    {
        config()->set('app.name', 'Zarbin App');
        config()->set('zarbin-seo.defaults.title', null);

        $this->assertSame('Zarbin App', $this->resolver()->resolve()->title);
    }

    public function test_resolves_description_from_excerpt(): void
    {
        $data = $this->resolver()->resolve(['excerpt' => '<p>Short&nbsp; excerpt</p>']);

        $this->assertSame('Short excerpt', $data->description);
    }

    public function test_resolves_description_from_content_and_limits_it(): void
    {
        config()->set('zarbin-seo.defaults.description_limit', 20);

        $data = $this->resolver()->resolve([
            'content' => '<p>This content is long enough to be shortened safely.</p>',
        ]);

        $this->assertSame('This content is long', $data->description);
    }

    public function test_resolves_image_from_common_image_fields(): void
    {
        $data = $this->resolver()->resolve(['cover_image_url' => 'https://example.com/cover.jpg']);

        $this->assertSame('https://example.com/cover.jpg', $data->image);
    }

    public function test_applies_locale(): void
    {
        $data = $this->resolver()->resolve(null, 'fa');

        $this->assertSame('fa', $data->locale);
    }

    private function resolver(): FallbackSeoResolver
    {
        return new FallbackSeoResolver;
    }
}
