<?php

namespace App\Application\Custom\Cart\Checkout;

use Niku\Cart\Http\Managers\ConfigurateManager;

class Configurate extends ConfigurateManager
{	       
	// Setting up the template structure
    public function view()
    {
    	return [
    		'default' => [                            
    
                'customFields' => [

                    'title' => [                        
                        'component' => 'niku-cart-title-customfield',
                        'saveable' => false,                                                                                   
                        'value' => 'Configuratie',                                           
                    ],                     

                    'errors' => [                        
                        'component' => 'niku-cart-errors-customfield',
                        'saveable' => false,                                                                                   
                        'value' => '',                                           
                        'mutator' => 'App\Application\Custom\Cart\Mutators\ErrorsMutator',                          
 
                    ],                     
    
                    'configurate' => [                        
                        'component' => 'niku-cart-configurate-customfield',
                        'saveable' => false,       
                        'value' => '',                                                                                            
                        'mutator' => 'App\Application\Custom\Cart\Mutators\ConfigurateMutator',                          
                    ],                     

                    'submit-button' => [                        
                        'component' => 'niku-cart-submit-button-customfield',
                        'saveable' => false,      
                        'value' => '',             
                        'label' => 'Afrekenen',                                                        
                        'to' => [
                            'name' => 'checkout',
                        ],                        
                    ],                     

                ],
            ],
    	];	
    }    
}
