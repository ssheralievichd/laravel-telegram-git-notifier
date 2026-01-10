<?php

namespace App\Providers;

use App\Http\Actions\CustomIndexAction;
use App\Http\Middleware\FilterRepository;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class CustomTelegramRouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $routePrefix = config('telegram-git-notifier.defaults.route_prefix');

        Route::prefix($routePrefix)
            ->name("$routePrefix.")
            ->middleware(FilterRepository::class)
            ->group(function () {
                Route::match(['get', 'post'], '/', CustomIndexAction::class)->name('index');
            });
    }
}
