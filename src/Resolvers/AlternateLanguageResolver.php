<?php

declare(strict_types=1);

namespace Zarbin\Seo\Resolvers;

use Zarbin\Seo\Support\LocaleHelper;

final class AlternateLanguageResolver
{
    public function __construct(
        private readonly TranslationAvailabilityResolver $availability = new TranslationAvailabilityResolver,
        private readonly LocalizedUrlResolver $urls = new LocalizedUrlResolver,
    ) {}

    /**
     * @return array<string, string>
     */
    public function forSource(mixed $source, ?string $currentLocale = null): array
    {
        if (! $this->shouldResolve()) {
            return [];
        }

        $alternates = [];
        $defaultLocale = LocaleHelper::defaultLocale();
        $defaultUrl = $defaultLocale === null ? null : $this->urls->resolveForSource($source, $defaultLocale);

        foreach (LocaleHelper::configuredLocales() as $locale) {
            $available = $this->availability->isAvailable($source, $locale);
            $url = $available ? $this->urls->resolveForSource($source, $locale) : null;
            $url ??= $this->fallbackUrl($available, $defaultUrl);

            if ($url === null) {
                continue;
            }

            $alternates[$locale] = $url;
        }

        return $this->withXDefault($alternates, $defaultLocale, $defaultUrl);
    }

    /**
     * @param  array<int|string, mixed>  $parameters
     * @return array<string, string>
     */
    public function forRoute(string $routeName, array $parameters = [], ?string $currentLocale = null): array
    {
        if (! $this->shouldResolve()) {
            return [];
        }

        $alternates = [];
        $defaultLocale = LocaleHelper::defaultLocale();
        $defaultUrl = $defaultLocale === null ? null : $this->urls->resolveForRoute($routeName, $defaultLocale, $parameters);

        foreach (LocaleHelper::configuredLocales() as $locale) {
            $url = $this->urls->resolveForRoute($routeName, $locale, $parameters);
            $url ??= $this->fallbackUrl(true, $defaultUrl);

            if ($url === null) {
                continue;
            }

            $alternates[$locale] = $url;
        }

        return $this->withXDefault($alternates, $defaultLocale, $defaultUrl);
    }

    private function shouldResolve(): bool
    {
        return LocaleHelper::enabled()
            && LocaleHelper::shouldGenerateHreflang()
            && LocaleHelper::configuredLocales() !== [];
    }

    private function fallbackUrl(bool $available, ?string $defaultUrl): ?string
    {
        if ($available && LocaleHelper::missingTranslationStrategy() !== 'fallback') {
            return null;
        }

        if (! $available && LocaleHelper::missingTranslationStrategy() !== 'fallback') {
            return null;
        }

        return $defaultUrl;
    }

    /**
     * @param  array<string, string>  $alternates
     * @return array<string, string>
     */
    private function withXDefault(array $alternates, ?string $defaultLocale, ?string $defaultUrl): array
    {
        $xDefault = LocaleHelper::xDefault();

        if ($xDefault === null || $xDefault === false) {
            return $alternates;
        }

        if ($xDefault === true) {
            $url = $defaultLocale === null ? $defaultUrl : ($alternates[$defaultLocale] ?? $defaultUrl);

            if ($url !== null) {
                $alternates['x-default'] = $url;
            }

            return $alternates;
        }

        if (! is_scalar($xDefault)) {
            return $alternates;
        }

        $xDefault = trim((string) $xDefault);

        if ($xDefault === '') {
            return $alternates;
        }

        if (isset($alternates[$xDefault])) {
            $alternates['x-default'] = $alternates[$xDefault];

            return $alternates;
        }

        if ($this->looksLikeUrl($xDefault)) {
            $alternates['x-default'] = $xDefault;
        }

        return $alternates;
    }

    private function looksLikeUrl(string $url): bool
    {
        return str_starts_with($url, 'http://')
            || str_starts_with($url, 'https://')
            || str_starts_with($url, '/');
    }
}
