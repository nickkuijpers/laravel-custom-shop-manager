<?php

namespace Niku\Cart\Http\Controllers\Cart;

use App\Application\Custom\Cart\Templates\Complex;
use App\Application\Custom\Cart\Templates\Simple;
use Niku\Cart\Http\Controllers\CartController;
use App\Application\Custom\Requests\CartProductAddRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Niku\Cms\Http\NikuPosts;

class CartItemsAddController extends CartController
{
    public function handle(Request $request)
    {
        $this->validate($request, [
            'cart_identifier' => 'required',
            'product_identifier' => 'required',
            'item_quantity' => 'integer',
        ]);
        
        $cart = $this->getCart($request->cart_identifier);
        if(empty($cart)){
            return $this->abort('The shoppingcart could not be found.', 422);
        }

        $product = $this->getProduct($request->product_identifier);
        if(!$product){
            return $this->abort('The product could not be found or is inactive.', 422);
        }

        // Receiving the product cart configuration template
        $configTemplate = $this->GetProductTemplate($product->template);
        if($configTemplate){

            // If the configurations are at the add to cart page, we need to add some validation
            if($configTemplate->configPosition['add_to_cart_page']){

                // Creating the validation array
                $validationRules = [];
                $keys = [];
                foreach($configTemplate->view['default']['customFields'] as $customKey => $customValue){
                    if($configTemplate->configPerQuantity){
                        foreach(range(1, intval($request->quantity), 1) as $quantity){
                            $validationRules[$quantity . '_0_configuration_' . $customKey] = $customValue['validation'];
                            $keys[] = $quantity . '_0_configuration_' . $customKey;
                        }
                    } else {
                        $validationRules['1_0_configuration_' . $customKey] = $customValue['validation'];
                        $keys[] = '1_0_configuration_' . $customKey;
                    }
                }

                // Lets validate the request
                $this->validate($request, $validationRules);

                // Setting the values to save as product meta
                $metasToSave = $request->only($keys);
            }
        }

        // Lets validate if we have a quantity in the request
        if(!empty($request->quantity)){
            $quantity = (int) $request->quantity;
        } else {
            $quantity = 1;
        }

        if($configTemplate){
            if($configTemplate->singularity){
                $quantity = 1;
            }

            $singularity = $configTemplate->singularity;
            $identifier = $configTemplate->identifier;
        } else {

            $singularity = false;
            $identifier = 'default';
        }

        // Lets validate the type of the product
        switch($identifier){
            // case 'configurable':
                // ..
            // break;
            default:

                if($singularity){

                    $cartProduct = new NikuPosts;
                    $cartProduct->post_type = 'shoppingcart-products';
                    $cartProduct->post_title = $product->post_title;
                    $cartProduct->post_name = $product->post_name;
                    $cartProduct->template = $identifier;
                    $cartProduct->save();

                    // Lets attach the product to the cart
                    $cartProduct->taxonomies()->attach($cart);

                } else {

                    // Lets check if the product is already in the cart
                    $cartProduct = $cart->posts()->where([
                        ['post_type', '=', 'shoppingcart-products'],
                        ['post_name', '=', $product->post_name],
                    ])->with('postmeta')->first();

                    // If it does not exist yet, lets create it with the basic information
                    if(!$cartProduct){
                        $cartProduct = new NikuPosts;
                        $cartProduct->post_type = 'shoppingcart-products';
                        $cartProduct->post_title = $product->post_title;
                        $cartProduct->post_name = $product->post_name;
                        $cartProduct->template = $identifier;
                        $cartProduct->save();

                        // Lets attach the product to the cart
                        $cartProduct->taxonomies()->attach($cart);
                    }

                }

                $quantity = $cartProduct->getMeta('quantity') + $quantity;

                // Lets calculate the total price based on the new quantity
                $totalPrice = number_format($quantity * $product->getMeta('price'), 2, '.', '');

                // Lets update the meta information
                $cartProduct->saveMetas([
                    'price_single' => number_format($product->getMeta('price'), 2, '.', ''),
                    'price_total' => $totalPrice,
                    'quantity' => $quantity,
                ]);

                $oldQuantity = (int) $cartProduct->getMeta('quantity');

                if(!empty($metasToSave)){
                    foreach($metasToSave as $key => $value){

                        $keyExploded = explode('_', $key);
                        $key = '';
                        foreach($keyExploded as $keyKey => $keyValue){

                            switch($keyKey){
                                case 0:
                                    $key .= $oldQuantity + $keyValue;
                                break;
                                case 1:
                                    $key .= '_' . $cartProduct->id;
                                break;
                                default:
                                    $key .= '_' . $keyValue;
                                break;
                            }

                        }

                        // Saving it to the database
                        $object = [
                            'meta_key' => $key,
                            'meta_value' => $value,
                            'group' => 'configuration',
                        ];

                        // Update or create the meta key of the post
                        $cartProduct->postmeta()->updateOrCreate([
                            'meta_key' => $key
                        ], $object);

                    }
                }

            break;
        }

        // Lets requery the item so we get the updated version
        $cartProduct = $this->getSingleCartProduct($cart, $cartProduct->id);

        if(!empty($configTemplate)){
            $this->triggerEvent('item_added_to_cart', $configTemplate, [
                'cart' => $cart,
                'product' => $cartProduct,
            ]);
        }

        // Lets return the response
        return response()->json([
            'status' => 'succesful',
            'item' => [
                'id' => $cartProduct->id,
                'title' => $cartProduct->post_title,
                'price_single' => number_format($cartProduct->getMeta('price_single'), 2, '.', ''),
                'quantity' => number_format($cartProduct->getMeta('quantity'), 2, '.', ''),
                'price_total' => number_format($cartProduct->getMeta('price_total'), 2, '.', ''),
            ]
        ]);
    }

}
