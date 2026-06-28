<?php

declare(strict_types=1);

namespace Zarbin\Seo\Contracts;

use Zarbin\Seo\Data\SeoData;

interface Seoable
{
    public function toSeoData(?string $locale = null): SeoData;
}
