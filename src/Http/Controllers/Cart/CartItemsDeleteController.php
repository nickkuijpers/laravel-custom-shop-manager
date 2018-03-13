<?php

namespace Niku\Cms\Http\Controllers\Cart;

use App\Application\Custom\Controllers\Cart\CartController;
use App\Application\Custom\Requests\CartDeleteRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Niku\Cms\Http\NikuPosts;

class CartItemsDeleteController extends CartController
{
    public function handle(CartDeleteRequest $request)
    {
        $cart = $this->getCart($request->cart_identifier);
        if(empty($cart)){
            return $this->abort('The shoppingcart could not be found.', 422);
        }

        $product = $this->getSingleCartProduct($cart, $request->item_identifier);
        if(!$product){
            return $this->abort('The product could not be found or is already deleted.', 422);
        }

        $product->delete();

        $configTemplate = $this->GetProductTemplate($product->template);
        if(!empty($configTemplate)){
            $this->triggerEvent('item_deleted_from_cart', $configTemplate, [
                'cart' => $cart,
                'product' => $product,
            ]);
        }

        return response()->json([
            'status' => 'succesful'
        ]);
    }
}
