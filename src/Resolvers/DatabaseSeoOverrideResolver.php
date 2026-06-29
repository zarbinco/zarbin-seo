<?php

declare(strict_types=1);

namespace Zarbin\Seo\Resolvers;

use Zarbin\Seo\Data\SeoData;
use Zarbin\Seo\Repositories\SeoMetaRepository;

final class DatabaseSeoOverrideResolver
{
    public function __construct(
        private readonly SeoMetaRepository $repository = new SeoMetaRepository,
    ) {}

    public function resolveForSource(object $source, ?string $locale = null): ?SeoData
    {
        return $this->repository->findForSource($source, $locale)?->toSeoData();
    }

    public function resolveForRoute(string $routeName, ?string $locale = null): ?SeoData
    {
        return $this->repository->findForRoute($routeName, $locale)?->toSeoData();
    }
}
