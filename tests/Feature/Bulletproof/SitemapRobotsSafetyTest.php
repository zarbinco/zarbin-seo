<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Feature\Bulletproof;

use Generator;
use Zarbin\Seo\Tests\TestCase;

final class SitemapRobotsSafetyTest extends TestCase
{
    public function test_disabled_sitemap_feature_returns_safe_empty_output(): void
    {
        config()->set('zarbin-seo.features.sitemap', false);

        $this->assertSame([], seo()->sitemapUrls());
        $this->assertStringContainsString('<urlset', seo()->sitemap());
        $this->get('/sitemap.xml')->assertOk()->assertSee('<urlset', false);
    }

    public function test_invalid_sitemap_sources_are_skipped_without_fatal_errors(): void
    {
        config()->set('zarbin-seo.models.'.SitemapRobotsSafetyModel::class, [
            'sitemap' => true,
            'sitemap_source' => [
                null,
                'not a source',
                new \stdClass,
                new SitemapRobotsSafetyModel('https://example.test/one'),
            ],
        ]);
        config()->set('zarbin-seo.models.'.ThrowingSitemapSourceModel::class, [
            'sitemap' => true,
            'sitemap_source' => static function (): Generator {
                throw new \RuntimeException('broken sitemap source');
                yield new ThrowingSitemapSourceModel;
            },
        ]);

        $urls = seo()->sitemapUrls();

        $this->assertCount(1, $urls);
        $this->assertSame('https://example.test/one', $urls[0]->loc);
    }

    public function test_duplicate_sitemap_urls_are_deduped(): void
    {
        config()->set('zarbin-seo.models.'.SitemapRobotsSafetyModel::class, [
            'sitemap' => true,
            'sitemap_source' => [
                new SitemapRobotsSafetyModel('https://example.test/same'),
                new SitemapRobotsSafetyModel('https://example.test/same'),
            ],
        ]);

        $this->assertCount(1, seo()->sitemapUrls());
    }

    public function test_sitemap_xml_escapes_sensitive_url_characters(): void
    {
        config()->set('zarbin-seo.routes', [
            'unsafe' => [
                'canonical' => 'https://example.test/?a=1&b=<x>',
                'sitemap' => true,
            ],
        ]);

        $this->assertStringContainsString('https://example.test/?a=1&amp;b=&lt;x&gt;', seo()->sitemap());
    }

    public function test_malformed_robots_config_is_normalized_safely(): void
    {
        config()->set('zarbin-seo.robots_txt.allow', ['/public', null, '/public']);
        config()->set('zarbin-seo.robots_txt.disallow', ['/admin', '', '/admin']);
        config()->set('zarbin-seo.robots_txt.sitemaps', ['https://example.test/sitemap.xml', null, 'https://example.test/sitemap.xml']);
        config()->set('zarbin-seo.robots_txt.user_agent', ['bad']);

        $robots = seo()->robotsTxt();

        $this->assertSame(1, substr_count($robots, 'Allow: /public'));
        $this->assertSame(1, substr_count($robots, 'Disallow: /admin'));
        $this->assertSame(1, substr_count($robots, 'Sitemap: https://example.test/sitemap.xml'));
        $this->assertStringContainsString('User-agent: *', $robots);
    }

    public function test_missing_app_url_does_not_break_robots_or_doctor(): void
    {
        config()->set('app.url', '');
        config()->set('zarbin-seo.robots_txt.sitemaps', []);

        $this->assertStringContainsString('User-agent: *', seo()->robotsTxt());
        $this->artisan('zarbin-seo:doctor')->assertExitCode(0);
    }
}

final class SitemapRobotsSafetyModel
{
    public function __construct(private readonly string $url) {}

    public function sitemapUrl(?string $locale = null): string
    {
        return $this->url;
    }
}

final class ThrowingSitemapSourceModel {}
