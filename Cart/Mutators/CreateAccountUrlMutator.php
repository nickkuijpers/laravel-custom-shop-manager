<?php

namespace App\Application\Custom\Cart\Mutators;

use Niku\Cms\Http\NikuPosts;
use Niku\Cms\Http\NikuTaxonomies;
use Niku\Cart\Http\Controllers\CartMutatorController;

class CreateAccountUrlMutator extends CartMutatorController
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
        
        $customField['api_url'] = '/cpm/create-account/edit/' . $cart->post_name;

        $customField['value'] = $holdValue;
        return $customField;
    }

}
