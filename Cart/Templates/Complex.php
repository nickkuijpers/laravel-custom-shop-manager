<?php

namespace App\Application\Custom\Cart\Templates;

use Niku\Cart\Http\Managers\AddToCartManager;

class Complex extends AddToCartManager
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

                    'first_name' => [
                        'label' => 'Voornaam',
                        'component' => 'niku-cms-text-customfield',
                        'value' => '',
                        'validation' => 'required',
                        'css_class' => 'col-md-4 col-sm-4',
                        'hide_label' => 'true',
                    ],
                    'last_name' => [
                        'label' => 'Achternaam',
                        'component' => 'niku-cms-text-customfield',
                        'value' => '',
                        'validation' => 'required',
                        'css_class' => 'col-md-4 col-sm-4',
                        'hide_label' => 'true',
                    ],
                    'email' => [
                        'label' => 'E-mailadres',
                        'component' => 'niku-cms-text-customfield',
                        'value' => '',
                        'validation' => 'required|email',                    
                        'css_class' => 'col-md-4 col-sm-4',
                        'hide_label' => 'true',
                    ],

                    'quantity' => [                        
                        'component' => 'niku-cart-quantity-customfield',
                        'value' => 1,
                        'validation' => 'required|integer',                                                                                            
                    ],            

                    'submit' => [
                        'label' => 'TOEVOEGEN',
                        'component' => 'niku-cart-addtocart-customfield',
                        'value' => '',
                        'validation' => '',                    
                        'css_class' => 'col-md-4 col-sm-4',
                        'hide_label' => 'true',
                        'saveable' => false,
                    ],            
    
                ],
            ],
             
    	];	
    }    
}
