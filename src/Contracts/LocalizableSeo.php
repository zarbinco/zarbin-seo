<?php

declare(strict_types=1);

namespace Zarbin\Seo\Contracts;

interface LocalizableSeo
{
    /**
     * @return array<int, string>
     */
    public function seoLocales(): array;

    public function hasSeoLocale(string $locale): bool;

    public function seoUrlForLocale(string $locale): ?string;
}
