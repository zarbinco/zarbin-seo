<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Zarbin\Seo\Http\Controllers\SeoUiController;
use Zarbin\Seo\Support\UiConfig;

if (UiConfig::routeEnabled()) {
    Route::prefix(UiConfig::path())
        ->as(UiConfig::routeNamePrefix())
        ->middleware(UiConfig::middleware())
        ->group(function (): void {
            Route::get('/', [SeoUiController::class, 'dashboard'])->name('dashboard');
            Route::get('/routes', [SeoUiController::class, 'routes'])->name('routes.index');
            Route::get('/routes/edit', [SeoUiController::class, 'editRoute'])->name('routes.edit');
            Route::post('/routes', [SeoUiController::class, 'updateRoute'])->name('routes.update');
            Route::delete('/routes', [SeoUiController::class, 'deleteRoute'])->name('routes.delete');
        });
}
