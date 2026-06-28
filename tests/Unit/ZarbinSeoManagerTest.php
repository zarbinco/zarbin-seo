<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Unit;

use Zarbin\Seo\Data\SeoData;
use Zarbin\Seo\Tests\TestCase;
use Zarbin\Seo\ZarbinSeo;

final class ZarbinSeoManagerTest extends TestCase
{
    public function test_manager_loads_defaults_from_config(): void
    {
        config()->set('app.name', 'Zarbin');
        config()->set('zarbin-seo.defaults.title', 'Default title');
        config()->set('zarbin-seo.defaults.description', 'Default description');
        config()->set('zarbin-seo.defaults.image', 'https://example.com/default.jpg');
        config()->set('zarbin-seo.defaults.separator', ' | ');
        config()->set('zarbin-seo.defaults.robots', 'index, follow');
        $this->app->setLocale('fa');

        $data = $this->manager()->reset()->get();

        $this->assertSame('Default title', $data->title);
        $this->assertSame('Default description', $data->description);
        $this->assertSame('https://example.com/default.jpg', $data->image);
        $this->assertSame(' | ', $data->separator);
        $this->assertSame(['index', 'follow'], $data->robots);
        $this->assertSame('Zarbin', $data->siteName);
        $this->assertSame('fa', $data->locale);
    }

    public function test_fluent_setters_update_seo_data(): void
    {
        $data = $this->manager()
            ->reset()
            ->title('Product')
            ->description('Product description')
            ->canonical('https://example.com/products/1')
            ->robots('noindex, follow')
            ->image('https://example.com/product.jpg')
            ->type('product')
            ->locale('en')
            ->siteName('Store')
            ->separator(' - ')
            ->extra(['sku' => 'SKU-1'])
            ->get();

        $this->assertSame('Product', $data->title);
        $this->assertSame('Product description', $data->description);
        $this->assertSame('https://example.com/products/1', $data->canonical);
        $this->assertSame(['noindex', 'follow'], $data->robots);
        $this->assertSame('https://example.com/product.jpg', $data->image);
        $this->assertSame('product', $data->type);
        $this->assertSame('en', $data->locale);
        $this->assertSame('Store', $data->siteName);
        $this->assertSame(' - ', $data->separator);
        $this->assertSame(['sku' => 'SKU-1'], $data->extra);
    }

    public function test_reset_restores_defaults(): void
    {
        config()->set('zarbin-seo.defaults.title', 'Default title');

        $manager = $this->manager()->reset();

        $manager->title('Changed');
        $this->assertSame('Changed', $manager->get()->title);

        $manager->reset();
        $this->assertSame('Default title', $manager->get()->title);
    }

    public function test_set_merges_array_data(): void
    {
        $data = $this->manager()
            ->reset()
            ->title('Existing')
            ->set([
                'description' => 'Merged description',
                'robots' => ['index', 'follow'],
            ])
            ->get();

        $this->assertSame('Existing', $data->title);
        $this->assertSame('Merged description', $data->description);
        $this->assertSame(['index', 'follow'], $data->robots);
    }

    public function test_set_merges_seo_data(): void
    {
        $incoming = SeoData::make([
            'title' => 'Incoming',
            'extra' => ['source' => 'dto'],
        ]);

        $data = $this->manager()
            ->reset()
            ->extra(['existing' => true])
            ->set($incoming)
            ->get();

        $this->assertSame('Incoming', $data->title);
        $this->assertSame(['existing' => true, 'source' => 'dto'], $data->extra);
    }

    public function test_description_setter_can_accept_cleaned_description(): void
    {
        $data = $this->manager()
            ->reset()
            ->description('<p>Hello&nbsp; world</p>')
            ->get();

        $this->assertSame('Hello world', $data->description);
    }

    private function manager(): ZarbinSeo
    {
        return $this->app->make('zarbin-seo');
    }
}
