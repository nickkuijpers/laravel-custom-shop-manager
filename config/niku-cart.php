<?php

return [

    'templates' => [
        'standaard' => [
            'label' => 'Standaard product',
            'class' => App\Application\Custom\Cart\Templates\Simple::class,
        ],
        'licentie' => [
            'label' => 'Licentie product',
            'class' => App\Application\Custom\Cart\Templates\Complex::class,
        ]
    ],

];
