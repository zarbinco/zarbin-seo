<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests;

use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase as Orchestra;
use Zarbin\Seo\ZarbinSeoServiceProvider;

abstract class TestCase extends Orchestra
{
    /**
     * @param  Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            ZarbinSeoServiceProvider::class,
        ];
    }
}
