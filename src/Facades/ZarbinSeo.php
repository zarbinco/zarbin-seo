<?php

declare(strict_types=1);

namespace Zarbin\Seo\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string name()
 * @method static string version()
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
