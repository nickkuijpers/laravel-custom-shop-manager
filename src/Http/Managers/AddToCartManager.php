<?php

namespace Niku\Cart\Http\Managers;

use Validator;
use Illuminate\Http\Request;
use Niku\Cms\Http\NikuPosts;
use Illuminate\Validation\Rule;
use Niku\Cart\Http\Traits\CartTrait;
use App\Application\Custom\Models\User;
use Niku\Cms\Http\Controllers\cmsController;

class AddToCartManager extends NikuPosts
{
    use CartTrait;

    public $singularProduct = false;
    public $disableQuantity = false;

    public $view;
    public $helpers;

    public $getPostByPostName = false;

    // Enable single field saving, creation must be skipped.
    public $enableAllSpecificFieldsUpdate = true;
    public $excludeSpecificFieldsFromUpdate = [];

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

        $cartModel = $this->fetchCart($request->cart_identifier);
        if(!$cartModel){
            return [
                'continue' => false,
                'message' => 'The cart does not exist. Try clearing your localstorage.'
            ];
        }

        // Check custom validations
        $validationRules = $this->fetchValidationsByLocation('addtocart', $this);
        Validator::make($request->all(), $validationRules)->validate();

        // Get the product
        $product = $this->fetchProduct($request->post_name);

        // If the product does not exist, we log it into the database so we can validate it later
        if(!$product){
            $unknownProduct = $this->fetchUnknownProduct($request->post_name);
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

        // Add a custom validator
        $customAddToCartValidator = $this->customAddToCartValidator($request->cart_identifier);
        if(is_array($customAddToCartValidator) && array_key_exists('continue', $customAddToCartValidator) && $customAddToCartValidator['continue'] === false){

            $return = [];
            $return['continue'] = false;

            $message = data_get($customAddToCartValidator, 'message');
            if($message){
                $return['message'] = $message;
            }

            return $return;
        }
    }

    // Overrideable validation
    public function customAddToCartValidator()
    {
    }

    public function override_show_post($id, $request, $postType)
    {
        Validator::make($request->all(), [
            'type' => [
                'required',
                Rule::in([
                    'addtocart',
                    'configuration'
                ]),
            ],
        ])->validate();

        $collection = [];

        $customFields = $this->fetchCustomFieldsByLocation($request->type, $this);

        $collection['templates']['default']['customFields'] = $customFields;

        $collection['config'] = $this->config;

        switch($id) {
            case '0';
                 $toMerge = [];
            break;
            default:
                $post = NikuPosts::where([
                    [ 'id' , '=', $id]
                ])->with('postmeta')->first();
                $collection['post'] = $post;

                $toMerge = [];

                $toMerge['post_title'] = [
                    'value' => $post->post_title,
                ];

                foreach($post->postmeta as $postmetaKey => $postmetaValue){
                    $toMerge[$postmetaValue->meta_key]['value'] = $postmetaValue->meta_value;
                }

            break;
        }

        $collection = $this->helpers->addValuesToCollection($collection, $toMerge);
        $collection = $this->helpers->showMutator($this, $collection, $request);

        unset($collection['templates']['listing']);

        $collection['instance'] = [
			'post_type' => $postType,
			'post_identifier' => $id,
		];

        return response()->json($collection);
    }

    /**
     * Lets connect the added post to the shoppingcart and do the required actions
     */
    public function override_create_post($request)
    {
        $onCheck = $this->validateCreate($request);
        if($onCheck['continue'] === false){
            if(array_key_exists('message', $onCheck)){
                $message = $onCheck['message'];
            } else {
                $message = 'You are not authorized to do this.';
            }
            return $this->abort($message);
        }

        // Receive the cart instance
        $cart = $this->fetchCart($request['cart_identifier']);

        // Receive the product instance
        $product = $this->fetchProduct($request['post_name']);

        // // Lets validate if we have a quantity in the request
        if(!empty($request['quantity'])){
            $quantity = (int) $request['quantity'];
        } else {
            $quantity = 1;
        }

        // If the product is a singular product, that means we need to create a new model for each product
        // instead of updating the quantity of the product.
        if($this->singularProduct === true){

            // Setting the quantity to one because it is a singular product
            $quantity = 1;

            $cartProduct = new NikuPosts;
            $cartProduct->post_type = 'shoppingcart-products';
            $cartProduct->post_title = $product->post_title;
            $cartProduct->post_name = $product->post_name;
            $cartProduct->template = $product->template;
            $cartProduct->save();

            $cartProduct->taxonomies()->attach($cart);

        // If not a singlar product
        } else {

            // Lets check if the product is already in the cart
            $cartProduct = $cart->posts()->where([
                ['post_type', '=', 'shoppingcart-products'],
                ['post_name', '=', $product->post_name],
            ])->with('postmeta')->first();
            if(!$cartProduct){
                $cartProduct = new NikuPosts;

                $cartProduct->post_type = 'shoppingcart-products';
                $cartProduct->post_title = $product->post_title;
                $cartProduct->post_name = $product->post_name;
                $cartProduct->template = $product->template;
                $cartProduct->save();

                $cartProduct->taxonomies()->attach($cart);

            } else {

                $cartProduct->post_type = 'shoppingcart-products';
                $cartProduct->post_title = $product->post_title;
                $cartProduct->post_name = $product->post_name;
                $cartProduct->template = $product->template;
                $cartProduct->save();

            }

            $quantity = $cartProduct->getMeta('quantity') + $quantity;
        }

        // Lets append the save values
        $toSave = [];
        $toSave['quantity'] = $quantity;

        $this->helpers->savePostMetaToDatabase($request->all(), $this, $cartProduct);

        $getPrice = $this->fetchPrice($product, $quantity);
        $toSave['price_single'] = $getPrice->price_single;
        $toSave['price_total'] = $getPrice->price_total;

        $cartProduct->saveMetas($toSave);

        // Return the response
    	return response()->json([
			'config' => $this->config,
    		'code' => 'success',
    		'message' => 'Succesvol toegevoegd.',
    		'action' => 'create',
    	], 200);
    }

    public function fetchPrice($product, $quantity)
    {
        $singlePrice = number_format($product->getMeta('price'), 2, '.', '');
        $totalPrice = number_format($quantity * $product->getMeta('price'), 2, '.', '');

        return (object) [
            'price_single' => $singlePrice,
            'price_total' => $totalPrice,
        ];
    }
}
