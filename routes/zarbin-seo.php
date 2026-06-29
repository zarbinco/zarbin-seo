<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Zarbin\Seo\Http\Controllers\RobotsTxtController;
use Zarbin\Seo\Http\Controllers\SitemapController;

if (config('zarbin-seo.features.sitemap', true) && config('zarbin-seo.sitemap.route_enabled', true)) {
    Route::get(config('zarbin-seo.sitemap.path', 'sitemap.xml'), SitemapController::class)
        ->name('zarbin-seo.sitemap');

    Route::get(config('zarbin-seo.sitemap.index_path', 'sitemap_index.xml'), [SitemapController::class, 'index'])
        ->name('zarbin-seo.sitemap.index');
}

if (config('zarbin-seo.features.robots_txt', true) && config('zarbin-seo.robots_txt.route_enabled', true)) {
    Route::get(config('zarbin-seo.robots_txt.path', 'robots.txt'), RobotsTxtController::class)
        ->name('zarbin-seo.robots');
}
