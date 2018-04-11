<?php

namespace App\Application\Custom\Cart\Mutators;

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

        $cartItems = $cart->posts()->where([
            ['post_type', '=', 'shoppingcart-products']
        ])->with('postmeta')->get();

        $items = [];
        $priceTotal = 0;

        foreach($cartItems as $key => $value){

            $productModelConfig = $this->GetProductTemplate($value->template);               

            $items[$key] = [                
                'id' => $value->id,
                'post_title' => $value->post_title,
                'post_name' => $value->post_name,

                // Pricing and quantity details
                'price_single' => number_format($value->getMeta('price_single'), 2, ',', ''),
                'quantity' => (integer) number_format($value->getMeta('quantity'), 0, '.', ''),
                'price_total' => number_format($value->getMeta('price_total'), 2, '.', ''),                
                'display_quantity' => $productModelConfig->disableQuantity,                
            ];

            // Add the price
            $priceTotal += number_format($value->getMeta('price_total'), 2, '.', ''); 

        }

        $customField['items'] = $items;
        $customField['price_total'] = number_format($priceTotal, 2, ',', '');
        $customField['postIdentifier'] = $cart->post_name;
        $customField['value'] = $holdValue;

        return $customField;   
    }    
}
