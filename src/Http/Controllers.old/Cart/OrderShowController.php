<?php

namespace Niku\Cart\Http\Controllers\Cart;

use Niku\Cart\Http\Controllers\CartController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Niku\Cms\Http\NikuPosts;

class OrderShowController extends CartController
{
    public function handle(Request $request)
    {
        $this->validate($request, [
            'order_identifier' => 'required',
        ]);

        $cart = $this->getOrder($request->order_identifier);
        if(empty($cart)){
            return $this->abort('The order could not be found.', 422);
        }

        $cartItems = $this->getAllCartProducts($cart);

        $items = [];
        $priceTotal = 0;

        foreach($cartItems as $key => $value){

            $cartConfig = $this->GetProductTemplate($value->template);
            if($cartConfig){
                $displayQuantity = $cartConfig->displayQuantity;
                $configPerQuantity = $cartConfig->configPerQuantity;
                $configPosition = $cartConfig->configPosition['configuration_page'];
            } else {
                $displayQuantity = true;
                $configPerQuantity = false;
                $configPosition = false;
            }

            $items[$key] = [
                'product' => [
                    'id' => $value->id,
                    'post_title' => $value->post_title,
                    'post_name' => $value->post_name,

                    // Pricing and quantity details
                    'price_single' => number_format($value->getMeta('price_single'), 2, ',', ''),
                    'quantity' => (integer) number_format($value->getMeta('quantity'), 0, '.', ''),
                    'price_total' => number_format($value->getMeta('price_total'), 2, '.', ''),
                ],
                'display_quantity' => $displayQuantity,
                'config_per_quantity' => $configPerQuantity,
            ];

            // Add the price
            $priceTotal += number_format($value->getMeta('price_total'), 2, '.', '');

            // Lets validate if we need to show any configurations on the add to cart page
            if($configPosition){

                // Let set the required configs if we need to show configurations in the add to cart page
                $items[$key]['config'] = true;
                $items[$key]['config_fields'] = $cartConfig->view;

                $customFields = $cartConfig->view;
                $quantity = (integer) number_format($value->getMeta('quantity'), 0, '.', '');
                $productId = $value->id;

                if($cartConfig->configPerQuantity){

                    foreach($customFields['default']['customFields'] as $customKey => $customValue){

                        foreach(range(1, intval($quantity), 1) as $quantity){

                            $newCustomKey = $quantity . '_' . $productId . '_configuration_' . $customKey;

                            $meta = $value->postmeta()->where([
                                ['meta_key', '=', $newCustomKey],
                            ])->first();

                            if($meta){
                                $items[$key]['config_fields']['default']['customFields'][$quantity][$newCustomKey] = $cartConfig->view['default']['customFields'][$customKey];
                                $items[$key]['config_fields']['default']['customFields'][$quantity][$newCustomKey]['value'] = $meta->meta_value;
                            } else {
                                $items[$key]['config_fields']['default']['customFields'][$quantity][$newCustomKey] = $cartConfig->view['default']['customFields'][$customKey];
                                $items[$key]['config_fields']['default']['customFields'][$quantity][$newCustomKey]['value'] = '';
                            }
                        }

                        unset($items[$key]['config_fields']['default']['customFields'][$customKey]);
                    }

                } else {

                    foreach($customFields['default']['customFields'] as $customKey => $customValue){

                        foreach(range(1, 1, 1) as $quantity){

                            $newCustomKey = $quantity . '_' . $productId . '_configuration_' . $customKey;

                            $meta = $value->postmeta()->where([
                                ['meta_key', '=', $newCustomKey],
                            ])->first();

                            if($meta){
                                $items[$key]['config_fields']['default']['customFields'][$quantity][$newCustomKey] = $cartConfig->view['default']['customFields'][$customKey];
                                $items[$key]['config_fields']['default']['customFields'][$quantity][$newCustomKey]['value'] = $meta->meta_value;
                            } else {
                                $items[$key]['config_fields']['default']['customFields'][$quantity][$newCustomKey] = $cartConfig->view['default']['customFields'][$customKey];
                                $items[$key]['config_fields']['default']['customFields'][$quantity][$newCustomKey]['value'] = '';
                            }

                        }

                        unset($items[$key]['config_fields']['default']['customFields'][$customKey]);
                    }

                }

            }

        }

        // Lets return the response
        return response()->json([
            'cart' => [
                'id' => $cart->post_name,
                'price_total' => number_format($priceTotal, 2, ',', ''),
                'payment_status' => $cart->getMeta('payment_status'),
                'payment_method' => $cart->getMeta('payment_method'),
                'items' => $items,
            ]
        ]);
    }
}
