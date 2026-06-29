<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Zarbin\Seo\Tests\TestCase;

final class BladeComponentTest extends TestCase
{
    public function test_blade_component_renders_current_manager_seo_tags(): void
    {
        seo()
            ->reset()
            ->title('Blade title')
            ->description('Blade description')
            ->canonical('https://example.com/blade');

        $html = Blade::render('<x-zarbin-seo::meta />');

        $this->assertStringContainsString('Blade title', $html);
        $this->assertStringContainsString('<meta name="description" content="Blade description">', $html);
        $this->assertStringContainsString('<link rel="canonical" href="https://example.com/blade">', $html);
    }

    public function test_blade_component_renders_source_seo_tags(): void
    {
        $model = new BladeComponentSourceModel;
        $model->name = 'Source title';
        $model->excerpt = 'Source description';

        $html = Blade::render('<x-zarbin-seo::meta :source="$model" />', [
            'model' => $model,
        ]);

        $this->assertStringContainsString('Source title', $html);
        $this->assertStringContainsString('Source description', $html);
    }
}

final class BladeComponentSourceModel
{
    public ?string $name = null;

    public ?string $excerpt = null;
}
