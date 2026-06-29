<?php

declare(strict_types=1);

namespace Zarbin\Seo\Concerns;

use DateTimeInterface;
use Zarbin\Seo\Data\SeoData;
use Zarbin\Seo\Support\AttributeReader;
use Zarbin\Seo\Support\LocaleHelper;

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

    /**
     * @return array<int, string>
     */
    public function seoLocales(): array
    {
        return LocaleHelper::configuredLocales();
    }

    public function hasSeoLocale(string $locale): bool
    {
        return true;
    }

    public function seoUrlForLocale(string $locale): ?string
    {
        return $this->seoCanonicalUrl($locale);
    }

    public function shouldBeInSitemap(?string $locale = null): bool
    {
        return true;
    }

    public function sitemapUrl(?string $locale = null): ?string
    {
        return $this->seoCanonicalUrl($locale);
    }

    public function sitemapUrlForLocale(string $locale): ?string
    {
        return $this->sitemapUrl($locale) ?? $this->seoUrlForLocale($locale);
    }

    public function sitemapPriority(?string $locale = null): float|int|null
    {
        return null;
    }

    public function sitemapChangeFrequency(?string $locale = null): ?string
    {
        return null;
    }

    public function sitemapLastModified(?string $locale = null): DateTimeInterface|string|null
    {
        $updatedAt = AttributeReader::get($this, 'updated_at');

        return $updatedAt instanceof DateTimeInterface || is_string($updatedAt) ? $updatedAt : null;
    }
}
