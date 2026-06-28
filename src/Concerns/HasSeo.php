<?php

declare(strict_types=1);

namespace Zarbin\Seo\Concerns;

use Zarbin\Seo\Data\SeoData;

trait HasSeo
{
    public function toSeoData(?string $locale = null): SeoData
    {
        return SeoData::make([
            'title' => $this->seoTitle($locale),
            'description' => $this->seoDescription($locale),
            'canonical' => $this->seoCanonicalUrl($locale),
            'robots' => $this->seoRobots($locale),
            'image' => $this->seoImage($locale),
            'type' => $this->seoType($locale),
            'locale' => $this->seoLocale($locale),
            'siteName' => $this->seoSiteName($locale),
            'separator' => $this->seoSeparator($locale),
            'extra' => $this->seoExtra($locale),
        ]);
    }

    public function seoTitle(?string $locale = null): ?string
    {
        return null;
    }

    public function seoDescription(?string $locale = null): ?string
    {
        return null;
    }

    public function seoCanonicalUrl(?string $locale = null): ?string
    {
        return null;
    }

    public function seoRobots(?string $locale = null): string|array|null
    {
        return null;
    }

    public function seoImage(?string $locale = null): ?string
    {
        return null;
    }

    public function seoType(?string $locale = null): ?string
    {
        return null;
    }

    public function seoLocale(?string $locale = null): ?string
    {
        return $locale;
    }

    public function seoSiteName(?string $locale = null): ?string
    {
        return null;
    }

    public function seoSeparator(?string $locale = null): ?string
    {
        return null;
    }

    /**
     * @return array<string, mixed>
     */
    public function seoExtra(?string $locale = null): array
    {
        return [];
    }
}
