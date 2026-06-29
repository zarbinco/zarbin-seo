<?php

declare(strict_types=1);

namespace Zarbin\Seo\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Zarbin\Seo\Models\SeoMeta;
use Zarbin\Seo\Repositories\SeoMetaRepository;

trait HasSeoMeta
{
    public function seoMetas(): MorphMany
    {
        return $this->morphMany(config('zarbin-seo.database.model', SeoMeta::class), 'seoable');
    }

    public function seoMetaForLocale(?string $locale = null): ?SeoMeta
    {
        $meta = $this->seoMetas()
            ->where('locale', SeoMeta::normalizedLocale($locale))
            ->first();

        return $meta instanceof SeoMeta ? $meta : null;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function saveSeoMeta(array $attributes, ?string $locale = null): SeoMeta
    {
        $meta = $this->seoMetas()->updateOrCreate(
            ['locale' => SeoMeta::normalizedLocale($locale)],
            (new SeoMetaRepository)->normalizeAttributes($attributes)
        );

        return $meta instanceof SeoMeta ? $meta : SeoMeta::query()->findOrFail($meta->getKey());
    }

    public function deleteSeoMeta(?string $locale = null): bool
    {
        $meta = $this->seoMetaForLocale($locale);

        if ($meta === null) {
            return false;
        }

        return (bool) $meta->delete();
    }
}
