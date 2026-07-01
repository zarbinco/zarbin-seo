<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Unit;

use Zarbin\Seo\Tests\TestCase;

final class SitemapConfigTest extends TestCase
{
    public function test_sitemap_alternates_are_disabled_by_default(): void
    {
        $this->assertFalse(config('zarbin-seo.sitemap.include_alternates'));
    }
}
