<?php

declare(strict_types=1);

use Zarbin\Seo\ZarbinSeo;

if (! function_exists('seo')) {
    function seo(): ZarbinSeo
    {
        return app('zarbin-seo');
    }
}
