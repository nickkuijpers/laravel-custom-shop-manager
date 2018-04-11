<?php

namespace App\Application\Custom\Cart\Mutators;

use Niku\Cms\Http\NikuPosts;
use Niku\Cms\Http\NikuTaxonomies;
use Mollie\Laravel\Facades\Mollie;
use Niku\Cart\Http\Controllers\CartMutatorController;

class PaymentMethodsMutator extends CartMutatorController
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

        $country = $cart->getMeta('land');

        switch($country){            
            case 'be':
                $countryCode = 'nl_BE';
            break;
            case 'de':
                $countryCode = 'de_DE';
            break;
            default:
                $countryCode = 'nl_NL';
            break;
        }

        // Lets fetch all the payment methods based on the country code
        $paymentMethods = Mollie::api()->methods()->all(0, 0, [
            'locale' => $countryCode,
        ]);        

        $methods = collect($paymentMethods->data);

        // Lets get the total shopping cart price
        $prices = $this->fetchCartPrices($cart);
        dd($prices);

        // Lets unset the methods of which the price does not match in between
                

        $methods = $methods->map(function($value, $key){

            $value = [
                'id' => $value->id,
                'image' => $value->image->normal,
                'description' => $value->description,
            ];

            return $value;
        });

        $customField['methods'] = $methods;
        $customField['value'] = $holdValue;
        return $customField;   
    }
 
}
