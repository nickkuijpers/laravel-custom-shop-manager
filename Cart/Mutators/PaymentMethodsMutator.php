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
        $mutatedCart = $this->fetchMutatedCart($cart);

        // The total price of the cart
        $totalCartPrice = $mutatedCart['cart_price_total'];

        // Lets unset the methods of which the price does not match in between        
        $methods = $methods->filter(function($value, $key) use ($totalCartPrice) {

            // If the price is in between of the payment methods requirements
            $myValue = $totalCartPrice;                        
            $minValue = filter_var(number_format($value->amount->minimum, 2, '.', ''), FILTER_VALIDATE_FLOAT);
            $maxValue = filter_var(number_format($value->amount->maximum, 2, '.', ''), FILTER_VALIDATE_FLOAT);

            if ($myValue >= $minValue && $myValue <= $maxValue) {                 
                return true;
            } else {
                return false;
            }
        });
        
        // Lets map the format to match the front-end
        $methods = $methods->map(function($value, $key) {            
            $value = [
                'id' => $value->id,
                'image' => $value->image->normal,
                'description' => $value->description,
            ];
            return $value;
        });
        
        if($methods->count() === 0){
            $customField['methods_message'] = 'Er zijn geen betaalmethodes beschikbaar. Neemt u contact met ons op.';
            $customField['methods_available'] = false;
        } else {
            $customField['methods_message'] = '';
            $customField['methods_available'] = true;
        }   

        $customField['methods'] = $methods;
        $customField['value'] = $holdValue;
        return $customField;   
    }
}
