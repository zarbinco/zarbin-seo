<?php

declare(strict_types=1);

namespace Zarbin\Seo\Contracts;

use DateTimeInterface;

interface Sitemapable
{
    public function shouldBeInSitemap(?string $locale = null): bool;

    public function sitemapUrl(?string $locale = null): ?string;

    public function sitemapPriority(?string $locale = null): float|int|null;

    public function sitemapChangeFrequency(?string $locale = null): ?string;

    public function sitemapLastModified(?string $locale = null): DateTimeInterface|string|null;
}
