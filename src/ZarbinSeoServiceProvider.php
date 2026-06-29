<?php

declare(strict_types=1);

namespace Zarbin\Seo;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Zarbin\Seo\View\Components\Meta;

final class ZarbinSeoServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('zarbin-seo')
            ->hasConfigFile()
            ->hasRoute('zarbin-seo')
            ->hasViews('zarbin-seo')
            ->hasViewComponent('zarbin-seo', Meta::class);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton('zarbin-seo', fn (): ZarbinSeo => new ZarbinSeo);
    }
}
