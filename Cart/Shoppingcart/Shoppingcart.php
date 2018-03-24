<?php

namespace App\Application\Custom\Cart\Shoppingcart;

use Niku\Cart\Http\Managers\ShoppingcartManager;

class Shoppingcart extends ShoppingcartManager
{	   
	// Setting up the template structure
    public function view()
    {
    	return [
    		'default' => [                            
    
                'customFields' => [
    
                    'shoppingcart' => [                        
                        'component' => 'niku-cart-shoppingcart-customfield',
                        'saveable' => false,                                                              
                        'cart_items_update_api_url' => '/cpm/shoppingcart/custom/edit/update_quantity',                        
                        'cart_items_delete_api_url' => '/cpm/shoppingcart/custom/edit/delete',                        
                        'mutator' => 'App\Application\Custom\Cart\Mutators\ShoppingcartMutator',  
                        'to_checkout_button' => true,
                    ],                     

                ],
            ],
    	];	
    }    
}
