<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Unit;

use DateTimeImmutable;
use Zarbin\Seo\Data\SitemapUrl;
use Zarbin\Seo\Tests\TestCase;

final class SitemapUrlTest extends TestCase
{
    public function test_creates_sitemap_url_from_array(): void
    {
        $url = SitemapUrl::make([
            'loc' => ' https://example.com/post ',
            'lastmod' => ' 2026-01-01 ',
            'changefreq' => 'Weekly',
            'priority' => 0.8,
            'alternates' => [' fa ' => ' https://example.com/fa/post '],
        ]);

        $this->assertSame('https://example.com/post', $url->loc);
        $this->assertSame('2026-01-01', $url->normalizedLastModified());
        $this->assertSame('weekly', $url->changefreq);
        $this->assertSame('0.8', $url->normalizedPriority());
        $this->assertSame(['fa' => 'https://example.com/fa/post'], $url->alternates);
    }

    public function test_normalizes_priority(): void
    {
        $this->assertSame('1.0', SitemapUrl::make(['loc' => 'https://example.com', 'priority' => 4])->normalizedPriority());
        $this->assertSame('0.0', SitemapUrl::make(['loc' => 'https://example.com', 'priority' => -2])->normalizedPriority());
        $this->assertNull(SitemapUrl::make(['loc' => 'https://example.com', 'priority' => 'nope'])->normalizedPriority());
    }

    public function test_validates_changefreq(): void
    {
        $this->assertSame('daily', SitemapUrl::make(['loc' => 'https://example.com', 'changefreq' => 'Daily'])->changefreq);
        $this->assertNull(SitemapUrl::make(['loc' => 'https://example.com', 'changefreq' => 'sometimes'])->changefreq);
    }

    public function test_formats_lastmod_from_datetime_interface(): void
    {
        $date = new DateTimeImmutable('2026-01-02T03:04:05+00:00');

        $this->assertSame(
            '2026-01-02T03:04:05+00:00',
            SitemapUrl::make(['loc' => 'https://example.com', 'lastmod' => $date])->normalizedLastModified()
        );
    }

    public function test_supports_alternates(): void
    {
        $url = SitemapUrl::make([
            'loc' => 'https://example.com',
            'alternates' => [
                'fa' => 'https://example.com/fa',
                '' => 'https://example.com/empty',
                'en' => '',
            ],
        ]);

        $this->assertTrue($url->hasAlternates());
        $this->assertSame(['fa' => 'https://example.com/fa'], $url->alternates);
        $this->assertSame([
            'fa' => 'https://example.com/fa',
            'en' => 'https://example.com/en',
        ], $url->withAlternates(['en' => 'https://example.com/en'])->alternates);
    }

    public function test_to_array_works(): void
    {
        $url = SitemapUrl::make([
            'loc' => 'https://example.com',
            'priority' => 0.6,
            'extra' => ['kind' => 'route'],
        ]);

        $this->assertSame([
            'loc' => 'https://example.com',
            'lastmod' => null,
            'changefreq' => null,
            'priority' => 0.6,
            'alternates' => [],
            'extra' => ['kind' => 'route'],
        ], $url->toArray());
    }
}
