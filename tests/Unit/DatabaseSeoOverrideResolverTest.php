<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Unit;

use Zarbin\Seo\Repositories\SeoMetaRepository;
use Zarbin\Seo\Resolvers\DatabaseSeoOverrideResolver;
use Zarbin\Seo\Tests\Concerns\CreatesSeoMetaTable;
use Zarbin\Seo\Tests\TestCase;

final class DatabaseSeoOverrideResolverTest extends TestCase
{
    use CreatesSeoMetaTable;

    public function test_returns_null_when_disabled(): void
    {
        $resolver = new DatabaseSeoOverrideResolver;

        $this->assertNull($resolver->resolveForSource(new DatabaseResolverSource('1')));
        $this->assertNull($resolver->resolveForRoute('home'));
    }

    public function test_returns_null_when_table_missing_and_ignore_missing_table_is_true(): void
    {
        $this->enableSeoMetaDatabase();

        $resolver = new DatabaseSeoOverrideResolver;

        $this->assertNull($resolver->resolveForSource(new DatabaseResolverSource('1')));
        $this->assertNull($resolver->resolveForRoute('home'));
    }

    public function test_resolves_source_override_into_seo_data(): void
    {
        $this->enableSeoMetaDatabase();
        $this->createSeoMetaTable();
        $source = new DatabaseResolverSource('1');

        (new SeoMetaRepository)->saveForSource($source, [
            'title' => 'Source database title',
            'schema_type' => 'Article',
        ], 'fa');

        $data = (new DatabaseSeoOverrideResolver)->resolveForSource($source, 'fa');

        $this->assertSame('Source database title', $data?->title);
        $this->assertSame('Article', $data?->type);
    }

    public function test_resolves_route_override_into_seo_data(): void
    {
        $this->enableSeoMetaDatabase();
        $this->createSeoMetaTable();

        (new SeoMetaRepository)->saveForRoute('home', [
            'title' => 'Route database title',
        ]);

        $data = (new DatabaseSeoOverrideResolver)->resolveForRoute('home');

        $this->assertSame('Route database title', $data?->title);
    }

    public function test_locale_specific_override_is_respected(): void
    {
        $this->enableSeoMetaDatabase();
        $this->createSeoMetaTable();
        $source = new DatabaseResolverSource('1');
        $repository = new SeoMetaRepository;

        $repository->saveForSource($source, ['title' => 'English title'], 'en');
        $repository->saveForSource($source, ['title' => 'Persian title'], 'fa');

        $resolver = new DatabaseSeoOverrideResolver;

        $this->assertSame('English title', $resolver->resolveForSource($source, 'en')?->title);
        $this->assertSame('Persian title', $resolver->resolveForSource($source, 'fa')?->title);
    }
}

final class DatabaseResolverSource
{
    public function __construct(private readonly string $key) {}

    public function getKey(): string
    {
        return $this->key;
    }
}
