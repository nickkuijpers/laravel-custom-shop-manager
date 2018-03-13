<?php

namespace Niku\Cms\Http\Controllers\Cart;

use App\Application\Custom\Controllers\Cart\CartController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Niku\Cms\Http\NikuPosts;

class CartItemsConfigStatusController extends CartController
{
    public function handle(Request $request)
    {
        $this->validate($request, [
            'cart_identifier' => 'required',
        ]);

        $cart = $this->getCart($request->cart_identifier);
        if(empty($cart)){
            return $this->abort('The shoppingcart could not be found.', 422);
        }

        $cartProducts = $this->getAllCartProducts($cart);

        $validationRules = [];
        $productRequest = new Request;

        foreach($cartProducts as $productValue){

            $productTemplate = $this->GetProductTemplate($productValue->template);

            if($productTemplate){

                $customFields = $productTemplate->view['default']['customFields'];
                $quantity = (integer) number_format($productValue->getMeta('quantity'), 0, '.', '');
                $productId = $productValue->id;

                if($productTemplate->configPerQuantity){

                    foreach($customFields as $customKey => $customValue){

                        foreach(range(1, intval($quantity), 1) as $quantity){

                            $newCustomKey = $quantity . '_' . $productId . '_configuration_' . $customKey;

                            $meta = $productValue->postmeta()->where([
                                ['meta_key', '=', $newCustomKey],
                            ])->first();

                            if(empty($meta)){
                                $validationRules[$newCustomKey] = $productTemplate->view['default']['customFields'][$customKey]['validation'];
                                $productRequest[$newCustomKey] = '';
                            } else {
                                $validationRules[$newCustomKey] = $productTemplate->view['default']['customFields'][$customKey]['validation'];
                                $productRequest[$newCustomKey] = $meta->meta_value;
                            }
                        }
                    }

                } else {

                    foreach($customFields as $customKey => $customValue){

                        foreach(range(1, 1, 1) as $quantity){

                            $newCustomKey = $quantity . '_' . $productId . '_configuration_' . $customKey;

                            $meta = $productValue->postmeta()->where([
                                ['meta_key', '=', $newCustomKey],
                            ])->first();

                            if(empty($meta)){
                                $validationRules[$newCustomKey] = $productTemplate->view['default']['customFields'][$customKey]['validation'];
                                $productRequest[$newCustomKey] = '';
                            } else {
                                $validationRules[$newCustomKey] = $productTemplate->view['default']['customFields'][$customKey]['validation'];
                                $productRequest[$newCustomKey] = $meta->meta_value;
                            }
                        }
                    }

                }

            }

        }

        $this->validate($productRequest, $validationRules);

        $hasConfig = false;
        if(count($validationRules) > 1){
            $hasConfig = true;
        }

        return response()->json([
            'status' => 'succesful',
            'config' => $hasConfig,
        ]);
    }

}
