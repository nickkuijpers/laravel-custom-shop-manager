<?php
/**
 * Adding cart config templates
 */

return [

    'templates' => [
        'standaard' => App\Application\Custom\Cart\Templates\Simple::class,
        'licentie' => App\Application\Custom\Cart\Templates\Complex::class,
    ],
    'checkout' => [
        'default' => App\Application\Custom\Cart\Checkout\CheckoutConfig::class,
    ],

];
