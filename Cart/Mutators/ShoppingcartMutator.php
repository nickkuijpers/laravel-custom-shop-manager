<?php

namespace App\Application\Custom\Cart\Mutators;

use Niku\Cms\Http\NikuPosts;
use Niku\Cms\Http\NikuTaxonomies;
use Niku\Cms\Http\Controllers\cmsController;
use Niku\Cms\Http\Controllers\MutatorController;

class ShoppingcartMutator extends MutatorController
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
                'id' => $value->id,
                'post_title' => $value->post_title,
                'post_name' => $value->post_name,

                // Pricing and quantity details
                'price_single' => number_format($value->getMeta('price_single'), 2, ',', ''),
                'quantity' => (integer) number_format($value->getMeta('quantity'), 0, '.', ''),
                'price_total' => number_format($value->getMeta('price_total'), 2, '.', ''),                
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

        $customField['items'] = $items;
        $customField['price_total'] = number_format($priceTotal, 2, ',', '');
        $customField['postIdentifier'] = $cart->post_name;
        $customField['value'] = $holdValue;

        return $customField;   
    }

    protected function GetProductTemplate($template)
    {
        // Receive the config variable where we have whitelisted all models
        $cartTemplates = config('niku-cart');

        // Validating if the model exists in the array
        if(!array_key_exists($template, $cartTemplates['templates'])){
            return false;
        }

        return (new $cartTemplates['templates'][$template]['class']);
    }
}
