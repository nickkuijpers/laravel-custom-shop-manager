<?php

namespace Niku\Cart\Http\Controllers;

use Illuminate\Http\Request;
use Niku\Cms\Http\NikuPosts;
use App\Http\Controllers\Controller;

class CartController extends Controller
{
    /**
     * Get product template content
     */
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

    /**
     * Get product template content
     */
    protected function GetCheckoutTemplate($template)
    {
        // Receive the config variable where we have whitelisted all models
        $cartTemplates = config('niku-cart');

        // Validating if the model exists in the array
        if(!array_key_exists($template, $cartTemplates['checkout'])){
            return false;
        }

        return (new $cartTemplates['checkout'][$template]);
    }

    /**
     * Integrate events based on the action
     */
    public function triggerEvent($action, $postTypeModel, $post)
    {
        if(method_exists($postTypeModel, $action)){
			$postTypeModel->$action($postTypeModel, $post, $postmeta);
		}
    }

    /**
     * Get the cart
     */
    protected function getCart($cartIdentifier)
    {
        $cart = NikuPosts::where([
            ['post_type', '=', 'shoppingcart'],
            ['post_name', '=', $cartIdentifier],
        ])->with('postmeta')->first();

        return $cart;
    }

    /**
     * Get the order
     */
    protected function getOrder($orderIdentifier)
    {
        $order = NikuPosts::where([
            ['post_type', '=', 'orders'],
            ['post_name', '=', $orderIdentifier],
            // ['status', '=', NULL],
        ])->first();

        return $order;
    }

    protected function getWebsite($websiteIdentifier)
    {
        $order = NikuPosts::where([
            ['post_type', '=', 'websites'],
            ['post_name', '=', $websiteIdentifier],
            ['status', '=', 'active'],
        ])->with('postmeta')->first();

        return $order;
    }

    /**
     * Get the product
     */
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

    /**
     * Get a single cart product
     */
    protected function getSingleCartProduct($cart, $cartProductIdentifier)
    {
        $cartProduct = $cart->where([
            ['post_type', '=', 'shoppingcart-products'],
            ['id', '=', $cartProductIdentifier],
        ])->with('postmeta')->first();

        return $cartProduct;
    }

    protected function getAllCartProducts($cart)
    {
        $cartProducts = $cart->posts()->where([
            ['post_type', '=', 'shoppingcart-products']
        ])->with('postmeta')->get();

        return $cartProducts;
    }

    protected function getTransaction($transactionIdentifier)
    {
        $transaction = NikuPosts::where([
            ['post_type', '=', 'transactions'],
            ['post_name', '=', $transactionIdentifier],
        ])->with('postmeta')->first();

        return $transaction;
    }

    /**
     * Abort the request
     */
    public function abort($message = 'Not authorized.')
    {
        return response()->json([
            'code' => 'error',
            'errors' => [0 => [$message]],
        ], 422);
    }

}
