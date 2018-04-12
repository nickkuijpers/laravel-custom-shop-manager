<?php

namespace App\Application\Custom\Cart\PostTypes;

use Niku\Cms\Http\NikuPosts;

class PaymentMethod extends NikuPosts
{	       
    public $view;
    public $identifier = 'shoppingcart';
    public $getPostByPostName = true;    
    public $disableDefaultPostName = true;

    public function __construct()
    {
        $this->view = $this->view();
    }

	// Setting up the template structure
    public function view()
    {
    	return [
    		'default' => [                            
    
                'customFields' => [

                    'steps' => [                        
                        'component' => 'niku-cart-steps-customfield',
                        'saveable' => false,                                                                                   
                        'value' => 'Afrekenen',         
                        'mutator' => 'App\Application\Custom\Cart\Mutators\StepsMutator',                                                        
                        'active' => 3,
                    ],         

                    'title' => [                        
                        'component' => 'niku-cart-title-customfield',
                        'saveable' => false,                                                                                   
                        'value' => 'Betaalmethode kiezen',                                           
                    ],                     

                    'errors' => [                        
                        'component' => 'niku-cart-errors-customfield',
                        'saveable' => false,                                                                                   
                        'value' => '',                                           
                        'mutator' => 'App\Application\Custom\Cart\Mutators\ErrorsMutator',                           
                    ],                     
    
                    'payment_method' => [
                        'label' => 'Betaalmethode',
                        'component' => 'niku-cart-payment-methods-customfield',
                        'css_class_row_wrapper' => 'col-md-12 col-sm-12',
                        'css_class_label' => 'col-md-12',
                        'css_class_input_wrapper' => 'col-md-12',
                        'mutator' => 'App\Application\Custom\Cart\Mutators\PaymentMethods\PaymentMethodsMutator',                                  
                        'value' => '',
                        'validation' => 'required',
                    ],

                    'submit-button' => [                        
                        'component' => 'niku-cart-payment-method-submit',
                        'saveable' => false,      
                        'value' => '',             
                        'label' => 'Bestelling controleren',                                                        
                        'link_to' => [
                            'name' => 'checkout',
                        ],                        
                    ],                     

                ],
            ],
    	];	
    }    
}
