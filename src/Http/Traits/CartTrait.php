<?php

namespace Niku\Cart\Http\Traits;

use Validator;
use Illuminate\Http\Request;
use Niku\Cms\Http\NikuPosts;
use Illuminate\Support\Facades\Log;
use Niku\Cms\Http\Controllers\cmsController;
use App\Application\Custom\Cart\PostTypes\Checkout;

trait CartTrait
{
    protected function fetchCart($cartIdentifier)
    {
        $cart = NikuPosts::where([
            ['post_type', '=', 'shoppingcart'],
            ['post_name', '=', $cartIdentifier],
        ])->with('postmeta')->first();

        return $cart;
    }

    protected function fetchCartById($cartIdentifier)
    {
        $cart = NikuPosts::where([
            ['post_type', '=', 'shoppingcart'],
            ['id', '=', $cartIdentifier],
        ])->with('postmeta')->first();

        return $cart;
    }

    protected function fetchOrder($orderIdentifier)
    {
        // $cart = NikuPosts::where([
        //     ['post_type', '=', 'shoppingcart'],
        //     ['post_name', '=', $orderIdentifier],
        // ])->with('postmeta')->first();

        $cart = NikuPosts::where([
            ['post_type', '=', 'order'],
            ['post_name', '=', $orderIdentifier],
        ])->with('postmeta')->first();

        return $cart;
    }

    protected function fetchOrderById($orderIdentifier)
    {
        // $order = NikuPosts::where([
        //     ['post_type', '=', 'shoppingcart'],
        //     ['id', '=', $orderIdentifier],
        // ])->with('postmeta')->first();

        $order = NikuPosts::where([
            ['post_type', '=', 'order'],
            ['id', '=', $orderIdentifier],
        ])->with('postmeta')->first();

        return $order;
    }

    protected function fetchSingleCartProduct($cart, $cartProductIdentifier)
    {
        $cartProduct = $cart->where([
            ['post_type', '=', 'shoppingcart-products'],
            ['id', '=', $cartProductIdentifier],
        ])->with('postmeta')->first();

        return $cartProduct;
    }

    protected function fetchProductTemplate($template)
    {
        // Receive the config variable where we have whitelisted all models
        $cartTemplates = config('niku-cart');

        // Validating if the model exists in the array
        if(!array_key_exists($template, $cartTemplates['templates'])){
            return false;
        }

        return (new $cartTemplates['templates'][$template]['class']);
    }


    protected function fetchProduct($productIdentifier)
    {
        $product = NikuPosts::where([
            ['post_type', '=', 'products'],
            ['status', '=', '1'],
            ['post_name', '=', $productIdentifier],
        ])->with('postmeta')->first();

        return $product;
    }

    protected function fetchUnknownProduct($unknownProductIdentifier)
    {
        $unknownProduct = NikuPosts::where([
            ['post_type', '=', 'unknown-products'],
            ['post_name', '=', $unknownProductIdentifier],
        ])->first();

        return $unknownProduct;
    }

    public function abort($message = 'Not authorized.', $config = '', $code = 'error')
	{
		return response()->json([
			'code' => $code,
			'errors' => [
				$code => [
					0 => $message,
				],
			],
			'config' => $config,
		], 430);
	}

    public function fetchCustomFieldsByLocation($location, $postTypeModel)
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

    public function fetchValidationsByLocation($location, $postTypeModel)
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

    protected function fetchAllCartProducts($cart)
    {
        $cartItems = $cart->posts()->where([
            ['post_type', '=', 'shoppingcart-products']
        ])->with('postmeta')->get();

        return $cartItems;
    }

    protected function fetchAllOrderProducts($order)
    {
        // $orderItems = $order->posts()->where([
        //     ['post_type', '=', 'shoppingcart-products']
        // ])->with('postmeta')->get();

        $orderItems = $order->posts()->where([
            ['post_type', '=', 'order-products']
        ])->with('postmeta')->get();

        return $orderItems;
    }

    public function fetchMutatedCart($cart)
    {
        $cartItems = $this->fetchAllCartProducts($cart);

        // Setting required variables
        $items = [];
        $cartPriceSubtotal = 0;
        $cartTaxTotal = 0;
        $cartPriceTotal = 0;

        // Foreaching all the cart items
        foreach($cartItems as $key => $value){

            // Receiving the product configurations of the post type manager
            $productModelConfig = $this->GetProductTemplate($value->template);

            // Fetching the tax group of the product
            $taxGroup = $this->getTaxGroup($value);

            // Are prices filled in with tax or without tax?
            $pricesInclusiveTax = config('niku-cart.config.prices_inclusive_tax');

            // Fetch the product prices
            $productPrices = $this->fetchPricesOfProduct($value, $pricesInclusiveTax, $taxGroup);

            // Fetching the quantity
            $quantity = (integer) number_format($value->getMeta('quantity'), 0, '.', '');

            // Mutating the postmeta
            $postmeta = $value->postmeta->keyBy('meta_key')->map(function($item, $key){
                return $item->meta_value;
            })->toArray();

            // Returning a new array to the items array
            $items[$key] = [

                // Default information
                'id' => $value->id,
                'post_title' => $value->post_title,
                'post_name' => $value->post_name,

                // Pricing and quantity details
                'price_single' => $productPrices->priceSingle,
                'quantity' => $quantity,
                'price_tax' => $productPrices->priceTax,
                'price_total' => $productPrices->priceLine,

                // Meta details
                'postmeta' => $postmeta,

                // Config
                'display_quantity' => $productModelConfig->disableQuantity,
            ];

            // Adding the price to the total of the cart
            $cartPriceSubtotal += $productPrices->priceLine;
            $cartTaxTotal += $productPrices->priceTax;
            $cartPriceTotal += $productPrices->priceTotal;
        }

        $return = collect([

            // Items
            'items' => $items,

            // Combined prices
            'cart_price_subtotal' => $cartPriceSubtotal,
            'cart_price_tax' => $cartTaxTotal,
            'cart_price_total' => $cartPriceTotal,
        ]);

        return $return;
    }

    protected function fetchPricesOfProduct($value, $pricesInclusiveTax, $taxGroup)
    {
        $productPriceSingle = filter_var(number_format($value->getMeta('price_single'), 2, '.', ''), FILTER_VALIDATE_FLOAT);
        $productPriceTotal = filter_var(number_format($value->getMeta('price_total'), 2, '.', ''), FILTER_VALIDATE_FLOAT);

        // Lets remove the tax from the product so we know what the tax amount is
        if($pricesInclusiveTax === true){
            $totalWithoutTax = $productPriceTotal / $taxGroup['percentage'];
            $productTaxAmount = $productPriceTotal - $totalWithoutTax;
            $productLineTotal = $productPriceTotal;

        // Need to add the tax percentage to the total price of the product in the cart
        } else {
            $totalWithTax = $productPriceTotal * $taxGroup['percentage'];
            $productTaxAmount = $totalWithTax - $productPriceTotal;
            $productLineTotal = $productPriceTotal;
            $productPriceTotal = $productPriceTotal + $productTaxAmount;

        }

        $prices = (object) [
            'priceSingle' => $productPriceSingle,
            'priceTax' => $productTaxAmount,
            'priceTotal' => $productPriceTotal,
            'priceLine' => $productLineTotal,
        ];

        return $prices;
    }

    protected function getTaxGroup($value)
    {
        // Fetching the tax group
        $productTaxGroup = $value->getMeta('tax_group');
        if(empty($productTaxGroup)){
            $productTaxGroup = config('niku-cart.config.default_tax_group');
        }

        $taxGroups = collect(config('niku-cart.config.tax_groups'));
        $taxGroup = data_get($taxGroups, $productTaxGroup);

        // If the tax group is empty, use a empty one
        if(empty($taxGroup)){
            $taxGroup = [
                'percentage' => 0,
                'title' => '0% BTW',
                'identifier' => 'none',
            ];
        }

        return $taxGroup;
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

    protected function configurationsRequired($cart, $request)
    {
        $checkConfigurations = (new Checkout)->override_show_post($cart->post_name, $request, 'shoppingcart');
        if($checkConfigurations->getStatusCode() == 431) {
            return true;
        } else {
            return false;
        }
    }

    public function mutateOrder($order)
    {
        // Fetching the post meta of the order and mapping the values
        $orderMeta = $order->postmeta->keyBy('meta_key')->map(function($item, $key){
            return $item->meta_value;
        })->toArray();

        // Fetching all the products of the order and mapping the values
        $items = $this->fetchAllOrderProducts($order)->keyBy('id')->map(function($item, $key){

            $item->product_meta = $item->postmeta->keyBy('meta_key')->map(function($item, $key){
                return $item->meta_value;
            })->toArray();

            unset($item->postmeta);
            unset($item->pivot);

            return $item;
        })->toArray();

        $orderArray = $order->toArray();
        unset($orderArray['postmeta']);

        // Returning the values
        $mutatedOrder = [];
        $mutatedOrder['order'] = $orderArray;
        $mutatedOrder['order']['order_meta'] = $orderMeta;
        $mutatedOrder['order']['items'] = $items;
        return $mutatedOrder;
    }
}
