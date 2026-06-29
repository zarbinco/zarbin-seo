<?php

declare(strict_types=1);

namespace Zarbin\Seo;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Zarbin\Seo\View\Components\Form;
use Zarbin\Seo\View\Components\Meta;

final class ZarbinSeoServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('zarbin-seo')
            ->hasConfigFile()
            ->hasMigration('create_zarbin_seo_meta_table')
            ->hasRoute('zarbin-seo')
            ->hasRoute('zarbin-seo-ui')
            ->hasViews('zarbin-seo')
            ->hasViewComponents('zarbin-seo', Meta::class, Form::class);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton('zarbin-seo', fn (): ZarbinSeo => new ZarbinSeo);
    }
}
