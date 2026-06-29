<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Feature\Bulletproof;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Zarbin\Seo\Repositories\SeoMetaRepository;
use Zarbin\Seo\Tests\TestCase;

final class DatabaseOverrideSafetyTest extends TestCase
{
    public function test_disabled_database_overrides_do_not_query_database(): void
    {
        config()->set('zarbin-seo.features.database_overrides', false);
        config()->set('zarbin-seo.database.enabled', false);
        config()->set('zarbin-seo.routes', [
            'home' => ['title' => 'Home'],
        ]);

        $queries = 0;
        DB::listen(static function () use (&$queries): void {
            $queries++;
        });

        $model = new DatabaseOverrideSafetyModel('1');

        seo()->for($model)->get();
        seo()->route('home')->get();

        $this->assertNull(seo()->saveOverride($model, ['title' => 'Manual']));
        $this->assertFalse(seo()->deleteOverride($model));
        $this->assertSame(0, $queries);
    }

    public function test_feature_enabled_but_database_disabled_is_safe_and_does_not_query(): void
    {
        config()->set('zarbin-seo.features.database_overrides', true);
        config()->set('zarbin-seo.database.enabled', false);

        $queries = 0;
        DB::listen(static function () use (&$queries): void {
            $queries++;
        });

        $model = new DatabaseOverrideSafetyModel('2');

        $this->assertSame('DB model', seo()->for($model)->get()->title);
        $this->assertNull(seo()->saveOverride($model, ['title' => 'Manual']));
        $this->assertSame(0, $queries);
    }

    public function test_enabled_database_overrides_with_missing_table_fail_safely_when_ignored(): void
    {
        config()->set('zarbin-seo.features.database_overrides', true);
        config()->set('zarbin-seo.database.enabled', true);
        config()->set('zarbin-seo.database.ignore_missing_table', true);
        config()->set('zarbin-seo.routes', [
            'home' => ['title' => 'Home'],
        ]);

        Schema::dropIfExists('seo_meta');

        $model = new DatabaseOverrideSafetyModel('3');

        $this->assertSame('DB model', seo()->for($model)->get()->title);
        $this->assertSame('Home', seo()->route('home')->get()->title);
        $this->assertNull(seo()->saveOverride($model, ['title' => 'Manual']));
        $this->assertFalse(seo()->deleteOverride($model));
    }

    public function test_missing_table_behavior_is_explicit_when_not_ignored(): void
    {
        config()->set('zarbin-seo.features.database_overrides', true);
        config()->set('zarbin-seo.database.enabled', true);
        config()->set('zarbin-seo.database.ignore_missing_table', false);

        Schema::dropIfExists('seo_meta');

        $repository = new SeoMetaRepository;

        $this->assertFalse($repository->tableExists());
        $this->assertNull($repository->saveForRoute('home', ['title' => 'Manual']));
    }

    public function test_invalid_custom_database_table_name_does_not_crash_when_missing_tables_are_ignored(): void
    {
        config()->set('zarbin-seo.features.database_overrides', true);
        config()->set('zarbin-seo.database.enabled', true);
        config()->set('zarbin-seo.database.ignore_missing_table', true);
        config()->set('zarbin-seo.database.table', 'not_existing_seo_meta_table');

        $this->assertFalse((new SeoMetaRepository)->tableExists());
        $this->assertSame('DB model', seo()->for(new DatabaseOverrideSafetyModel('4'))->get()->title);
    }
}

final class DatabaseOverrideSafetyModel
{
    public string $title = 'DB model';

    public function __construct(private readonly string $key) {}

    public function getKey(): string
    {
        return $this->key;
    }
}
