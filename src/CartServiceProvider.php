<?php

namespace Niku\Cart;

use Illuminate\Support\ServiceProvider;

class CartServiceProvider extends ServiceProvider
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
        ], 'niku-cart-config');

        // Register the default post types
        $this->publishes([
            __DIR__.'/../Cart' => app_path('/Application/Custom/Cart'),
        ], 'niku-cart-posttypes');

    }
}
