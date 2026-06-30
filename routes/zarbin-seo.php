<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Zarbin\Seo\Http\Controllers\RobotsTxtController;
use Zarbin\Seo\Http\Controllers\SitemapController;
use Zarbin\Seo\Support\SitemapPathResolver;

if (
    config('zarbin-seo.features.sitemap', true)
    && config('zarbin-seo.sitemap.enabled', true)
    && config('zarbin-seo.sitemap.route_enabled', true)
) {
    Route::get(SitemapPathResolver::defaultPath(), SitemapController::class)
        ->name('zarbin-seo.sitemap');

    Route::get(SitemapPathResolver::indexPath(), [SitemapController::class, 'index'])
        ->name('zarbin-seo.sitemap.index');

    if (config('zarbin-seo.sitemap.localized_route_enabled', true)) {
        $reservedPaths = [
            SitemapPathResolver::defaultPath(),
            SitemapPathResolver::indexPath(),
        ];

        foreach (SitemapPathResolver::localizedPaths() as $locale => $path) {
            if (in_array($path, $reservedPaths, true)) {
                continue;
            }

            Route::get($path, [SitemapController::class, 'localized'])
                ->defaults('locale', $locale)
                ->name('zarbin-seo.sitemap.localized.'.preg_replace('/[^A-Za-z0-9_-]+/', '-', $locale));
        }
    }
}

if (config('zarbin-seo.features.robots_txt', true) && config('zarbin-seo.robots_txt.route_enabled', true)) {
    Route::get(config('zarbin-seo.robots_txt.path', 'robots.txt'), RobotsTxtController::class)
        ->name('zarbin-seo.robots');
}
