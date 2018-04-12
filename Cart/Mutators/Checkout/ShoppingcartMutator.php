<?php

namespace App\Application\Custom\Cart\Mutators\Checkout;

use Niku\Cms\Http\NikuPosts;
use Niku\Cms\Http\NikuTaxonomies;
use Niku\Cart\Http\Controllers\CartMutatorController;

class ShoppingcartMutator extends CartMutatorController
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

        $mutatedCart = $this->fetchMutatedCart($cart);

        foreach($mutatedCart as $key => $value){
            $customField[$key] = $value;
        }
        
        $customField['postIdentifier'] = $cart->post_name;
        $customField['value'] = $holdValue;

        return $customField;   
    }    
}
