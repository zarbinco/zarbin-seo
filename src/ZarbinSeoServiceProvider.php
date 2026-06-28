<?php

declare(strict_types=1);

namespace Zarbin\Seo;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class ZarbinSeoServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('zarbin-seo')
            ->hasConfigFile();
    }

    public function packageRegistered(): void
    {
        $this->app->singleton('zarbin-seo', fn (): ZarbinSeo => new ZarbinSeo);
    }
}
