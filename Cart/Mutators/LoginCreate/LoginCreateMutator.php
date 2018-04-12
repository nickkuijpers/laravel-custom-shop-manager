<?php

namespace App\Application\Custom\Cart\Mutators\LoginCreate;

use Niku\Cms\Http\NikuPosts;
use Niku\Cms\Http\NikuTaxonomies;
use Niku\Cart\Http\Controllers\CartMutatorController;

class LoginCreateMutator extends CartMutatorController
{	  	        
    public function out($customField, $collection, $key, $postTypeModel, $holdValue, $request)    
    {                             
        $postId = data_get($request, 'cart_identifier');
        if(!$postId){
            return $customField;
        }

        $cart = NikuPosts::where([
            ['post_type', '=', 'shoppingcart'],
            ['post_name', '=', $postId],
        ])->with('postmeta')->first();

        $configurationsRequired = $this->configurationsRequired($cart, $request);
        if($configurationsRequired === true){
            $customField['link_to'] = [
                'name' => 'configure',
            ];
        } else {
            $customField['link_to'] = [
                'name' => 'checkout',
            ];
        }

        $customField['value'] = '';
        return $customField;   
    }

}
