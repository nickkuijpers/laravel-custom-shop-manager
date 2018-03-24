<?php

return [

    'templates' => [        
        'standaard' => [
            'label' => 'Standaard product',
            'class' => App\Cart\Templates\Simple::class,
        ],
        'licentie' => [
            'label' => 'Licentie product',
            'class' => App\Cart\Templates\Complex::class,
        ]
    ],    

];
