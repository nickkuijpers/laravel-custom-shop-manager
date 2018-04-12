<?php

namespace App\Application\Custom\Cart\Mutators\Shoppingcart;

use Niku\Cms\Http\NikuPosts;
use Niku\Cms\Http\NikuTaxonomies;
use Niku\Cart\Http\Controllers\CartMutatorController;

class ShoppingcartButtonMutator extends CartMutatorController
{	  	    
    public function out($customField, $collection, $key, $postTypeModel, $holdValue, $request)    
    {             
        $postId = data_get($collection, 'post.id');
        if(!$postId){
            return $customField;
        }

        $cart = NikuPosts::where([
            ['post_type', '=', 'shoppingcart'],
            ['id', '=', $postId],
        ])->with('postmeta')->first();

        // Lets validate if configurations are required
        $configurationsRequest = $this->configurationsRequired($cart, $request);        
        if($configurationsRequest === true){
            $linkTo = 'configure';
        } else {
            $linkTo = 'checkout';
        }

        $authenticationRequired = config('niku-cart.authentication.required');
        if($authenticationRequired === true){

            $user = $request->user('api');
            if($user){

                $customField['label'] = 'Afrekenen';
                $customField['to'] = [
                    'name' => $linkTo,
                ];

            } else {

                $customField['label'] = 'Afrekenen';
                $customField['to'] = [
                    'name' => 'checkout-login',
                ];

            }            

        } else {

            $customField['label'] = 'Afrekenen';
            $customField['to'] = [
                'name' => $linkTo,
            ];

        }

        return $customField;   
    }    
}
