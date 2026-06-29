<?php

declare(strict_types=1);

namespace Zarbin\Seo\Contracts;

use Zarbin\Seo\Data\CommerceData;

interface CommerceSeo
{
    public function toCommerceData(?string $locale = null): CommerceData|array|null;
}
