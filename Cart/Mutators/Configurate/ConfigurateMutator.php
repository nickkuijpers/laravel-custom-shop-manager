<?php

namespace App\Application\Custom\Cart\Mutators\Configurate;

use Niku\Cms\Http\NikuPosts;
use Niku\Cms\Http\NikuTaxonomies;
use Niku\Cart\Http\Controllers\CartMutatorController;

class ConfigurateMutator extends CartMutatorController
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

        $cartItems = $cart->posts()->where([
            ['post_type', '=', 'shoppingcart-products']
        ])->with('postmeta')->get();

        $items = [];
        foreach($cartItems as $key => $value){                                            

            $items[] = [                
                'identifier' => $value->id,
                'post_type' => $value->template,      
                'single_field_updation_disabled' => false,          
            ];

        }
        
        $customField['items'] = $items;                
        $customField['value'] = '';

        return $customField;   
    }

}
