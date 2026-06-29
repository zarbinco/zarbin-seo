<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Unit;

use Illuminate\Support\Facades\Schema;
use Zarbin\Seo\Models\SeoMeta;
use Zarbin\Seo\Repositories\SeoMetaRepository;
use Zarbin\Seo\Tests\Concerns\CreatesSeoMetaTable;
use Zarbin\Seo\Tests\TestCase;

final class SeoMetaRepositoryTest extends TestCase
{
    use CreatesSeoMetaTable;

    public function test_enabled_is_false_by_default(): void
    {
        $this->assertFalse((new SeoMetaRepository)->enabled());
    }

    public function test_enabled_is_true_only_when_feature_and_database_are_enabled(): void
    {
        $repository = new SeoMetaRepository;

        config()->set('zarbin-seo.features.database_overrides', true);
        $this->assertFalse($repository->enabled());

        config()->set('zarbin-seo.features.database_overrides', false);
        config()->set('zarbin-seo.database.enabled', true);
        $this->assertFalse($repository->enabled());

        config()->set('zarbin-seo.features.database_overrides', true);
        $this->assertTrue($repository->enabled());
    }

    public function test_table_exists_returns_false_safely_when_table_missing(): void
    {
        Schema::dropIfExists('seo_meta');

        $this->assertFalse((new SeoMetaRepository)->tableExists());
    }

    public function test_save_and_find_for_source_create_and_update_locale_record(): void
    {
        $this->enableSeoMetaDatabase();
        $this->createSeoMetaTable();
        $repository = new SeoMetaRepository;
        $source = new RepositorySource('source-1');

        $created = $repository->saveForSource($source, [
            'title' => 'Initial title',
            'canonical' => 'https://example.com/initial',
        ], 'fa');

        $this->assertInstanceOf(SeoMeta::class, $created);
        $this->assertSame('Initial title', $repository->findForSource($source, 'fa')?->title);

        $updated = $repository->saveForSource($source, [
            'title' => 'Updated title',
        ], 'fa');

        $this->assertSame($created?->getKey(), $updated?->getKey());
        $this->assertSame('Updated title', $repository->findForSource($source, 'fa')?->title);
        $this->assertSame(1, SeoMeta::query()->count());
    }

    public function test_save_and_find_for_route_create_route_record(): void
    {
        $this->enableSeoMetaDatabase();
        $this->createSeoMetaTable();
        $repository = new SeoMetaRepository;

        $repository->saveForRoute('home', [
            'title' => 'Home override',
        ], 'en');

        $meta = $repository->findForRoute('home', 'en');

        $this->assertInstanceOf(SeoMeta::class, $meta);
        $this->assertSame(SeoMeta::routeType(), $meta->seoable_type);
        $this->assertSame('home', $meta->seoable_id);
        $this->assertSame('en', $meta->locale);
    }

    public function test_delete_for_source_and_route_delete_records(): void
    {
        $this->enableSeoMetaDatabase();
        $this->createSeoMetaTable();
        $repository = new SeoMetaRepository;
        $source = new RepositorySource('source-1');

        $repository->saveForSource($source, ['title' => 'Source'], 'fa');
        $repository->saveForRoute('home', ['title' => 'Route'], 'fa');

        $this->assertTrue($repository->deleteForSource($source, 'fa'));
        $this->assertTrue($repository->deleteForRoute('home', 'fa'));
        $this->assertNull($repository->findForSource($source, 'fa'));
        $this->assertNull($repository->findForRoute('home', 'fa'));
    }

    public function test_normalize_attributes_supports_aliases_and_removes_unsupported_keys(): void
    {
        $attributes = (new SeoMetaRepository)->normalizeAttributes([
            'title' => '0',
            'canonical' => 'https://example.com/canonical',
            'type' => 'Article',
            'schema' => 'Ignored because type wins',
            'robots' => 'index, follow, index',
            'open_graph' => [
                'title' => 'OG title',
                'description' => 'OG description',
                'image' => 'https://example.com/og.jpg',
            ],
            'twitter' => [
                'title' => 'Twitter title',
                'description' => 'Twitter description',
                'image' => 'https://example.com/twitter.jpg',
            ],
            'extra' => ['score' => 0],
            'unsupported' => 'drop me',
            'description' => null,
        ]);

        $this->assertSame('0', $attributes['title']);
        $this->assertSame('https://example.com/canonical', $attributes['canonical_url']);
        $this->assertSame('Article', $attributes['schema_type']);
        $this->assertSame(['index', 'follow'], $attributes['robots']);
        $this->assertSame('OG title', $attributes['og_title']);
        $this->assertSame('OG description', $attributes['og_description']);
        $this->assertSame('https://example.com/og.jpg', $attributes['og_image']);
        $this->assertSame('Twitter title', $attributes['twitter_title']);
        $this->assertSame('Twitter description', $attributes['twitter_description']);
        $this->assertSame('https://example.com/twitter.jpg', $attributes['twitter_image']);
        $this->assertSame(['score' => 0], $attributes['extra']);
        $this->assertArrayNotHasKey('unsupported', $attributes);
        $this->assertArrayNotHasKey('description', $attributes);
    }
}

final class RepositorySource
{
    public function __construct(private readonly int|string $key) {}

    public function getKey(): int|string
    {
        return $this->key;
    }
}
