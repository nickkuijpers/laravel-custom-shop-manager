<?php

namespace App\Application\Custom\Cart\PostTypes;

use Validator;
use Illuminate\Http\Request;
use Niku\Cms\Http\NikuPosts;
use Illuminate\Support\Facades\Auth;
use App\Application\Custom\Models\User;
use Niku\Cart\Http\Managers\CheckoutManager;
use Niku\Cms\Http\Controllers\cmsController;

class Checkout extends CheckoutManager
{	         

    public $authenticationRequired = true;

	// Setting up the template structure
    public function view()
    {
    	return [
    		'default' => [
                
                'label' => 'Afrekenen',
                'description' => 'Vult u de benodigde gegevens in',
                'css_class_customfields_wrapper' => 'col-md-9',
    
                'customFields' => [

                    'steps' => [                        
                        'component' => 'niku-cart-steps-customfield',
                        'saveable' => false,                                                                                   
                        'value' => 'Afrekenen',         
                        'mutator' => 'App\Application\Custom\Cart\Mutators\StepsMutator',                                                        
                        'active' => 4,
                    ],                

                    'title' => [                        
                        'component' => 'niku-cart-title-customfield',
                        'saveable' => false,                                                                                   
                        'value' => 'Afrekenen',                                           
                    ],                  
                    
                    'form_wrapper' => [                        
                        'component' => 'niku-cart-form-wrapper-customfield',
                        'css_class_row_wrapper' => 'row',                                                
                        'saveable' => false,         
                        'value' => '',               
                        'customFields' => [

                            'contactgegevens_title' => [
                                'label' => 'Contactgegegevens',
                                'component' => 'niku-cms-title-customfield',
                                'css_class_row_wrapper' => 'col-md-12 col-sm-12',
                                'css_class_title_wrapper' => 'col-md-12',
                                'css_class_title' => 'h4',
                                'saveable' => false,          
                                'value' => '',              
                            ],
            
                            'company' => [
                                'label' => 'Naam bedrijf',
                                'type' => 'text',
                                'value' => '',
                                'component' => 'niku-cms-readonly-customfield',
                                'css_class_row_wrapper' => 'col-md-12 col-sm-12',
                                'css_class_label' => 'col-md-12',
                                'css_class_input_wrapper' => 'col-md-12',
                                'validation' => '',
                            ],
            
                            'aanhef' => [
                                'label' => 'Aanhef',
                                'type' => 'text',
                                'value' => 'dhr.',
                                'component' => 'niku-cms-readonly-customfield',
                                'options' => [
                                    'dhr.' => 'Dhr.',
                                    'mevr.' => 'Mevr.',
                                ],
                                'css_class_row_wrapper' => 'col-md-4 col-sm-4',
                                'css_class_label' => 'col-md-12',
                                'css_class_input_wrapper' => 'col-md-12',
                                'validation' => '',
                            ],
            
                            'voornaam' => [
                                'label' => 'Voornaam',
                                'type' => 'text',
                                'value' => '',
                                'component' => 'niku-cms-readonly-customfield',
                                'css_class_row_wrapper' => 'col-md-4 col-sm-4',
                                'css_class_label' => 'col-md-12',
                                'css_class_input_wrapper' => 'col-md-12',
                                'validation' => '',
                            ],

                            'achternaam' => [
                                'label' => 'Achternaam',
                                'type' => 'text',
                                'value' => '',
                                'component' => 'niku-cms-readonly-customfield',
                                'css_class_row_wrapper' => 'col-md-4 col-sm-4',
                                'css_class_label' => 'col-md-12',
                                'css_class_input_wrapper' => 'col-md-12',
                                'validation' => '',
                            ],
            
                            'telefoonnummer' => [
                                'label' => 'Telefoonnummer',
                                'type' => 'text',
                                'value' => '',
                                'component' => 'niku-cms-readonly-customfield',
                                'css_class_row_wrapper' => 'col-md-6 col-sm-6',
                                'css_class_label' => 'col-md-12',
                                'css_class_input_wrapper' => 'col-md-12',
                                'validation' => '',
                            ],
            
                            'email' => [
                                'label' => 'E-mailadres',
                                'type' => 'text',
                                'value' => '',
                                'component' => 'niku-cms-readonly-customfield',
                                'css_class_row_wrapper' => 'col-md-6 col-sm-6',
                                'css_class_label' => 'col-md-12',
                                'css_class_input_wrapper' => 'col-md-12',
                                'validation' => '',
                            ],
            
                            'factuurgegevens_title' => [
                                'label' => 'Factuurgegevens',
                                'component' => 'niku-cms-title-customfield',
                                'css_class_row_wrapper' => 'col-md-12 col-sm-12',
                                'css_class_title_wrapper' => 'col-md-12',
                                'css_class_title' => 'h4',
                                'value' => '',
                            ],
            
                            'adres' => [
                                'label' => 'Adres',
                                'type' => 'text',
                                'value' => '',
                                'component' => 'niku-cms-readonly-customfield',
                                'css_class_row_wrapper' => 'col-md-9 col-sm-9',
                                'css_class_label' => 'col-md-12',
                                'css_class_input_wrapper' => 'col-md-12',
                                'validation' => '',
                            ],
            
                            'nummer' => [
                                'label' => 'Nummer',
                                'type' => 'text',
                                'value' => '',
                                'component' => 'niku-cms-readonly-customfield',
                                'css_class_row_wrapper' => 'col-md-3 col-sm-3',
                                'css_class_label' => 'col-md-12',
                                'css_class_input_wrapper' => 'col-md-12',
                                'validation' => '',
                            ],
            
                            'postcode' => [
                                'label' => 'Postcode',
                                'type' => 'text',
                                'value' => '',
                                'component' => 'niku-cms-readonly-customfield',
                                'css_class_row_wrapper' => 'col-md-4 col-sm-4',
                                'css_class_label' => 'col-md-12',
                                'css_class_input_wrapper' => 'col-md-12',
                                'validation' => '',
                            ],
            
                            'plaats' => [
                                'label' => 'Plaats',
                                'type' => 'text',
                                'value' => '',
                                'component' => 'niku-cms-readonly-customfield',
                                'css_class_row_wrapper' => 'col-md-4 col-sm-4',
                                'css_class_label' => 'col-md-12',
                                'css_class_input_wrapper' => 'col-md-12',
                                'validation' => '',
                            ],
            
                            'land' => [
                                'label' => 'Land',
                                'component' => 'niku-cms-readonly-customfield',
                                'css_class_row_wrapper' => 'col-md-4 col-sm-4',
                                'css_class_label' => 'col-md-12',
                                'css_class_input_wrapper' => 'col-md-12',
                                'options' => [
                                    'nl' => 'Nederland',
                                    'be' => 'Belgie',
                                    'de' => 'Duitsland',
                                ],
                                'value' => 'nl',
                                'validation' => '',
                            ],
            
                            'btw_nummer' => [
                                'label' => 'BTW nummer',
                                'type' => 'text',
                                'value' => '',
                                'component' => 'niku-cms-readonly-customfield',
                                'css_class_row_wrapper' => 'col-md-12 col-sm-12',
                                'css_class_label' => 'col-md-12',
                                'css_class_input_wrapper' => 'col-md-12',
                                'validation' => '',
                            ],
            
                            'betaalmethode_title' => [
                                'label' => 'Betaalmethode',
                                'component' => 'niku-cms-title-customfield',
                                'css_class_row_wrapper' => 'col-md-12 col-sm-12',
                                'css_class_title_wrapper' => 'col-md-12',
                                'css_class_title' => 'h4',
                                'value' => '',
                            ],
            
                            // 'payment_method' => [
                            //     'label' => 'Betaalmethode',
                            //     'component' => 'niku-cart-payment-methods-customfield',
                            //     'css_class_row_wrapper' => 'col-md-12 col-sm-12',
                            //     'css_class_label' => 'col-md-12',
                            //     'css_class_input_wrapper' => 'col-md-12',
                            //     'mutator' => 'App\Application\Custom\Cart\Mutators\PaymentMethodsMutator',                                  
                            //     'value' => '',
                            //     'validation' => 'required',
                            // ],
        
                            'shoppingcart' => [                                                
                                'validation' => '',
                                'saveable' => false,                        
                                'component' => 'niku-cart-shoppingcart-customfield',
                                'saveable' => false,                               
                                'main_table_wrapper' => 'margintopmedium',                               
                                'cart_items_update_api_url' => '',                        
                                'cart_items_delete_api_url' => '',                        
                                'mutator' => 'App\Application\Custom\Cart\Mutators\Checkout\ShoppingcartMutator',  
                                'to_checkout_button' => false,
                                'value' => '',
                            ],
        
                            'betalen' => [
                                'label' => 'Betalen',
                                'component' => 'niku-cart-checkout-submit-customfield',                                                                        
                                'validation' => '',
                                'api_url' => '',
                                'mutator' => 'App\Application\Custom\Cart\Mutators\Checkout\CheckoutUrlApiMutator',  
                                'saveable' => false,
                                'value' => '',
                            ],

                        ]
                    ],
                        
                ],
            ],
             
    	];	
    }

    public function override_edit_response($postId, $request, $response)
    {
        // Need to change the shopping cart to a order
        $cart = $this->fetchCartById($postId); 

        // Fetching all the products in the cart
        $cartItems = $this->fetchAllCartProducts($cart);
        
        // Changing the cart to a order
        $this->changeCartToOrder($cart, $cartItems);                

        // Lets validate if all the configurations are fullfulled
        $checkConfigurations = $this->override_show_post($cart->post_name, $request, 'shoppingcart');        
        if($checkConfigurations->getStatusCode() == 431) {
            return $checkConfigurations;
        }                       
        
        // Need to create a Mollie transaction
        // - Need to prepare the values for Mollie like redirection urls
        // - Need to make a function which is overridable so we can do some actions on the Mollie transaction event
        // - Need to save the Mollie transaction in our own table
        // - If there is a exception trown by Mollie, we need to log this

        // Redirect to thank you page
        dd($postId, $request->all(), $response);
    }
 
    protected function changeCartToOrder($cart, $cartItems)
    {
        // Changing the cart to a order
        // $cart->post_type = 'orders';
        // $cart->status = 'in_progress';
        $cart->save();
        
        // Changing the cart items to a order product
        foreach($cartItems as $key => $value){
            // $value->post_type = 'order-products';
            $value->save();
        }
    }
 
    // public function override_edit_response($postId, $request, $response)
    // {        
    //     $cart = $this->getCart($postId);        
    //     $items = $this->getAllCartProducts($cart);        

    //     $title = '';
    //     if(!empty($checkoutFields) && !empty($checkoutFields->postTitle)){
    //         foreach($checkoutFields->postTitle as $postTitle){
    //             $title .= $request->get($postTitle) . ' ';
    //         }
    //         $title = trim($title);
    //         $cart->post_title = $title;
    //     }

    //     // Lets move it from the shoppingcart to the order post type
    //     $cart->post_type = 'orders';
    //     $cart->status = 'in_progress';
    //     $cart->save();

    //     // Lets recalculate the total price of all the items in the shoppingcart
    //     $priceTotal = 0;
    //     foreach($items as $key => $value){
    //         $priceTotal += number_format($value->getMeta('price_total'), 2, '.', '');
    //     }

    //     // Lets whitelist the payment method
    //     switch($request->paymentMethod){
    //         default:
    //             $paymentMethod = 'ideal';
    //         break;
    //     }

    //     // Save the default requirements of the order and payment information
    //     $cart->saveMetas([
    //         'price_total' => $priceTotal,
    //         'payment_method' => $paymentMethod,
    //         'payment_status' => 'in_progress',
    //     ]);

    //     // Lets create a customer
    //     $customer = new NikuPosts;
    //     $customer->post_type = 'customers';
    //     $customer->post_title = $title;
    //     $customer->post_name = $title;
    //     $customer->save();

    //     foreach($request->only($checkoutKeys) as $checkoutKey => $checkoutValue){

    //         // Saving it to the database
    //         $object = [
    //             'meta_key' => $checkoutKey,
    //             'meta_value' => $checkoutValue,
    //             'group' => 'checkout',
    //         ];

    //         // Update or create the meta key of the post
    //         $customer->postmeta()->updateOrCreate([
    //             'meta_key' => $checkoutKey
    //         ], $object);

    //     }

    //     // Lets attach the customer to the order
    //     $customer->taxonomies()->attach($cart);

    //     $this->triggerEvent('order_customer_created', $checkoutFields, [
    //         'cart' => $cart,
    //         'customer' => $customer,
    //     ]);

    //     // Lets receive the redirect path by the users website config
    //     $website = $this->getWebsite($websiteId);
    //     $redirectUrlPath = $website->post_title . $website->getMeta('embed_redirect_path_thankyou');

    //     // Lets set some required values
    //     $redirectUrl = $redirectUrlPath . "?identifier=" . $cart->post_name;
    //     $webhookUrl = config('app.payment_webhook_url') . "api/cart/" . $website->post_name . "/order/payment/callback?identifier=" . $cart->post_name;
    //     $description = 'Bestelling ' . $cart->id;

    //     $this->triggerEvent('order_created', $checkoutFields, [
    //         'cart' => $cart,
    //         'customer' => $customer,
    //     ]);

    //     // Lets create a Mollie transaction
    //     try {

    //         $paymentMollie = Mollie::api()->payments()->create([
    //             "amount"      => $priceTotal,
    //             "description" => $description,
    //             "redirectUrl" => $redirectUrl,
    //             "webhookUrl" => $webhookUrl,
    //         ]);

    //         // Lets validate if there is a duplicate transaction id and if so, append it.
    //         $transactionCount = 1;

    //          // Lets create a Mollie transaction
    //         $mollie = new NikuPosts;
    //         $mollie->post_type = 'transactions';
    //         $mollie->post_title = $description;
    //         $mollie->post_name = $cart->post_name . '_' . $paymentMollie->id;
    //         $mollie->save();

    //         // Lets save the mollie transactions meta
    //         $mollie->saveMetas([
    //             'price_total' => $priceTotal,
    //             'price_total_received_by_payment_provider' => $paymentMollie->amount,
    //             'ip_address' => $request->ip(),
    //             'payment_identifier' => $paymentMollie->id,
    //             'payment_status' => $paymentMollie->status,
    //             'payment_created' => $paymentMollie->createdDatetime,
    //             'payment_links' => json_encode($paymentMollie->links),
    //         ]);

    //         // Lets attach the customer to the order
    //         $mollie->taxonomies()->attach($cart);

    //         $this->triggerEvent('order_create_payment_transaction_succeed', $checkoutFields, [
    //             'cart' => $cart,
    //             'transaction' => $mollie,
    //         ]);

    //         return response()->json([
    //             'status' => 'succesful',
    //             'redirect_url' => $paymentMollie->links->paymentUrl
    //         ]);
    //     }
    //     //catch exception
    //     catch( \Mollie_API_Exception $e) {

    //         $this->triggerEvent('order_create_payment_transaction_failure', $checkoutFields, [
    //             'cart' => $cart,
    //             'error' => $e->getMessage(),
    //         ]);

    //         // Need to create an event order has failed
    //         return response()->json([
    //             'status' => 'failed',
    //             'message' =>  'Message: ' .$e->getMessage(),
    //             'redirect_url' => $redirectUrl
    //         ], 500);
    //     }

    // }
 
}
