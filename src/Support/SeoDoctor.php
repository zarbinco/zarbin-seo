<?php

declare(strict_types=1);

namespace Zarbin\Seo\Support;

use Throwable;
use Zarbin\Seo\Repositories\SeoMetaRepository;

final class SeoDoctor
{
    /**
     * @var array<int, CommandResult>|null
     */
    private ?array $results = null;

    /**
     * @return array<int, CommandResult>
     */
    public function results(): array
    {
        if ($this->results !== null) {
            return $this->results;
        }

        return $this->results = [
            ...$this->coreResults(),
            ...$this->featureResults(),
            ...$this->localizationResults(),
            ...$this->databaseResults(),
            ...$this->uiResults(),
            ...$this->commerceResults(),
            ...$this->mappingResults(),
            ...$this->routePathResults(),
        ];
    }

    public function hasErrors(): bool
    {
        return $this->contains(fn (CommandResult $result): bool => $result->isError());
    }

    public function hasWarnings(): bool
    {
        return $this->contains(fn (CommandResult $result): bool => $result->isWarning());
    }

    /**
     * @return array<int, CommandResult>
     */
    private function coreResults(): array
    {
        $results = [];

        $results[] = $this->appBound('zarbin-seo')
            ? CommandResult::ok('Service binding', 'The zarbin-seo service is bound.')
            : CommandResult::error('Service binding', 'The zarbin-seo service binding was not found.');

        $config = $this->config('zarbin-seo');
        $results[] = is_array($config)
            ? CommandResult::ok('Configuration', 'The package configuration is loaded.')
            : CommandResult::error('Configuration', 'The package configuration is not loaded.');

        $appUrl = $this->config('app.url');
        $results[] = is_string($appUrl) && trim($appUrl) !== ''
            ? CommandResult::ok('Application URL', trim($appUrl))
            : CommandResult::warning('Application URL', 'config("app.url") is empty.');

        $defaultTitle = $this->config('zarbin-seo.defaults.title');
        $appName = $this->config('app.name');
        $results[] = $this->filled($defaultTitle) || $this->filled($appName)
            ? CommandResult::ok('Default title', 'A default title or app name is available.')
            : CommandResult::warning('Default title', 'Set zarbin-seo.defaults.title or app.name.');

        $robots = $this->config('zarbin-seo.defaults.robots');
        $results[] = $this->filled($robots)
            ? CommandResult::ok('Default robots', is_scalar($robots) ? (string) $robots : 'Configured.')
            : CommandResult::warning('Default robots', 'Set zarbin-seo.defaults.robots.');

        return $results;
    }

    /**
     * @return array<int, CommandResult>
     */
    private function featureResults(): array
    {
        return [
            $this->featureResult('Open Graph', 'open_graph'),
            $this->featureResult('Twitter cards', 'twitter'),
            $this->featureResult('Schema', 'schema'),
            $this->sitemapFeatureResult(),
            $this->sitemapAlternatesResult(),
            $this->robotsFeatureResult(),
        ];
    }

    /**
     * @return array<int, CommandResult>
     */
    private function localizationResults(): array
    {
        if (! LocaleHelper::enabled()) {
            return [
                CommandResult::info('Localization', 'Localization is disabled.'),
            ];
        }

        $results = [];
        $locales = LocaleHelper::configuredLocales();
        $results[] = $locales === []
            ? CommandResult::warning('Localization locales', 'Localization is enabled but no locales are configured.')
            : CommandResult::ok('Localization locales', count($locales).' locale(s) configured.', ['locales' => $locales]);

        $defaultLocale = LocaleHelper::defaultLocale();
        $results[] = $defaultLocale === null
            ? CommandResult::warning('Default locale', 'Localization is enabled but no default locale is configured.')
            : CommandResult::ok('Default locale', $defaultLocale);

        $strategy = (string) $this->config('zarbin-seo.localization.missing_translation_strategy', 'hide');
        $results[] = LocaleHelper::isValidStrategy($strategy)
            ? CommandResult::ok('Missing translation strategy', $strategy)
            : CommandResult::warning('Missing translation strategy', 'Invalid strategy; expected hide, fallback, or noindex.');

        return $results;
    }

    /**
     * @return array<int, CommandResult>
     */
    private function databaseResults(): array
    {
        $repository = new SeoMetaRepository;

        if (! (bool) $this->config('zarbin-seo.features.database_overrides', false)) {
            return [
                CommandResult::info('Database overrides', 'Feature is disabled.'),
            ];
        }

        if (! (bool) $this->config('zarbin-seo.database.enabled', false)) {
            return [
                CommandResult::warning('Database overrides', 'Feature flag is enabled but database.enabled is false.'),
            ];
        }

        try {
            $tableExists = $repository->tableExists();
        } catch (Throwable $exception) {
            return [
                CommandResult::warning('Database table', 'Could not check SEO meta table: '.$exception->getMessage()),
            ];
        }

        return [
            $tableExists
                ? CommandResult::ok('Database table', 'SEO meta table is available.')
                : CommandResult::warning('Database table', 'Database overrides are enabled but the SEO meta table is missing.'),
        ];
    }

    /**
     * @return array<int, CommandResult>
     */
    private function uiResults(): array
    {
        if (! UiConfig::enabled()) {
            return [
                CommandResult::ok('UI', 'Optional UI is disabled.'),
            ];
        }

        $results = [
            CommandResult::ok('UI', 'Optional UI is enabled.'),
        ];

        if (UiConfig::routeEnabled()) {
            $results[] = CommandResult::ok('UI routes', UiConfig::path(), [
                'middleware' => UiConfig::middleware(),
            ]);
        } else {
            $results[] = CommandResult::info('UI routes', 'Dedicated UI routes are disabled.');
        }

        if (UiConfig::databaseRequired() && ! (new SeoMetaRepository)->enabled()) {
            $results[] = CommandResult::warning('UI database', 'UI is enabled but database overrides are not enabled.');
        } elseif (UiConfig::databaseRequired() && ! (new SeoMetaRepository)->tableExists()) {
            $results[] = CommandResult::warning('UI database', 'UI is enabled but the SEO meta table is not ready.');
        }

        return $results;
    }

    /**
     * @return array<int, CommandResult>
     */
    private function commerceResults(): array
    {
        if (! (bool) $this->config('zarbin-seo.features.commerce', false)) {
            return [
                CommandResult::info('Commerce', 'Commerce schema feature is disabled.'),
            ];
        }

        $results = [];
        $results[] = CommerceConfig::enabled()
            ? CommandResult::ok('Commerce', 'Commerce schema feature is enabled.')
            : CommandResult::warning('Commerce', 'features.commerce is true but commerce.enabled is false.');

        $currency = CommerceConfig::defaultCurrency();
        $results[] = $currency === null
            ? CommandResult::info('Commerce currency', 'No default currency configured.')
            : CommandResult::ok('Commerce currency', $currency);

        return $results;
    }

    /**
     * @return array<int, CommandResult>
     */
    private function mappingResults(): array
    {
        $routes = $this->config('zarbin-seo.routes', []);
        $models = $this->config('zarbin-seo.models', []);

        $routes = is_array($routes) ? $routes : [];
        $models = is_array($models) ? $models : [];

        return [
            CommandResult::info('Configured routes', count($routes).' route(s) configured.', ['count' => count($routes)]),
            CommandResult::info('Configured models', count($models).' model mapping(s) configured.', ['count' => count($models)]),
        ];
    }

    /**
     * @return array<int, CommandResult>
     */
    private function routePathResults(): array
    {
        return [
            CommandResult::info('Sitemap path', (string) $this->config('zarbin-seo.sitemap.path', 'sitemap.xml')),
            CommandResult::info('Robots path', (string) $this->config('zarbin-seo.robots_txt.path', 'robots.txt')),
        ];
    }

    private function featureResult(string $label, string $key): CommandResult
    {
        return (bool) $this->config('zarbin-seo.features.'.$key, true)
            ? CommandResult::ok($label, 'Enabled.')
            : CommandResult::info($label, 'Disabled.');
    }

    private function sitemapFeatureResult(): CommandResult
    {
        $enabled = (bool) $this->config('zarbin-seo.features.sitemap', true)
            && (bool) $this->config('zarbin-seo.sitemap.enabled', true);
        $routeEnabled = (bool) $this->config('zarbin-seo.sitemap.route_enabled', true);

        return $enabled
            ? CommandResult::ok('Sitemap', $routeEnabled ? 'Enabled with public route enabled.' : 'Enabled with public route disabled.')
            : CommandResult::info('Sitemap', 'Disabled.');
    }

    private function sitemapAlternatesResult(): CommandResult
    {
        $enabled = (bool) $this->config('zarbin-seo.features.sitemap', true)
            && (bool) $this->config('zarbin-seo.sitemap.enabled', true);
        $routeEnabled = (bool) $this->config('zarbin-seo.sitemap.route_enabled', true);
        $includeAlternates = (bool) $this->config('zarbin-seo.sitemap.include_alternates', false);

        if (! $enabled || ! $includeAlternates) {
            return CommandResult::info('Sitemap alternates', 'Sitemap xhtml alternates are disabled.');
        }

        $message = 'Sitemap xhtml alternates are enabled. They are optional when hreflang is rendered in HTML head; some browser/local server combinations may display sitemap XML as plain text with xhtml alternates. Disable sitemap.include_alternates for cleaner sitemap XML if needed.';

        return $routeEnabled
            ? CommandResult::warning('Sitemap alternates', $message)
            : CommandResult::info('Sitemap alternates', $message);
    }

    private function robotsFeatureResult(): CommandResult
    {
        $enabled = (bool) $this->config('zarbin-seo.features.robots_txt', true)
            && (bool) $this->config('zarbin-seo.robots_txt.enabled', true);

        return $enabled
            ? CommandResult::ok('Robots.txt', 'Enabled.')
            : CommandResult::info('Robots.txt', 'Disabled.');
    }

    /**
     * @param  callable(CommandResult): bool  $callback
     */
    private function contains(callable $callback): bool
    {
        foreach ($this->results() as $result) {
            if ($callback($result)) {
                return true;
            }
        }

        return false;
    }

    private function appBound(string $abstract): bool
    {
        if (! function_exists('app')) {
            return false;
        }

        try {
            return app()->bound($abstract);
        } catch (Throwable) {
            return false;
        }
    }

    private function filled(mixed $value): bool
    {
        return ! ($value === null || $value === '' || $value === []);
    }

    private function config(?string $key = null, mixed $default = null): mixed
    {
        if (! function_exists('config')) {
            return $default;
        }

        try {
            return config($key, $default);
        } catch (Throwable) {
            return $default;
        }
    }
}
