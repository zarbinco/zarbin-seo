<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Zarbin\Seo\Data\SeoData;
use Zarbin\Seo\Tests\Concerns\CreatesSeoMetaTable;
use Zarbin\Seo\Tests\TestCase;

final class UiBladeComponentsTest extends TestCase
{
    use CreatesSeoMetaTable;

    protected UiBladeComponentsModel $model;

    protected function setUp(): void
    {
        parent::setUp();

        $this->enableSeoMetaDatabase();
        $this->createSeoMetaTable();
        $this->model = new UiBladeComponentsModel;

        config()->set('zarbin-seo.features.ui', true);
        config()->set('zarbin-seo.ui.enabled', true);
        config()->set('zarbin-seo.routes', [
            'home' => [
                'title' => 'Home title',
                'description' => 'Home description',
                'canonical' => 'https://example.test/home',
                'robots' => 'index, follow',
            ],
        ]);
        config()->set('zarbin-seo.ui.inventory.models.enabled', true);
        config()->set('zarbin-seo.models', [
            UiBladeComponentsModel::class => [
                'title' => 'title',
                'description' => 'description',
                'canonical' => 'canonical',
                'robots' => 'robots',
                'ui' => [
                    'enabled' => true,
                    'label' => 'Products',
                    'source' => [$this->model],
                    'key' => 'id',
                    'display' => ['title'],
                ],
            ],
        ]);
    }

    public function test_panel_component_renders(): void
    {
        $html = Blade::render('<x-zarbin-seo::panel locale="en" />');

        $this->assertStringContainsString('data-zarbin-seo-component="panel"', $html);
        $this->assertStringContainsString('data-zarbin-seo-component="dashboard"', $html);
        $this->assertStringContainsString('data-zarbin-seo-component="routes"', $html);
        $this->assertStringContainsString('data-zarbin-seo-component="models"', $html);
    }

    public function test_dashboard_component_renders(): void
    {
        $html = Blade::render('<x-zarbin-seo::dashboard />');

        $this->assertStringContainsString('data-zarbin-seo-component="dashboard"', $html);
        $this->assertStringContainsString('Manage SEO readiness and route overrides.', $html);
    }

    public function test_routes_component_renders_route_inventory(): void
    {
        $html = Blade::render('<x-zarbin-seo::routes />');

        $this->assertStringContainsString('data-zarbin-seo-component="routes"', $html);
        $this->assertStringContainsString('home', $html);
        $this->assertStringContainsString('Home title', $html);
    }

    public function test_models_component_renders_model_inventory_when_enabled(): void
    {
        $html = Blade::render('<x-zarbin-seo::models />');

        $this->assertStringContainsString('data-zarbin-seo-component="models"', $html);
        $this->assertStringContainsString('Products', $html);
        $this->assertStringContainsString('Component Product', $html);
    }

    public function test_route_form_component_renders_seo_form(): void
    {
        $html = Blade::render('<x-zarbin-seo::route-form route="home" locale="fa" />');

        $this->assertStringContainsString('data-zarbin-seo-component="route-form"', $html);
        $this->assertStringContainsString('name="route" value="home"', $html);
        $this->assertStringContainsString('name="seo[title]"', $html);
        $this->assertStringContainsString('Home title', $html);
    }

    public function test_model_form_component_renders_seo_form(): void
    {
        $html = Blade::render('<x-zarbin-seo::model-form :source="$model" locale="fa" />', [
            'model' => $this->model,
        ]);

        $this->assertStringContainsString('data-zarbin-seo-component="model-form"', $html);
        $this->assertStringContainsString('name="model" value="'.e(UiBladeComponentsModel::class).'"', $html);
        $this->assertStringContainsString('name="seo[title]"', $html);
        $this->assertStringContainsString('Component Product', $html);
    }

    public function test_components_render_translated_labels(): void
    {
        $this->app->setLocale('fa');

        $html = Blade::render('<x-zarbin-seo::routes locale="fa" />');

        $this->assertStringContainsString(__('zarbin-seo::ui.components.routes_title'), $html);
        $this->assertStringContainsString(__('zarbin-seo::ui.routes.status'), $html);
    }

    public function test_components_are_rtl_for_fa_locale_and_ltr_for_en_locale(): void
    {
        $rtl = Blade::render('<x-zarbin-seo::routes locale="fa" />');
        $ltr = Blade::render('<x-zarbin-seo::routes locale="en" />');

        $this->assertStringContainsString('dir="rtl"', $rtl);
        $this->assertStringContainsString('lang="fa"', $rtl);
        $this->assertStringContainsString('dir="ltr"', $ltr);
        $this->assertStringContainsString('lang="en"', $ltr);
    }

    public function test_canonical_url_and_raw_html_preview_are_ltr(): void
    {
        $html = Blade::render('<x-zarbin-seo::route-form route="home" locale="fa" />');

        $this->assertStringContainsString('id="zarbin-seo-canonical"', $html);
        $this->assertStringContainsString('class="zarbin-seo-snippet-url" dir="ltr"', $html);
        $this->assertStringContainsString('class="zarbin-seo-preview" readonly dir="ltr"', $html);
    }

    public function test_preview_and_alert_components_render(): void
    {
        $html = Blade::render(
            '<x-zarbin-seo::preview :data="$seoData" /><x-zarbin-seo::alert type="warning">Careful</x-zarbin-seo::alert>',
            ['seoData' => SeoData::make(['title' => 'Preview title'])]
        );

        $this->assertStringContainsString('data-zarbin-seo-component="preview"', $html);
        $this->assertStringContainsString('Preview title', $html);
        $this->assertStringContainsString('data-zarbin-seo-component="alert"', $html);
        $this->assertStringContainsString('Careful', $html);
    }
}

final class UiBladeComponentsModel
{
    public string $id = '1';

    public string $title = 'Component Product';

    public string $description = 'Component description';

    public string $canonical = 'https://example.test/component-product';

    public string $robots = 'index, follow';

    public function getKey(): string
    {
        return $this->id;
    }
}
