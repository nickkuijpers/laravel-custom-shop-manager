<?php

namespace Niku\Cms;

use Illuminate\Support\ServiceProvider;

class CmsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // Register translations
        $this->loadTranslationsFrom(__DIR__.'/../translations', 'niku-assets');

        // Register config
        $this->publishes([
            __DIR__.'/../config/niku-cart.php' => config_path('niku-cart.php'),
        ], 'niku-config');

        // Register the default post types
        $this->publishes([
            __DIR__.'/../PostTypes' => app_path('/Cms/PostTypes'),
        ], 'niku-cart');

    }
}
