<?php

namespace Niku\Cart\Http\Managers;

use Validator;
use Illuminate\Http\Request;
use Niku\Cms\Http\NikuPosts;
use Niku\Cart\Http\Traits\CartTrait;
use Niku\Cms\Http\Controllers\cmsController;

class AddToCartManager extends NikuPosts
{
    use CartTrait;

    public $singularProduct = false;

    public $view;
    public $helpers;

    public $getPostByPostName = true;

    // The label of the custom post type
	public $label = 'Checkout';

    // Define the custom post type
    public $identifier = 'shoppingcart-products';    
    
    // Users can only view their own posts when this is set to true
    public $userCanOnlySeeHisOwnPosts = false;

    public $disableDefaultPostName = true;    
    public $disableSanitizingPostName = true;
    public $makePostNameRandom = true;

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
                'enable_title' => false,                
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
        ],
    ];

    public function __construct()
    {      
        $this->helpers = new cmsController;  
        $this->view = $this->view();
    }

    /**
     * Validating if the product exists, if so we continue
     */
    public function validateCreate($request)
    {
        $continue = true;

        Validator::make($request->all(), [
            'post_name' => 'required', 
            'cart_identifier' => 'required',                     
        ])->validate();

        $cartModel = $this->getCart($request->cart_identifier);
        if(!$cartModel){
            return [
                'continue' => false,
                'message' => 'The cart does not exist. Try clearing your localstorage.'
            ];          
        }

        // Check custom validations         
        $validationRules = $this->helpers->validatePostFields($request->all(), $request, $this);        
        Validator::make($request->all(), $validationRules)->validate();

        // Get the product
        $product = $this->getProduct($request->post_name);
        
        // If the product does not exist, we log it into the database so we can validate it later
        if(!$product){
            $unknownProduct = $this->getUnknownProduct($request->post_name);            
            if(!$unknownProduct){
                $unknownProduct = new NikuPosts;
                $unknownProduct->post_type = 'unknown-products';
                $unknownProduct->post_title = $request->post_name;
                $unknownProduct->post_name = $request->post_name;
                $unknownProduct->save();
            }            

            return [
                'continue' => false,
                'message' => 'Product "' . $request->post_name . '" does not exist or is inactive.'
            ];            
        }        
    }

    /**
     * Lets connect the added post to the shoppingcart and do the required actions
     */
    public function override_create_post($request)
    {        
        $onCheck = $this->validateCreate($request);        
        if($onCheck['continue'] === false){                        
            return $this->abort('You are not authorized to do this.');
        }
        
        $cart = $this->getCart($request['cart_identifier']);
             
        $product = $this->getProduct($request['post_name']);            
        
        // // Lets validate if we have a quantity in the request
        if(!empty($request['quantity'])){
            $quantity = (int) $request['quantity'];
        } else {
            $quantity = 1;
        }    
        
        if($this->singularProduct === true){                              
            
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
                $cartProduct->template = $product->template;
                $cartProduct->save();

                // Lets attach the product to the cart
                $cartProduct->taxonomies()->attach($cart);
            }

            $quantity = $cartProduct->getMeta('quantity') + $quantity;

            $toSave = [];
            $toSave['quantity'] = $quantity;
            $cartProduct->saveMetas($toSave);

        } else {

            $cartProduct = new NikuPosts;
            $cartProduct->post_type = 'shoppingcart-products';
            $cartProduct->post_title = $product->post_title;
            $cartProduct->post_name = $product->post_name;
            $cartProduct->template = $product->template;
            $cartProduct->save();

            // Lets attach the product to the cart
            $cartProduct->taxonomies()->attach($cart);                        

            $toSave = [];
            $toSave['quantity'] = $quantity;
            $cartProduct->saveMetas($toSave);
        }

        $this->helpers->savePostMetaToDatabase($request->all(), $this, $cartProduct);

        // Lets calculate the total price based on the new quantity
        $singlePrice = number_format($product->getMeta('price'), 2, '.', '');
        $totalPrice = number_format($quantity * $product->getMeta('price'), 2, '.', '');

        $toSave['price_single'] = $singlePrice;
        $toSave['price_total'] = $totalPrice;

        $cartProduct->saveMetas($toSave);

        // Return the response
    	return response()->json([
			'config' => $this->config,
    		'code' => 'success',
    		'message' => 'Succesvol toegevoegd.',
    		'action' => 'create',    		
    	], 200);    
    }	 
}
