<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Unit;

use Illuminate\Support\ServiceProvider;
use Zarbin\Seo\Support\UiTranslator;
use Zarbin\Seo\Tests\TestCase;
use Zarbin\Seo\ZarbinSeoServiceProvider;

final class UiTranslationTest extends TestCase
{
    public function test_english_translation_exists_for_dashboard_title(): void
    {
        $this->app->setLocale('en');

        $this->assertSame('Zarbin SEO', __('zarbin-seo::ui.dashboard.title'));
    }

    public function test_persian_translation_exists_for_dashboard_title(): void
    {
        $this->app->setLocale('fa');

        $this->assertSame('داشبورد', __('zarbin-seo::ui.navigation.dashboard'));
    }

    public function test_field_labels_exist_in_both_locales(): void
    {
        $this->app->setLocale('en');
        $this->assertSame('SEO Title', __('zarbin-seo::ui.fields.title.label'));

        $this->app->setLocale('fa');
        $this->assertSame('عنوان SEO', __('zarbin-seo::ui.fields.title.label'));
    }

    public function test_missing_translation_helper_falls_back_to_humanized_key(): void
    {
        $this->assertSame('Missing Key', UiTranslator::get('missing.key'));
    }

    public function test_translation_publish_tag_is_registered(): void
    {
        $paths = ServiceProvider::pathsToPublish(
            ZarbinSeoServiceProvider::class,
            'zarbin-seo-translations',
        );

        $this->assertNotEmpty($paths);

        $publishedSources = array_map(
            static fn (string $path): string => str_replace('\\', '/', $path),
            array_keys($paths),
        );

        $this->assertContains(str_replace('\\', '/', dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'lang'), $publishedSources);
    }
}
