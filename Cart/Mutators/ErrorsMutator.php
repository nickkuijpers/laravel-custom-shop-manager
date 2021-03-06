<?php

namespace App\Application\Custom\Cart\Mutators;

use Niku\Cms\Http\NikuPosts;
use Niku\Cms\Http\NikuTaxonomies;
use Niku\Cart\Http\Controllers\CartMutatorController;

class ErrorsMutator extends CartMutatorController
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

        $value = json_decode($cart->getMeta('errors'), true);                        
        if(is_array($value) && count($value) >= 1){
            $customField['showErrors'] = true;
        } else {
            $customField['showErrors'] = false;            
        }

        $cart->saveMetas([
            'errors' => '',
        ]);

        $customField['value'] = $value;
        return $customField;
    }

}
