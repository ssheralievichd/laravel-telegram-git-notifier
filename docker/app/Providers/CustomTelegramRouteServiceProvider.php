<?php

namespace App\Providers;

use App\Http\Actions\CustomIndexAction;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class CustomTelegramRouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $routePrefix = config('telegram-git-notifier.defaults.route_prefix');

        Route::prefix($routePrefix)
            ->name("$routePrefix.")
            ->group(function () {
                Route::match(['get', 'post'], '/', CustomIndexAction::class)->name('index');
            });
    }
}
