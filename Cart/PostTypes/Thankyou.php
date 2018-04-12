<?php

namespace App\Application\Custom\Cart\PostTypes;

use Niku\Cms\Http\NikuPosts;

class Thankyou extends NikuPosts
{	       
    public $view;
    public $identifier = 'order';
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
                        'active' => 6,
                    ],          

                    'thankyou' => [                        
                        'component' => 'niku-cart-thankyou-customfield',
                        'saveable' => false,                                                                                   
                        'value' => 'Afrekenen',         
                        'mutator' => 'App\Application\Custom\Cart\Mutators\Thankyou\ThankyouMutator',                                                        
                        'active' => 6,
                    ],         
  
                ],
            ],
    	];	
    }    
}
