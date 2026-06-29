<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Unit;

use Zarbin\Seo\Models\SeoMeta;
use Zarbin\Seo\Tests\Concerns\CreatesSeoMetaTable;
use Zarbin\Seo\Tests\TestCase;

final class ZarbinSeoDatabaseOverrideTest extends TestCase
{
    use CreatesSeoMetaTable;

    public function test_save_override_for_model_saves_when_enabled(): void
    {
        $this->enableSeoMetaDatabase();
        $this->createSeoMetaTable();
        $model = new ManagerOverrideSource('1');

        $meta = seo()->saveOverride($model, [
            'title' => 'Saved model title',
        ], 'fa');

        $this->assertInstanceOf(SeoMeta::class, $meta);
        $this->assertSame('Saved model title', $meta->title);
        $this->assertSame('fa', $meta->locale);
    }

    public function test_save_override_for_route_saves_when_enabled(): void
    {
        $this->enableSeoMetaDatabase();
        $this->createSeoMetaTable();

        $meta = seo()->saveOverride('home', [
            'title' => 'Saved route title',
        ], 'en');

        $this->assertInstanceOf(SeoMeta::class, $meta);
        $this->assertSame('route', $meta->seoable_type);
        $this->assertSame('home', $meta->seoable_id);
        $this->assertSame('en', $meta->locale);
    }

    public function test_delete_override_for_model_deletes_when_enabled(): void
    {
        $this->enableSeoMetaDatabase();
        $this->createSeoMetaTable();
        $model = new ManagerOverrideSource('1');

        seo()->saveOverride($model, ['title' => 'Saved model title']);

        $this->assertTrue(seo()->deleteOverride($model));
        $this->assertSame(0, SeoMeta::query()->count());
    }

    public function test_delete_override_for_route_deletes_when_enabled(): void
    {
        $this->enableSeoMetaDatabase();
        $this->createSeoMetaTable();

        seo()->saveOverride('home', ['title' => 'Saved route title']);

        $this->assertTrue(seo()->deleteOverride('home'));
        $this->assertSame(0, SeoMeta::query()->count());
    }

    public function test_for_model_includes_saved_override(): void
    {
        $this->enableSeoMetaDatabase();
        $this->createSeoMetaTable();
        $model = new ManagerOverrideSource('1');

        seo()->saveOverride($model, ['title' => 'Saved resolved title']);

        $this->assertSame('Saved resolved title', seo()->reset()->for($model)->get()->title);
    }

    public function test_route_includes_saved_route_override(): void
    {
        $this->enableSeoMetaDatabase();
        $this->createSeoMetaTable();
        config()->set('zarbin-seo.routes', [
            'home' => [
                'title' => 'Configured home title',
            ],
        ]);

        seo()->saveOverride('home', ['title' => 'Saved route resolved title']);

        $this->assertSame('Saved route resolved title', seo()->reset()->route('home')->get()->title);
    }

    public function test_save_override_returns_null_when_disabled(): void
    {
        $this->createSeoMetaTable();

        $this->assertNull(seo()->saveOverride(new ManagerOverrideSource('1'), ['title' => 'Nope']));
        $this->assertNull(seo()->saveOverride('home', ['title' => 'Nope']));
    }
}

final class ManagerOverrideSource
{
    public function __construct(private readonly string $key) {}

    public function getKey(): string
    {
        return $this->key;
    }

    public string $title = 'Normal title';
}
