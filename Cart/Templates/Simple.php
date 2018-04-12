<?php

namespace App\Application\Custom\Cart\Templates;

use Niku\Cart\Http\Managers\AddToCartManager;

class Simple extends AddToCartManager
{	 
    public $singularProduct = false;
    
	// Setting up the template structure
    public function view()
    {
    	return [
    		'default' => [
                
                'label' => 'Afrekenen',
                'description' => 'Vult u de benodigde gegevens in',
                'css_class_customfields_wrapper' => 'col-md-9',
    
                'customFields' => [

                    'submit' => [
                        'label' => 'TOEVOEGEN',
                        'component' => 'niku-cart-addtocart-customfield',
                        'value' => '',
                        'validation' => '',                    
                        'css_class' => 'col-md-4 col-sm-4',
                        'hide_label' => 'true',
                        'validation_location' => [
                            'addtocart' => true,
                            'shoppingcart' => false,
                            'configuration' => false,                            
                        ],   
                        'saveable' => false,
                    ],                    
                ],
            ],
             
    	];	
    }
}
