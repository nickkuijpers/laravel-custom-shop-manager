<?php

namespace App\Application\Custom\Cart\Checkout;

use Validator;
use Illuminate\Http\Request;
use Niku\Cms\Http\NikuPosts;
use Illuminate\Support\Facades\Auth;
use App\Application\Custom\Models\User;
use Niku\Cart\Http\Managers\CreateAccountManager;
use Niku\Cms\Http\Controllers\cmsController;

class LoginCreate extends CreateAccountManager
{	         
	// Setting up the template structure
    public function view()
    {
    	return [
    		'default' => [
                
                'label' => 'Afrekenen',
                'description' => 'Vult u de benodigde gegevens in',
                'css_class_customfields_wrapper' => 'col-md-9',
    
                'customFields' => [

                    'create-login' => [                        
                        'component' => 'niku-cart-login-create-customfield',
                        'saveable' => false,                                                                                   
                        'value' => 'Afrekenen',              
                        'mutator' => 'App\Application\Custom\Cart\Mutators\LoginCreate\LoginCreateMutator',                               
                    ],                  
                    
                     
                        
                ],
            ],
             
    	];	
    }
    
}
