<?php

namespace Niku\Cms\Http\Controllers\Cart;

use App\Application\Custom\Cart\Templates\Complex;
use App\Application\Custom\Cart\Templates\Simple;
use App\Application\Custom\Controllers\Cart\CartController;
use App\Application\Custom\Requests\ProductsShowRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Niku\Cms\Http\NikuPosts;

class ProductsShowController extends CartController
{
    public function handle(ProductsShowRequest $request)
    {
        $product = $this->getProduct($request->product_id);

        // If the product does not exist, we log it into the database so we can add it later
        if(!$product){
            $unknownProduct = $this->getUnknownProduct($request->product_id);
            if(!$unknownProduct){
                $unknownProduct = new NikuPosts;
                $unknownProduct->post_type = 'unknown-products';
                $unknownProduct->post_title = $request->product_id;
                $unknownProduct->post_name = 'up-' . $request->product_id;
                $unknownProduct->save();
            }

            return $this->abort('Product "' . $request->product_id . '" does not exist or is inactive.');
        }

        // Lets get the add to cart product type configuration file
        $cartConfig = $this->GetProductTemplate($product->template);
        if(!$cartConfig){
            return $this->abort('The template of the product is not available.');
        }

        // Lets create the return array
        $return = [
            'product' => [
                'id' => $product->post_id,
                'post_name' => $product->post_name,
                'post_title' => $product->post_title,
            ],
            'display_quantity' => $cartConfig->displayQuantity,
            'config_per_quantity' => $cartConfig->configPerQuantity,
            'singularity' => $cartConfig->singularity,
        ];

        // Lets validate if we need to show any configurations on the add to cart page
        if($cartConfig->configPosition['add_to_cart_page']){

            // Let set the required configs if we need to show configurations in the add to cart page
            $return['config'] = true;
            $return['config_fields'] = $cartConfig->view;
        }

        return response()->json($return);
    }

}
