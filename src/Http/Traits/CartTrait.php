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
}
