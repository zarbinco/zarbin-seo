<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Unit;

use Zarbin\Seo\Tests\TestCase;

final class ZarbinSeoRenderingTest extends TestCase
{
    public function test_rendering_current_manager_data_works(): void
    {
        $html = seo()
            ->reset()
            ->title('About')
            ->description('About description')
            ->canonical('https://example.com/about')
            ->siteName('Example')
            ->separator('|')
            ->render();

        $this->assertStringContainsString('<title>About | Example</title>', $html);
        $this->assertStringContainsString('<meta name="description" content="About description">', $html);
        $this->assertStringContainsString('<link rel="canonical" href="https://example.com/about">', $html);
    }

    public function test_segmented_manager_rendering_methods_work(): void
    {
        seo()
            ->reset()
            ->title('About')
            ->description('About description')
            ->canonical('https://example.com/about')
            ->image('https://example.com/about.jpg');

        $this->assertStringContainsString('<meta name="description" content="About description">', seo()->meta());
        $this->assertStringContainsString('<meta property="og:title" content="About">', seo()->openGraph());
        $this->assertStringContainsString('<meta name="twitter:card" content="summary_large_image">', seo()->twitter());
        $this->assertStringContainsString('"name":"About"', seo()->jsonLd());
    }

    public function test_rendering_after_for_model_works(): void
    {
        $model = new RenderingSourceModel;
        $model->name = 'Model title';
        $model->excerpt = 'Model description';

        $html = seo()->reset()->for($model)->render();

        $this->assertStringContainsString('Model title', $html);
        $this->assertStringContainsString('Model description', $html);
    }

    public function test_reset_still_restores_defaults(): void
    {
        config()->set('zarbin-seo.defaults.title', 'Default title');

        $manager = seo()->reset()->title('Changed title');

        $this->assertStringContainsString('Changed title', $manager->titleTag());
        $this->assertStringContainsString('Default title', $manager->reset()->titleTag());
    }
}

final class RenderingSourceModel
{
    public ?string $name = null;

    public ?string $excerpt = null;
}
