<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Zarbin\Seo\Repositories\SeoMetaRepository;
use Zarbin\Seo\Tests\Concerns\CreatesSeoMetaTable;
use Zarbin\Seo\Tests\TestCase;

final class FormComponentTest extends TestCase
{
    use CreatesSeoMetaTable;

    public function test_form_component_renders_expected_fields(): void
    {
        $html = Blade::render('<x-zarbin-seo::form :source="$model" locale="fa" />', [
            'model' => new FormComponentModel('1'),
        ]);

        $this->assertStringContainsString('name="seo[title]"', $html);
        $this->assertStringContainsString('name="seo[description]"', $html);
        $this->assertStringContainsString('name="seo[canonical]"', $html);
        $this->assertStringContainsString('name="seo[robots]"', $html);
        $this->assertStringContainsString('<select', $html);
    }

    public function test_form_component_renders_search_and_raw_html_previews(): void
    {
        config()->set('zarbin-seo.routes', [
            'home' => [
                'title' => 'Home title',
                'description' => 'Home description',
                'canonical' => 'https://example.test/home',
                'robots' => 'index, follow',
            ],
        ]);

        $html = Blade::render('<x-zarbin-seo::form source="home" />');

        $this->assertStringContainsString('Search result preview', $html);
        $this->assertStringContainsString('Raw HTML preview', $html);
        $this->assertStringContainsString('Home title', $html);
        $this->assertStringContainsString('https://example.test/home', $html);
        $this->assertStringContainsString('Home description', $html);
    }

    public function test_form_component_renders_persian_source_data_in_preview(): void
    {
        $this->app->setLocale('fa');
        $model = new FormComponentModel('1');
        $model->title = 'محصول سن‌ایچ';
        $model->description = 'توضیحات فارسی محصول';

        $html = Blade::render('<x-zarbin-seo::form :source="$model" locale="fa" />', [
            'model' => $model,
        ]);

        $this->assertStringContainsString('پیش‌نمایش نتیجه جستجو', $html);
        $this->assertStringContainsString('محصول سن‌ایچ', $html);
        $this->assertStringContainsString('توضیحات فارسی محصول', $html);
    }

    public function test_form_component_shows_resolved_values(): void
    {
        $model = new FormComponentModel('1');
        $model->title = 'Resolved title';

        $html = Blade::render('<x-zarbin-seo::form :source="$model" />', [
            'model' => $model,
        ]);

        $this->assertStringContainsString('value="Resolved title"', $html);
    }

    public function test_form_component_loads_database_override_values_when_available(): void
    {
        $this->enableSeoMetaDatabase();
        $this->createSeoMetaTable();
        $model = new FormComponentModel('1');

        (new SeoMetaRepository)->saveForSource($model, [
            'title' => 'Override title',
        ], 'fa');

        $html = Blade::render('<x-zarbin-seo::form :source="$model" locale="fa" />', [
            'model' => $model,
        ]);

        $this->assertStringContainsString('value="Override title"', $html);
    }

    public function test_form_component_preserves_robots_select_value(): void
    {
        $this->enableSeoMetaDatabase();
        $this->createSeoMetaTable();
        $model = new FormComponentModel('1');

        (new SeoMetaRepository)->saveForSource($model, [
            'robots' => ['noindex', 'follow'],
        ], 'fa');

        $html = Blade::render('<x-zarbin-seo::form :source="$model" locale="fa" />', [
            'model' => $model,
        ]);

        $this->assertStringContainsString('value="noindex, follow"', $html);
        $this->assertStringContainsString('Noindex, Follow', $html);
        $this->assertStringContainsString('selected', $html);
    }

    public function test_source_as_route_string_works(): void
    {
        config()->set('zarbin-seo.routes', [
            'home' => [
                'title' => 'Home title',
            ],
        ]);

        $html = Blade::render('<x-zarbin-seo::form source="home" locale="en" />');

        $this->assertStringContainsString('name="route" value="home"', $html);
        $this->assertStringContainsString('value="Home title"', $html);
    }

    public function test_standalone_form_renders_form_element(): void
    {
        $html = Blade::render('<x-zarbin-seo::form source="home" action="/save-seo" standalone />');

        $this->assertStringContainsString('<form method="POST" action="/save-seo">', $html);
    }

    public function test_non_standalone_form_renders_fields_without_parent_form(): void
    {
        $html = Blade::render('<x-zarbin-seo::form source="home" />');

        $this->assertStringNotContainsString('<form method=', $html);
        $this->assertStringContainsString('<fieldset', $html);
    }
}

final class FormComponentModel
{
    public string $title = 'Model title';

    public ?string $description = null;

    public function __construct(private readonly string $key) {}

    public function getKey(): string
    {
        return $this->key;
    }
}
