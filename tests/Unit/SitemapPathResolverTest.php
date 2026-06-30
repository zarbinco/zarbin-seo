<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Unit;

use Zarbin\Seo\Support\SitemapPathResolver;
use Zarbin\Seo\Tests\TestCase;

final class SitemapPathResolverTest extends TestCase
{
    public function test_default_and_index_paths_are_normalized(): void
    {
        config()->set('zarbin-seo.sitemap.path', '/feeds//sitemap.xml/');
        config()->set('zarbin-seo.sitemap.index_path', '/feeds//sitemap_index.xml/');

        $this->assertSame('feeds/sitemap.xml', SitemapPathResolver::defaultPath());
        $this->assertSame('feeds/sitemap_index.xml', SitemapPathResolver::indexPath());
    }

    public function test_localized_paths_are_normalized_and_malformed_entries_are_skipped(): void
    {
        config()->set('zarbin-seo.sitemap.localized_paths', [
            ' fa ' => '/sitemap-fa.xml',
            'en' => '///nested//sitemap-en.xml',
            '' => 'empty-locale.xml',
            'bad' => '',
            'array' => ['nope'],
        ]);

        $this->assertSame([
            'fa' => 'sitemap-fa.xml',
            'en' => 'nested/sitemap-en.xml',
        ], SitemapPathResolver::localizedPaths());
    }

    public function test_path_for_locale_uses_localized_path_or_default_path(): void
    {
        config()->set('zarbin-seo.sitemap.localized_paths', [
            'fa' => 'sitemap-fa.xml',
        ]);

        $this->assertSame('sitemap-fa.xml', SitemapPathResolver::pathForLocale('fa'));
        $this->assertSame('sitemap.xml', SitemapPathResolver::pathForLocale('en'));
        $this->assertSame('sitemap.xml', SitemapPathResolver::pathForLocale());
    }

    public function test_url_for_path_uses_configured_app_url_without_duplicate_slashes(): void
    {
        config()->set('app.url', 'https://example.com/');

        $this->assertSame('https://example.com/sitemap-fa.xml', SitemapPathResolver::urlForPath('/sitemap-fa.xml'));
    }

    public function test_url_for_path_uses_sitemap_base_url_when_configured(): void
    {
        config()->set('app.url', 'https://example.com');
        config()->set('zarbin-seo.sitemap.base_url', 'http://sunich.test');

        $this->assertSame('http://sunich.test/sitemap-fa.xml', SitemapPathResolver::urlForPath('sitemap-fa.xml'));
    }

    public function test_url_for_path_normalizes_sitemap_base_url_and_leading_path_slash(): void
    {
        config()->set('app.url', 'https://example.com');
        config()->set('zarbin-seo.sitemap.base_url', 'http://sunich.test/');

        $this->assertSame('http://sunich.test/sitemap-en.xml', SitemapPathResolver::urlForPath('/sitemap-en.xml'));
    }

    public function test_url_for_path_falls_back_to_url_helper_when_app_url_is_empty(): void
    {
        config()->set('app.url', '');

        $this->assertStringEndsWith('/sitemap.xml', SitemapPathResolver::urlForPath('sitemap.xml'));
    }

    public function test_localized_sitemap_entries_include_locale_path_and_loc(): void
    {
        config()->set('app.url', 'https://example.com');
        config()->set('zarbin-seo.sitemap.localized_paths', [
            'fa' => 'sitemap-fa.xml',
            'en' => 'sitemap-en.xml',
        ]);

        $this->assertSame([
            [
                'locale' => 'fa',
                'path' => 'sitemap-fa.xml',
                'loc' => 'https://example.com/sitemap-fa.xml',
            ],
            [
                'locale' => 'en',
                'path' => 'sitemap-en.xml',
                'loc' => 'https://example.com/sitemap-en.xml',
            ],
        ], SitemapPathResolver::localizedSitemapEntries());
    }

    public function test_malformed_localized_paths_config_returns_empty_array(): void
    {
        config()->set('zarbin-seo.sitemap.localized_paths', 'not-an-array');

        $this->assertSame([], SitemapPathResolver::localizedPaths());
    }
}
