<?php

declare(strict_types=1);

namespace Zarbin\Seo\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string name()
 * @method static string version()
 * @method static \Zarbin\Seo\Data\SeoData defaults()
 * @method static \Zarbin\Seo\ZarbinSeo reset()
 * @method static \Zarbin\Seo\Data\SeoData get()
 * @method static \Zarbin\Seo\ZarbinSeo set(array|\Zarbin\Seo\Data\SeoData $data)
 * @method static \Zarbin\Seo\ZarbinSeo title(?string $title)
 * @method static \Zarbin\Seo\ZarbinSeo description(?string $description)
 * @method static \Zarbin\Seo\ZarbinSeo canonical(?string $canonical)
 * @method static \Zarbin\Seo\ZarbinSeo robots(string|array|null $robots)
 * @method static \Zarbin\Seo\ZarbinSeo image(?string $image)
 * @method static \Zarbin\Seo\ZarbinSeo type(?string $type)
 * @method static \Zarbin\Seo\ZarbinSeo locale(?string $locale)
 * @method static \Zarbin\Seo\ZarbinSeo siteName(?string $siteName)
 * @method static \Zarbin\Seo\ZarbinSeo separator(?string $separator)
 * @method static \Zarbin\Seo\ZarbinSeo extra(array $extra)
 *
 * @see \Zarbin\Seo\ZarbinSeo
 */
final class ZarbinSeo extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'zarbin-seo';
    }
}
