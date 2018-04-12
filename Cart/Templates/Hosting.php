<?php

namespace App\Application\Custom\Cart\Templates;

use Niku\Cart\Http\Managers\AddToCartManager;

class Hosting extends AddToCartManager
{       
    public $singularProduct = true;
    public $disableQuantity = true;
    
    public function periodPricing()
    {
        return [
            'default_period' => '12',
            'period_pricing' => [
                '6' => [
                    'label' => '6 Maanden',

                    // New price
                    'price_times' => 0.5,
    
                    // Manipulate price
                    'type' => 'percentage',
                    'amount' => 5,
                    'discount' => false,
                ],
                '12' => [
                    'label' => '1 jaar',
    
                    // New price
                    'price_times' => 1,
    
                    // Manipulate price
                    'type' => 'none',
                    'amount' => 0,
                    'discount' => false,
                ],
                '24' => [
                    'label' => '2 jaar',

                    // New price
                    'price_times' => 2,
    
                    // Manipulate price
                    'type' => 'percentage',
                    'amount' => 5,
                    'discount' => true,
                ],
                '36' => [
                    'label' => '3 jaar',
    
                    // New price
                    'price_times' => 3,
    
                    // Manipulate price
                    'type' => 'percentage',
                    'amount' => 10,
                    'discount' => true,
                ],
                '48' => [
                    'label' => '4 jaar',
    
                    // New price
                    'price_times' => 4,
    
                    // Manipulate price
                    'type' => 'percentage',
                    'amount' => 15,
                    'discount' => true,
                ],
                '60' => [
                    'label' => '5 jaar',
    
                    // New price
                    'price_times' => 5,
    
                    // Manipulate price
                    'type' => 'percentage',
                    'amount' => 20,
                    'discount' => true,
                ],                
            ],
        ];
    }

	// Setting up the template structure
    public function view()
    {
    	return [
    		'default' => [
                
                'label' => 'Afrekenen',
                'description' => 'Vult u de benodigde gegevens in',
                'css_class_customfields_wrapper' => 'col-md-9',
    
                'customFields' => [

                    'post_title' => [                        
                        'component' => 'niku-cart-shoppingcart-configuration-product-title-customfield',
                        'value' => '',
                        'validation' => 'required',                                            
                        'validation_location' => [
                            'addtocart' => false,
                            'shoppingcart' => true,
                            'configuration' => true,                            
                        ],
                    ],

                    'period' => [
                        'label' => 'Period',
                        'component' => 'niku-cms-text-customfield',
                        'value' => '',
                        'validation' => 'required',
                        'css_class' => 'col-md-4 col-sm-4',
                        'hide_label' => 'true',
                        'validation_location' => [
                            'addtocart' => true,
                            'shoppingcart' => true,
                            'configuration' => true,                            
                        ],
                    ],

                    // 'first_name' => [
                    //     'label' => 'Voornaam',
                    //     'component' => 'niku-cms-text-customfield',
                    //     'value' => '',
                    //     'validation' => 'required',
                    //     'css_class' => 'col-md-4 col-sm-4',
                    //     'hide_label' => 'true',
                    //     'validation_location' => [
                    //         'addtocart' => true,
                    //         'shoppingcart' => true,
                    //         'configuration' => true,                            
                    //     ],
                    // ],
                    // 'last_name' => [
                    //     'label' => 'Achternaam',
                    //     'component' => 'niku-cms-text-customfield',
                    //     'value' => '',
                    //     'validation' => 'required',
                    //     'css_class' => 'col-md-4 col-sm-4',
                    //     'hide_label' => 'true',
                    //     'validation_location' => [
                    //         'addtocart' => false,
                    //         'shoppingcart' => true,
                    //         'configuration' => true,                            
                    //     ],
                    // ],
                    // 'email' => [
                    //     'label' => 'E-mailadres',
                    //     'component' => 'niku-cms-text-customfield',
                    //     'value' => '',
                    //     'validation' => 'required|email',                    
                    //     'css_class' => 'col-md-4 col-sm-4',
                    //     'hide_label' => 'true',
                    //     'validation_location' => [
                    //         'addtocart' => true,
                    //         'shoppingcart' => true,
                    //         'configuration' => true,                            
                    //     ],
                    // ],

                    'quantity' => [                        
                        'component' => 'niku-cart-quantity-customfield',
                        'value' => 1,
                        'validation' => 'required|integer',     
                        'validation_location' => [
                            'addtocart' => true,
                            'shoppingcart' => true,
                            'configuration' => false,                            
                        ],                                                                                       
                    ],       
                                                                          
                    'submit' => [
                        'label' => 'TOEVOEGEN',
                        'component' => 'niku-cart-addtocart-customfield',
                        'value' => '',
                        'validation' => '',                    
                        'css_class' => 'col-md-4 col-sm-4',
                        'hide_label' => 'true',
                        'saveable' => false,
                        'validation_location' => [
                            'addtocart' => true,
                            'shoppingcart' => false,
                            'configuration' => false,                            
                        ],
                    ],            
    
                ],
            ],
             
    	];	
    }    
}
