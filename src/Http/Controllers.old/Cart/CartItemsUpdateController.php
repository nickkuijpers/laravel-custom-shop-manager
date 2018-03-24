<?php

namespace Niku\Cart\Http\Controllers\Cart;

use Niku\Cart\Http\Controllers\CartController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Niku\Cms\Http\NikuPosts;

class CartItemsUpdateController extends CartController
{
    public function handle(Request $request)
    {
        $this->validate($request, [
            'cart_identifier' => 'required',
            'item_identifier' => 'required',
            'item_quantity' => 'integer',
        ]);

        $cart = $this->getCart($request->cart_identifier);
        if(empty($cart)){
            return $this->abort('The shoppingcart could not be found.', 422);
        }

        // Lets validate if the product is available and active
        $cartProduct = $this->getSingleCartProduct($cart, $request->item_identifier);
        if(!$cartProduct){
            return $this->abort('The product could not be found or is already deleted.', 422);
        }

        // Receiving the config template
        $configTemplate = $this->GetProductTemplate($cartProduct->template);

        // Validating if we can update the quantity
        if($configTemplate->singularity){
            return $this->abort('The quantity of this product cannot be updated.');
        }

        // Lets calculate the total price based on the new quantity
        $totalPrice = number_format($request->quantity * $cartProduct->getMeta('price_single'), 2, '.', '');

        // Lets update the meta information
        $cartProduct->saveMetas([
            'price_total' => $totalPrice,
            'quantity' => $request->quantity,
        ]);

        // Lets requery the item so we get the updated version
        $cartProduct = $cart->where([
            ['post_type', '=', 'shoppingcart-products'],
            ['id', '=', $request->item_identifier],
        ])->with('postmeta')->first();

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
