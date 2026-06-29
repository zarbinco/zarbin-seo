<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Unit;

use Zarbin\Seo\Concerns\HasSeo;
use Zarbin\Seo\Contracts\LocalizableSeo;
use Zarbin\Seo\Contracts\Seoable;
use Zarbin\Seo\Repositories\SeoMetaRepository;
use Zarbin\Seo\Resolvers\SeoSourceResolver;
use Zarbin\Seo\Tests\Concerns\CreatesSeoMetaTable;
use Zarbin\Seo\Tests\TestCase;

final class SeoSourceResolverDatabaseOverrideTest extends TestCase
{
    use CreatesSeoMetaTable;

    public function test_model_database_override_beats_model_and_config_title(): void
    {
        $this->enableSeoMetaDatabase();
        $this->createSeoMetaTable();
        config()->set('zarbin-seo.models.'.DatabaseOverrideSeoableModel::class.'.title', 'configTitle');
        $model = new DatabaseOverrideSeoableModel('1');

        (new SeoMetaRepository)->saveForSource($model, ['title' => 'Database title']);

        $data = (new SeoSourceResolver)->resolve($model);

        $this->assertSame('Database title', $data->title);
    }

    public function test_route_database_override_beats_route_config_title(): void
    {
        $this->enableSeoMetaDatabase();
        $this->createSeoMetaTable();
        config()->set('zarbin-seo.routes', [
            'home' => [
                'title' => 'Configured home',
            ],
        ]);

        (new SeoMetaRepository)->saveForRoute('home', ['title' => 'Database home']);

        $data = (new SeoSourceResolver)->route('home');

        $this->assertSame('Database home', $data->title);
    }

    public function test_null_database_fields_do_not_erase_resolved_values(): void
    {
        $this->enableSeoMetaDatabase();
        $this->createSeoMetaTable();
        $model = new DatabaseOverrideSeoableModel('1');

        (new SeoMetaRepository)->saveForSource($model, [
            'title' => null,
            'image' => 'https://example.com/database.jpg',
        ]);

        $data = (new SeoSourceResolver)->resolve($model);

        $this->assertSame('Model title', $data->title);
        $this->assertSame('https://example.com/database.jpg', $data->image);
    }

    public function test_alternate_languages_are_preserved_after_database_override_merge(): void
    {
        $this->enableSeoMetaDatabase();
        $this->createSeoMetaTable();
        config()->set('zarbin-seo.localization.enabled', true);
        config()->set('zarbin-seo.localization.locales', ['fa', 'en']);
        config()->set('zarbin-seo.localization.default_locale', 'fa');
        $model = new DatabaseOverrideLocalizedModel('1');

        (new SeoMetaRepository)->saveForSource($model, ['title' => 'Database localized title'], 'fa');

        $data = (new SeoSourceResolver)->resolve($model, 'fa');

        $this->assertSame('Database localized title', $data->title);
        $this->assertSame([
            'fa' => 'https://example.com/fa/model',
            'en' => 'https://example.com/en/model',
        ], $data->alternateLanguages);
    }

    public function test_schema_type_and_robots_override_resolved_data(): void
    {
        $this->enableSeoMetaDatabase();
        $this->createSeoMetaTable();
        $model = new DatabaseOverrideSeoableModel('1');

        (new SeoMetaRepository)->saveForSource($model, [
            'schema_type' => 'Article',
            'robots' => ['noindex', 'nofollow'],
        ]);

        $data = (new SeoSourceResolver)->resolve($model);

        $this->assertSame('Article', $data->type);
        $this->assertSame(['noindex', 'nofollow'], $data->robots);
    }

    public function test_missing_table_does_not_throw_when_ignore_missing_table_is_true(): void
    {
        $this->enableSeoMetaDatabase();

        $data = (new SeoSourceResolver)->resolve(new DatabaseOverrideSeoableModel('1'));

        $this->assertSame('Model title', $data->title);
    }
}

final class DatabaseOverrideSeoableModel implements Seoable
{
    use HasSeo;

    public function __construct(private readonly string $key) {}

    public function getKey(): string
    {
        return $this->key;
    }

    public function seoTitle(?string $locale = null): ?string
    {
        return 'Model title';
    }

    public function seoType(?string $locale = null): ?string
    {
        return 'WebPage';
    }
}

final class DatabaseOverrideLocalizedModel implements LocalizableSeo
{
    public function __construct(private readonly string $key) {}

    public function getKey(): string
    {
        return $this->key;
    }

    public function seoLocales(): array
    {
        return ['fa', 'en'];
    }

    public function hasSeoLocale(string $locale): bool
    {
        return true;
    }

    public function seoUrlForLocale(string $locale): ?string
    {
        return "https://example.com/{$locale}/model";
    }
}
