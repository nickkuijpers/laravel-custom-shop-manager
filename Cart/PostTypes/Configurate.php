<?php

namespace App\Application\Custom\Cart\PostTypes;

use Niku\Cart\Http\Managers\ConfigurateManager;

class Configurate extends ConfigurateManager
{	       
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
                        'active' => 2,                                             
                    ],         

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
                        'mutator' => 'App\Application\Custom\Cart\Mutators\Configurate\ConfigurateMutator',                          
                    ],                     

                    'submit-button' => [                        
                        'component' => 'niku-cart-submit-button-customfield',
                        'saveable' => false,      
                        'value' => '',             
                        'label' => 'Betaalmethode kiezen',                                                        
                        'to' => [
                            'name' => 'payment-method',
                        ],                        
                    ],                     

                ],
            ],
    	];	
    }    
}
