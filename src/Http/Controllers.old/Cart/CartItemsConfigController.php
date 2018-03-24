<?php

namespace Niku\Cart\Http\Controllers\Cart;

use Niku\Cart\Http\Controllers\CartController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Niku\Cms\Http\NikuPosts;

class CartItemsConfigController extends CartController
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
        $productCustomFields = [];

        foreach($cartProducts as $productValue){

            $productTemplate = $this->GetProductTemplate($productValue->template);
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

                        $validationRules[$newCustomKey] = $productTemplate->view['default']['customFields'][$customKey]['validation'];
                        $productCustomFields[$productValue->id]['customFields'][$newCustomKey] = $request->get($newCustomKey);
                    }
                }

            } else {

                foreach($customFields as $customKey => $customValue){

                    foreach(range(1, 1, 1) as $quantity){

                        $newCustomKey = $quantity . '_' . $productId . '_configuration_' . $customKey;

                        $meta = $productValue->postmeta()->where([
                            ['meta_key', '=', $newCustomKey],
                        ])->first();

                        $validationRules[$newCustomKey] = $productTemplate->view['default']['customFields'][$customKey]['validation'];
                        $productCustomFields[$productValue->id]['customFields'][$newCustomKey] = $request->get($newCustomKey);
                    }
                }

            }

        }

        $this->validate($request, $validationRules);

        foreach($cartProducts as $productValue){
            if(array_has($productCustomFields, $productValue->id)){
                foreach($productCustomFields[$productValue->id]['customFields'] as $productCustomFieldKey => $productCustomFieldValue){

                    // Saving it to the database
                    $object = [
                        'meta_key' => $productCustomFieldKey,
                        'meta_value' => $productCustomFieldValue,
                        'group' => 'configuration',
                    ];

                    // Update or create the meta key of the post
                    $productValue->postmeta()->updateOrCreate([
                        'meta_key' => $productCustomFieldKey
                    ], $object);

                }
            }
        }

        // Lets return the response
        return response()->json([
            'status' => 'succesful',
        ]);
    }

    /**
     * get product template content
     */
    public function productConfigs($templateType)
    {
        // Receive the config variable where we have whitelisted all models
        $cartTemplates = config('niku-cart');

        // Validating if the model exists in the array
        if(array_key_exists($templateType, $cartTemplates['templates'])){

            // Setting the model class
           $fields = new $cartTemplates['templates'][$templateType];

           return [
                'configMetas' =>   $fields->view['default']['customFields'],
                'configPosition' => $fields->congifPosition
           ];

        } else {
            return false;
        }
    }
}
