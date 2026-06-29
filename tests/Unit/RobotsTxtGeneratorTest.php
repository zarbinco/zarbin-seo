<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Unit;

use Zarbin\Seo\Generators\RobotsTxtGenerator;
use Zarbin\Seo\Tests\TestCase;

final class RobotsTxtGeneratorTest extends TestCase
{
    public function test_renders_user_agent(): void
    {
        $robots = (new RobotsTxtGenerator)->render();

        $this->assertStringContainsString('User-agent: *', $robots);
    }

    public function test_renders_allow_and_disallow(): void
    {
        config()->set('zarbin-seo.robots_txt.allow', ['/']);
        config()->set('zarbin-seo.robots_txt.disallow', ['/admin']);

        $robots = (new RobotsTxtGenerator)->render();

        $this->assertStringContainsString('Allow: /', $robots);
        $this->assertStringContainsString('Disallow: /admin', $robots);
    }

    public function test_renders_configured_sitemaps(): void
    {
        config()->set('zarbin-seo.robots_txt.sitemaps', [
            'https://example.com/sitemap.xml',
            'https://example.com/sitemap.xml',
        ]);

        $robots = (new RobotsTxtGenerator)->render();

        $this->assertSame(1, substr_count($robots, 'Sitemap: https://example.com/sitemap.xml'));
    }

    public function test_auto_adds_sitemap_when_sitemap_enabled_and_no_sitemaps_configured(): void
    {
        config()->set('app.url', 'https://example.com');

        $robots = (new RobotsTxtGenerator)->render();

        $this->assertStringContainsString('Sitemap: https://example.com/sitemap_index.xml', $robots);
    }

    public function test_removes_duplicate_lines(): void
    {
        config()->set('zarbin-seo.robots_txt.allow', ['/', '/']);
        config()->set('zarbin-seo.robots_txt.disallow', ['/admin', '/admin']);

        $robots = (new RobotsTxtGenerator)->render();

        $this->assertSame(1, substr_count($robots, 'Allow: /'));
        $this->assertSame(1, substr_count($robots, 'Disallow: /admin'));
    }

    public function test_returns_empty_string_when_disabled(): void
    {
        config()->set('zarbin-seo.robots_txt.enabled', false);

        $this->assertSame('', (new RobotsTxtGenerator)->render());
    }
}
