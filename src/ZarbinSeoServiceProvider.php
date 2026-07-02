<?php

declare(strict_types=1);

namespace Zarbin\Seo;

use Illuminate\View\Compilers\BladeCompiler;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Zarbin\Seo\Console\Commands\CheckCommand;
use Zarbin\Seo\Console\Commands\DoctorCommand;
use Zarbin\Seo\Console\Commands\InstallCommand;
use Zarbin\Seo\Console\Commands\RobotsCommand;
use Zarbin\Seo\Console\Commands\SitemapCommand;
use Zarbin\Seo\View\Components\Alert;
use Zarbin\Seo\View\Components\Form;
use Zarbin\Seo\View\Components\Meta;
use Zarbin\Seo\View\Components\Preview;
use Zarbin\Seo\View\Components\Ui\Dashboard;
use Zarbin\Seo\View\Components\Ui\ModelForm;
use Zarbin\Seo\View\Components\Ui\Models;
use Zarbin\Seo\View\Components\Ui\Panel;
use Zarbin\Seo\View\Components\Ui\RouteForm;
use Zarbin\Seo\View\Components\Ui\Routes;

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

    public function packageBooted(): void
    {
        $this->registerUiComponents();

        $translations = dirname(__DIR__).'/lang';

        $this->loadTranslationsFrom($translations, 'zarbin-seo');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                $translations => function_exists('lang_path')
                    ? lang_path('vendor/zarbin-seo')
                    : resource_path('lang/vendor/zarbin-seo'),
            ], 'zarbin-seo-translations');
        }
    }

    private function registerUiComponents(): void
    {
        $this->callAfterResolving(BladeCompiler::class, function (BladeCompiler $blade): void {
            foreach ($this->uiComponents() as $alias => $component) {
                $blade->component($component, 'zarbin-seo::'.$alias);
            }

            if (! (bool) config('zarbin-seo.ui.components.global_aliases', false)) {
                return;
            }

            $prefix = $this->componentAliasPrefix();

            foreach ($this->uiComponents() as $alias => $component) {
                $blade->component($component, $prefix.'-'.$alias);
            }
        });
    }

    /**
     * @return array<string, class-string>
     */
    private function uiComponents(): array
    {
        return [
            'panel' => Panel::class,
            'dashboard' => Dashboard::class,
            'routes' => Routes::class,
            'models' => Models::class,
            'route-form' => RouteForm::class,
            'model-form' => ModelForm::class,
            'preview' => Preview::class,
            'alert' => Alert::class,
        ];
    }

    private function componentAliasPrefix(): string
    {
        $prefix = config('zarbin-seo.ui.components.alias_prefix', 'zarbin-seo');

        return is_scalar($prefix) && trim((string) $prefix) !== ''
            ? trim((string) $prefix)
            : 'zarbin-seo';
    }
}
