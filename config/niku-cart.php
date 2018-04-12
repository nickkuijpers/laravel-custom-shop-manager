<?php

return [

    'authentication' => [
        'required' => true,
    ],

    'config' => [
        'prices_inclusive_tax' => false,
        'default_tax_group' => 'high',
        'tax_groups' => [
            'none' => [
                'percentage' => 1.0,
                'title' => '0% BTW',
                'identifier' => 'none',
            ],
            'low' => [
                'percentage' => 1.06,
                'title' => '6% BTW',
                'identifier' => 'low',
            ],
            'middle' => [
                'percentage' => 1.21,
                'title' => '21% BTW',
                'identifier' => 'middle',
            ],
            'high' => [
                'percentage' => 1.21,
                'title' => '21% BTW',
                'identifier' => 'high',
            ],
        ],
    ],

    'templates' => [
        'standaard' => [
            'label' => 'Standaard product',
            'class' => App\Application\Custom\Cart\Templates\Simple::class,
        ],
        'licentie' => [
            'label' => 'Licentie product',
            'class' => App\Application\Custom\Cart\Templates\Complex::class,
        ],
        'hosting' => [
            'label' => 'Hosting',
            'class' => App\Application\Custom\Cart\Templates\Hosting::class,
        ]
    ],

];
