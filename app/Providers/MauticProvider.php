<?php

namespace App\Providers;

use App\Providers\Mautic\MauticService;
use Illuminate\Support\ServiceProvider;

class MauticProvider extends ServiceProvider
{
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('mautic', function ($app) {
            return new MauticService(config('mautic'));
        });
    }
}
