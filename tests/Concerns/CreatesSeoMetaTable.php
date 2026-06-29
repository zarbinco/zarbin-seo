<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Concerns;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

trait CreatesSeoMetaTable
{
    protected function createSeoMetaTable(?string $table = null): void
    {
        $table ??= config('zarbin-seo.database.table', 'seo_meta');

        Schema::dropIfExists($table);

        Schema::create($table, function (Blueprint $table): void {
            $table->id();
            $table->string('seoable_type');
            $table->string('seoable_id');
            $table->string('locale', 20)->default('');
            $table->text('title')->nullable();
            $table->text('description')->nullable();
            $table->text('canonical_url')->nullable();
            $table->json('robots')->nullable();
            $table->text('image')->nullable();
            $table->text('og_title')->nullable();
            $table->text('og_description')->nullable();
            $table->text('og_image')->nullable();
            $table->text('twitter_title')->nullable();
            $table->text('twitter_description')->nullable();
            $table->text('twitter_image')->nullable();
            $table->string('schema_type')->nullable();
            $table->json('extra')->nullable();
            $table->timestamps();

            $table->unique(['seoable_type', 'seoable_id', 'locale'], 'zarbin_seo_meta_unique');
            $table->index(['seoable_type', 'seoable_id'], 'zarbin_seo_meta_seoable_index');
            $table->index('locale', 'zarbin_seo_meta_locale_index');
        });
    }

    protected function enableSeoMetaDatabase(): void
    {
        config()->set('zarbin-seo.features.database_overrides', true);
        config()->set('zarbin-seo.database.enabled', true);
        config()->set('zarbin-seo.database.ignore_missing_table', true);
    }
}
