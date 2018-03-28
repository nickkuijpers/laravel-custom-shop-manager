<?php

namespace Niku\Cart\Http\Traits;

use Validator;
use Niku\Cms\Http\NikuPosts;
use Illuminate\Http\Request;
use Niku\Cms\Http\Controllers\cmsController;

trait CartTrait
{
    protected function getCart($cartIdentifier)
    {
        $cart = NikuPosts::where([
            ['post_type', '=', 'shoppingcart'],
            ['post_name', '=', $cartIdentifier],
        ])->with('postmeta')->first();

        return $cart;
    }

    protected function getSingleCartProduct($cart, $cartProductIdentifier)
    {
        $cartProduct = $cart->where([
            ['post_type', '=', 'shoppingcart-products'],
            ['id', '=', $cartProductIdentifier],
        ])->with('postmeta')->first();

        return $cartProduct;
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

    protected function getProduct($productIdentifier)
    {
        $product = NikuPosts::where([
            ['post_type', '=', 'products'],
            ['status', '=', '1'],
            ['post_name', '=', $productIdentifier],
        ])->with('postmeta')->first();

        return $product;
    }

    protected function getUnknownProduct($unknownProductIdentifier)
    {
        $unknownProduct = NikuPosts::where([
            ['post_type', '=', 'unknown-products'],
            ['post_name', '=', $unknownProductIdentifier],
        ])->first();

        return $unknownProduct;
    }

    public function abort($message = 'Not authorized.')
    {
        return response()->json([
            'code' => 'error',
            'errors' => [0 => [$message]],
        ], 422);
    }

    public function getCustomFieldsByLocation($location, $postTypeModel)
    {
        $validations = [];
        foreach($postTypeModel->view as $key => $value){
            foreach($value['customFields'] as $fieldKey => $fieldValue){

                $required = false;

                if(array_key_exists('validation_location', $fieldValue)){

                    switch($location){
                        case 'addtocart':
                            if(array_key_exists('addtocart', $fieldValue['validation_location'])){
                                if($fieldValue['validation_location']['addtocart'] === true){
                                    $required = true;
                                }
                            }
                        break;
                        case 'shoppingcart':
                            if(array_key_exists('shoppingcart', $fieldValue['validation_location'])){
                                if($fieldValue['validation_location']['shoppingcart'] === true){
                                    $required = true;
                                }
                            }
                        break;
                        case 'configuration':
                            if(array_key_exists('configuration', $fieldValue['validation_location'])){
                                if($fieldValue['validation_location']['configuration'] === true){
                                    $required = true;
                                }
                            }
                        break;
                    }

                } else {
                    $required = true;
                }

                // Requires validation
                if($required === true){
                    if(array_key_exists('validation', $fieldValue)){
                        $validations[$fieldKey] = $fieldValue;
                    }
                }

            }

        }

        return $validations;
    }

    public function getValidationsByLocation($location, $postTypeModel)
    {
        $validations = [];
        foreach($postTypeModel->view as $key => $value){
            foreach($value['customFields'] as $fieldKey => $fieldValue){

                $required = false;

                if(array_key_exists('validation_location', $fieldValue)){

                    switch($location){
                        case 'addtocart':
                            if(array_key_exists('addtocart', $fieldValue['validation_location'])){
                                if($fieldValue['validation_location']['addtocart'] === true){
                                    $required = true;
                                }
                            }
                        break;
                        case 'shoppingcart':
                            if(array_key_exists('shoppingcart', $fieldValue['validation_location'])){
                                if($fieldValue['validation_location']['shoppingcart'] === true){
                                    $required = true;
                                }
                            }
                        break;
                        case 'configuration':
                            if(array_key_exists('configuration', $fieldValue['validation_location'])){
                                if($fieldValue['validation_location']['configuration'] === true){
                                    $required = true;
                                }
                            }
                        break;
                    }

                } else {
                    $required = true;
                }

                if(array_key_exists('saveable', $fieldValue) && $fieldValue['saveable'] === false){
                    $required = false;
                }

                // Requires validation
                if($required === true){
                    if(array_key_exists('validation', $fieldValue)){
                        $validations[$fieldKey] = $fieldValue['validation'];
                    }
                }

            }

        }

        return $validations;
    }

}
