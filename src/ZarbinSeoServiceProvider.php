<?php

declare(strict_types=1);

namespace Zarbin\Seo;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Zarbin\Seo\Console\Commands\CheckCommand;
use Zarbin\Seo\Console\Commands\DoctorCommand;
use Zarbin\Seo\Console\Commands\InstallCommand;
use Zarbin\Seo\Console\Commands\RobotsCommand;
use Zarbin\Seo\Console\Commands\SitemapCommand;
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
            ->hasViewComponents('zarbin-seo', Meta::class, Form::class)
            ->hasCommands([
                InstallCommand::class,
                DoctorCommand::class,
                CheckCommand::class,
                SitemapCommand::class,
                RobotsCommand::class,
            ]);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton('zarbin-seo', fn (): ZarbinSeo => new ZarbinSeo);
    }
}
