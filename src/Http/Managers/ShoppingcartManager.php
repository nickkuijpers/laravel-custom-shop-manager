<?php

namespace Niku\Cart\Http\Managers;

use Validator;
use Illuminate\Http\Request;
use Niku\Cms\Http\NikuPosts;
use Niku\Cart\Http\Traits\CartTrait;
use Niku\Cms\Http\Controllers\cmsController;
use Niku\Cms\Http\Controllers\Cms\ShowPostController;

class ShoppingcartManager extends NikuPosts
{
    use CartTrait;
    
    // The label of the custom post type
	public $label = 'Checkout';
    
    // Define the custom post type
    public $identifier = 'shoppingcart';

    // Users can only view their own posts when this is set to true
    public $userCanOnlySeeHisOwnPosts = false;

    public $view;
    public $helpers;

    public $getPostByPostName = true;
    
    public $config = [
        'back_to_previous_page' => false,
        'disable_overview_button' => true,        
        'link_to_edit_post_type' => 'step4',
        'created_at_post_type' => 'step4',
        'redirect_after_created' => 'step4',
        'redirect_after_editted_posttype' => 'step4',
        'redirect_after_editted_name' => 'step4',

        'template' => [
            'single' => [
                'enable_title' => true,                
                'page_title' => 'Winkelwagens',

                'enable_button' => false,
                'link_back_to_listing' => [
                    'name' => 'step4',
                    'params' => [
                        'post_type' => 'step4',
                    ],
                ],
                'redirect_after_created_link' => [
                    'name' => 'step4',
                    'post_type' => 'step4',
                    'enable' => true,
                ],  
                'redirect_after_editted_link' => [
                    'name' => 'step4',
                    'post_type' => 'step4',
                    'enable' => true,
                ], 
            ],
            'list' => [
                'enable' => false,
                'page_title' => 'Woningen',
                'link_create_new_post' => [
                    'name' => 'superadminSingle',
                    'params' => [
                        'post_type' => 'woningen',
                        'type' => 'new',
                        'id' => 0,
                    ],
                ],
            ],
        ],
    ];

    public function __construct()
    {      
        $this->helpers = new cmsController;  
        $this->view = $this->view();
    }

    public function edit_custom_post_delete($request)
    {
        Validator::make($request->all(), [
            'cart_identifier' => 'required',
            'item_identifier' => 'required',        
        ])->validate();

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

        return (new ShowPostController)->init($request, 'shoppingcart', $request->cart_identifier);        
    }

    public function edit_custom_post_update_quantity($request)
    {
        Validator::make($request->all(), [
            'cart_identifier' => 'required',
            'item_identifier' => 'required',
            'item_quantity' => 'integer',
        ])->validate();

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

        return (new ShowPostController)->init($request, 'shoppingcart', $request->cart_identifier);        
    }

    public function edit_custom_post_initialize_cart($request)
    {
        // Before we can save it, we need to validate that it
        // is really unique by searching the databsae for it.
        $done = 0;
        while (!$done) {

            // If there is no shopping cart available yet, we need to
            // create a unique session so we can identify the cart.
            $randomString = uniqid(str_random(40));

            // Set done when the result is 0 from the query we do
            // to validate if the unique string is unique.
            $cartSearchUnique = NikuPosts::where([
                ['post_type', '=', 'shoppingcart'],
                ['post_name', '=', $randomString],
            ])->count();

            // If there is any result, we do it again!
            if($cartSearchUnique === 0){
                $done = 1;
            }
        }

        // Add it with a random and prefix to custom post type
        $post = new NikuPosts;
        $post->post_name = $randomString;
        $post->post_type = 'shoppingcart';
        $post->save();

        $post->saveMetas([
            'ip_address' => $request->ip(),
            'time_created' => now(),
        ]);

        // Lets return the response
        return response()->json([
            'cart_identifier' => $post->post_name
        ], 200);
    }

    public function edit_custom_post_get_product($request){        
        Validator::make($request->all(), [
            'post_name' => 'required',         
        ])->validate();

        $product = $this->getProduct($request->post_name);

        // If the product does not exist, we log it into the database so we can add it later
        if(!$product){
            $unknownProduct = $this->getUnknownProduct($request->post_name);
            if(!$unknownProduct){
                $unknownProduct = new NikuPosts;
                $unknownProduct->post_type = 'unknown-products';
                $unknownProduct->post_title = $request->post_name;
                $unknownProduct->post_name = $request->post_name;
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
            'post_type' => $product->template,
        ];

        return response()->json($return);
    }

    public function edit_custom_post_add_to_cart($request)
    {        
        Validator::make($request->all(), [
            'cart_identifier' => 'required',
            'product_identifier' => 'required',
            'item_quantity' => 'integer',
        ])->validate();        
        
        $cart = $this->getCart($request->cart_identifier);
        if(empty($cart)){
            return $this->abort('The shoppingcart could not be found.', 422);
        }

        $product = $this->getProduct($request->product_identifier);
        if(!$product){
            return $this->abort('The product could not be found or is inactive.', 422);
        }

        // Receiving the product cart configuration template
        $configTemplate = $this->GetProductTemplate($product->template);
        if($configTemplate){

            // If the configurations are at the add to cart page, we need to add some validation
            if($configTemplate->configPosition['add_to_cart_page']){

                // Creating the validation array
                $validationRules = [];
                $keys = [];
                foreach($configTemplate->view['default']['customFields'] as $customKey => $customValue){
                    if($configTemplate->configPerQuantity){
                        foreach(range(1, intval($request->quantity), 1) as $quantity){
                            $validationRules[$quantity . '_0_configuration_' . $customKey] = $customValue['validation'];
                            $keys[] = $quantity . '_0_configuration_' . $customKey;
                        }
                    } else {
                        $validationRules['1_0_configuration_' . $customKey] = $customValue['validation'];
                        $keys[] = '1_0_configuration_' . $customKey;
                    }
                }

                // Lets validate the request                
                Validator::make($request->all(), $validationRules)->validate();   

                // Setting the values to save as product meta
                $metasToSave = $request->only($keys);
            }
        }

        // Lets validate if we have a quantity in the request
        if(!empty($request->quantity)){
            $quantity = (int) $request->quantity;
        } else {
            $quantity = 1;
        }

        if($configTemplate){
            if($configTemplate->singularity){
                $quantity = 1;
            }

            $singularity = $configTemplate->singularity;
            $identifier = $configTemplate->identifier;
        } else {

            $singularity = false;
            $identifier = 'default';
        }

        // Lets validate the type of the product
        switch($identifier){
            // case 'configurable':
                // ..
            // break;
            default:

                if($singularity){

                    $cartProduct = new NikuPosts;
                    $cartProduct->post_type = 'shoppingcart-products';
                    $cartProduct->post_title = $product->post_title;
                    $cartProduct->post_name = $product->post_name;
                    $cartProduct->template = $identifier;
                    $cartProduct->save();

                    // Lets attach the product to the cart
                    $cartProduct->taxonomies()->attach($cart);

                } else {

                    // Lets check if the product is already in the cart
                    $cartProduct = $cart->posts()->where([
                        ['post_type', '=', 'shoppingcart-products'],
                        ['post_name', '=', $product->post_name],
                    ])->with('postmeta')->first();

                    // If it does not exist yet, lets create it with the basic information
                    if(!$cartProduct){
                        $cartProduct = new NikuPosts;
                        $cartProduct->post_type = 'shoppingcart-products';
                        $cartProduct->post_title = $product->post_title;
                        $cartProduct->post_name = $product->post_name;
                        $cartProduct->template = $identifier;
                        $cartProduct->save();

                        // Lets attach the product to the cart
                        $cartProduct->taxonomies()->attach($cart);
                    }

                }

                $quantity = $cartProduct->getMeta('quantity') + $quantity;

                // Lets calculate the total price based on the new quantity
                $totalPrice = number_format($quantity * $product->getMeta('price'), 2, '.', '');

                // Lets update the meta information
                $cartProduct->saveMetas([
                    'price_single' => number_format($product->getMeta('price'), 2, '.', ''),
                    'price_total' => $totalPrice,
                    'quantity' => $quantity,
                ]);

                $oldQuantity = (int) $cartProduct->getMeta('quantity');

                if(!empty($metasToSave)){
                    foreach($metasToSave as $key => $value){

                        $keyExploded = explode('_', $key);
                        $key = '';
                        foreach($keyExploded as $keyKey => $keyValue){

                            switch($keyKey){
                                case 0:
                                    $key .= $oldQuantity + $keyValue;
                                break;
                                case 1:
                                    $key .= '_' . $cartProduct->id;
                                break;
                                default:
                                    $key .= '_' . $keyValue;
                                break;
                            }

                        }

                        // Saving it to the database
                        $object = [
                            'meta_key' => $key,
                            'meta_value' => $value,
                            'group' => 'configuration',
                        ];

                        // Update or create the meta key of the post
                        $cartProduct->postmeta()->updateOrCreate([
                            'meta_key' => $key
                        ], $object);

                    }
                }

            break;
        }

        // Lets requery the item so we get the updated version
        $cartProduct = $this->getSingleCartProduct($cart, $cartProduct->id);

        if(!empty($configTemplate)){
            $this->triggerEvent('item_added_to_cart', $configTemplate, [
                'cart' => $cart,
                'product' => $cartProduct,
            ]);
        }

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
            
    public function triggerEvent($action, $postTypeModel, $post)
    {
        if(method_exists($postTypeModel, $action)){
            $postTypeModel->$action($postTypeModel, $post, $postmeta);
        }
    }        
}
