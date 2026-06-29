<?php

declare(strict_types=1);

namespace Zarbin\Seo\Support;

use Throwable;

final class RouteUrl
{
    /**
     * @param  array<int|string, mixed>  $parameters
     */
    public static function make(string $name, array $parameters = []): ?string
    {
        if (! function_exists('route')) {
            return null;
        }

        try {
            return route($name, $parameters);
        } catch (Throwable) {
            return null;
        }
    }
}
