<?php

namespace Niku\Cart\Http\Managers;

use Validator;
use Illuminate\Http\Request;
use Niku\Cms\Http\NikuPosts;
use Niku\Cart\Http\Traits\CartTrait;
use Niku\Cms\Http\Controllers\cmsController;
use Niku\Cms\Http\Controllers\Cms\CheckPostController;
use App\Application\Custom\Cart\PostTypes\CreateAccount;
use Mollie\Laravel\Facades\Mollie;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Application\Custom\Models\User;
use Niku\Cart\Http\Managers\CheckoutManager;

class CheckoutManager extends NikuPosts
{
	use CartTrait;

	// The label of the custom post type
	public $label = 'Checkout';

	// Define the custom post type
	public $identifier = 'shoppingcart';

	// Users can only view their own posts when this is set to true
	public $userCanOnlySeeHisOwnPosts = false;

	public $disableDefaultPostName = true;
	public $disableSanitizingPostName = true;
	public $makePostNameRandom = true;

	public $view;
	public $helpers;

	public $getPostByPostName = true;

	public $enableAllSpecificFieldsUpdate = true;
	public $excludeSpecificFieldsFromUpdate = [];

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

	public function validateShow($id, $request, $cart)
    {
		$continue = true;

		$cartItems = $this->fetchAllCartProducts($cart);

		$errors = [];
		foreach($cartItems as $cartKey => $cartValue){

			$values = [];
			$postType = $cartValue->template;
			$id = $cartValue->id;
			$validation = (new CheckPostController)->internal($values, $postType, $id);

			if(optional($validation)->code && $validation->code == "failure"){
				$continue = false;
				$errors[$cartValue->post_title] = $validation->errors;
			}

		}

		if($continue === true){
			$message = '';
		} else {
			$message = 'U heeft de configuratie benodigdheden niet correct ingevuld.';
		}

		return [
			'continue' => $continue,
			'errors' => $errors,
			'message' => $message,
		];

    }

	public function override_show_post($id, $request, $postType)
    {
		$cart = $this->fetchCart($id);
        if(empty($cart)){
            return $this->abort('The shoppingcart could not be found.', 422);
		}

		// Lets validate if authentication is required
		$authenticationRequired = config('niku-cart.authentication.required');
		if($authenticationRequired === true){
			$user = $request->user('api');
			if(!$user){
				return response()->json([
					'code' => 'error',
					'redirect_to' => [
						'name' => 'checkout-login',
					],
					'errors' => [
						'auth' => ['You must be authenticated'],
					],
				], 431);
			}
		}

		// Lets check if there are any products which have not been passed the configurations yet
		$onCheck = $this->validateShow($id, $request, $cart);
        if($onCheck['continue'] === false){
            if(array_key_exists('message', $onCheck)){
                $message = $onCheck['message'];
            } else {
                $message = 'You are not authorized to do this.';
            }

			$toSave = [
				'errors' => json_encode($onCheck['errors'])
			];
			$cart->saveMetas($toSave);

			return response()->json([
				'code' => 'error',
				'redirect_to' => [
					'name' => 'configure',
				],
				'errors' => $onCheck['errors'],
			], 431);
        }

		// Getting the custom fields
		$customFields = $this->view;

		// Creating the collection
		$collection = [];
		$collection['post'] = $cart;
		$collection['templates'] = $customFields;
        $collection['config'] = $this->config;

		// Merging existing values
		$toMerge = [];
		$toMerge['post_title'] = [
			'value' => $cart->post_title,
		];

		foreach($cart->postmeta as $cartmetaKey => $cartmetaValue){
			$toMerge[$cartmetaValue->meta_key]['value'] = $cartmetaValue->meta_value;
		}

		// Executing default methods to merge and mutate
        $collection = $this->helpers->addValuesToCollection($collection, $toMerge);
        $collection = $this->helpers->showMutator($this, $collection, $request);

		// Unset unrequired items
        unset($collection['templates']['listing']);

		$collection['instance'] = [
			'post_type' => $postType,
			'post_identifier' => $id,
		];

        return response()->json($collection);
    }

	/**
     * Handling the editting of the order
     */
    public function override_edit_response($postId, $request, $response)
    {
        // Validate if we need authentication
        $authenticationRequired = config('niku-cart.authentication.required');
		if($authenticationRequired === true){
			$user = $request->user('api');
			if(!$user){
				return response()->json([
					'code' => 'error',
					'redirect_to' => [
						'name' => 'checkout-login',
					],
					'errors' => 'You must be authenticated',
				], 431);
			}
        }

        // Need to change the shopping cart to a order
        $cart = $this->fetchCartById($postId);

        // Fetching all the products in the cart
        $cartItems = $this->fetchAllCartProducts($cart);

        // // Lets validate if the user details are all filled in
        $checkUsersDetails = (new CreateAccount)->override_check_post($cart->post_name, $request);
        if($checkUsersDetails->getStatusCode() !== 200) {
            return $checkUsersDetails;
        }

        // Lets validate if all the configurations are fullfulled
        $checkConfigurations = $this->override_show_post($cart->post_name, $request, 'shoppingcart');
        if($checkConfigurations->getStatusCode() !== 200) {
            return $checkConfigurations;
        }

        // Lets validate if the payment method is set and if not, redirect to the payment page
        if(empty($cart->getMeta('payment_method'))){
            return response()->json([
                'code' => 'error',
                'redirect_to' => [
                    'name' => 'payment-method',
                ],
                'errors' => [
                    'payment_method' => ['De betaalmethode is verplicht om af te kunnen rekenen'],
                ],
            ], 431);
        }

        // Changing the cart to a order
        $order = $this->changeCartToOrder($cart, $cartItems, $request);

        // Lets fetch all the users details and set that to the order
        $this->connectUserToOrder($order, $user);

        // Need to create a Mollie transaction
        $transaction = $this->createMollieTransaction($order, $user);
        if($transaction->created === false){

            // Lets trigger a function which we can hook into to save some information
            $this->trigger_mollie_transaction_failed($transaction->error);

            // Logging the error
            Log::emergency($order->id . ' - ' . json_encode($transaction->error));

            return response()->json([
                'code' => 'error',
                'errors' => [
                    'payment_api' => ['Er is geen transactie aangemaakt. Neemt u contact met ons op.'],
                    'hint' => [$transaction->error],
                ],
            ], 422);

        }

        // Redirect to thank you page
        return response()->json([
            'redirection_url' => $transaction->redirection_url
        ], 200);
    }

    protected function changeCartToOrder($cart, $cartItems, $request)
    {
        // Fetching all the prices and details
        $mutatedCart = $this->fetchMutatedCart($cart, 'order');

        // Changing the cart to a order
        $cart->post_type = 'order';
        $cart->status = 'in_progress';

        // Saving the changes made to the order
        $cart->save();

        // Setting the values to save as order meta
        $toSave = [];
        $toSave['ip_address'] = $request->ip();
        $toSave['payment_status'] = 'in_progress';
        $toSave['price_subtotal'] = $mutatedCart['cart_price_subtotal'];
        $toSave['price_tax'] = $mutatedCart['cart_price_tax'];
        $toSave['price_total'] = $mutatedCart['cart_price_total'];
        $toSave['items'] = json_encode($mutatedCart['items']);

        // Saving the metas to the order
        $cart->saveMetas($toSave);

        // Changing the cart items to a order product
        foreach($cartItems as $key => $value){
            $value->post_type = 'order-products';
            $value->save();
        }

        // Lets refetch the order
        $order = NikuPosts::where([
            ['id', '=', $cart->id],
            ['post_type', '=', 'order']
        ])->with('postmeta')->first();

        return $order;
    }

    protected function connectUserToOrder($order, $user)
    {
        // Fetching all the users values and setting the values out of the array
        $userValues = collect((new CreateAccount)->getUserValues($user))->map(function($item, $key){
            return $item['value'];
        });

        // Connecting the user to the order
        $order->post_author = $user->id;
        $order->save();

        // Saving the users values to the order
        $order->saveMetas($userValues);
    }

    protected function createMollieTransaction($order, $user)
    {
        // Setting the urls
        $redirectUrl = config('niku-cart.mollie.redirect_url') . '/thankyou?order=' . $order->post_name . '&url=/thankyou?order=' . $order->post_name;
        $webhookUrl = config('niku-cart.mollie.webhook_url') . '/cpm/checkout/show/' . $order->post_name . '/mollie_webhook';

        // Setting the description
        $description = 'Bestelling ' . $order->id;

        // Fetching the and setting the price
        $priceTotal = filter_var(number_format($order->getMeta('price_total'), 2, '.', ''), FILTER_VALIDATE_FLOAT);

        // Fetching the payment method
        $paymentMethod = $order->getMeta('payment_method');

        // Lets create a Mollie transaction
        try {

            $paymentMollie = Mollie::api()->payments()->create([
                "amount"      => $priceTotal,
                "description" => $description,
                "redirectUrl" => $redirectUrl,
                "webhookUrl" => $webhookUrl,
                "method" => $paymentMethod,
            ]);

            // Lets mutate the order so its easier to look at in the database
            $mutatedOrder = $this->mutateOrder($order);

            // Saving the transaction
            $mollie = new NikuPosts;
            $mollie->post_type = 'mollie_transaction';
            $mollie->post_title = $description;
            $mollie->status = $paymentMollie->status;
            $mollie->post_parent = $order->id;
            $mollie->post_password = $paymentMollie->id;
            $mollie->post_author = $user->id;
            $mollie->post_content = json_encode($mutatedOrder);
            $mollie->post_excerpt = $priceTotal;
            $mollie->post_name = $order->post_name;
            $mollie->save();

            // Saving the transaction metas
            $toSave = [];
            $toSave['price_total'] = $priceTotal;
            $toSave['price_total_received_by_payment_provider'] = $paymentMollie->amount;
            $toSave['description'] = $description;
            $toSave['ip_address'] = $order->getMeta('ip_address');
            $toSave['redirectUrl'] = $redirectUrl;
            $toSave['webhookUrl'] = $webhookUrl;
            $toSave['mollie_id'] = $paymentMollie->id;
            $toSave['mollie_response'] = json_encode($paymentMollie);
            $toSave['webhook_url'] = $paymentMollie->links->webhookUrl;
            $toSave['redirection_url'] = $paymentMollie->links->redirectUrl;
            $toSave['payment_url'] = $paymentMollie->links->paymentUrl;
            $mollie->saveMetas($toSave);

            // Lets trigger a function which we can hook into to save some information
            $this->trigger_mollie_transaction_created($mutatedOrder, $paymentMollie, $user);

            // Lets redirect to the payment url
            return (object) [
                'created' => true,
                'redirection_url' => $paymentMollie->links->paymentUrl,
            ];
        }

        // Catch the exception
        catch( \Mollie_API_Exception $e) {

            return (object) [
                'created' => false,
                'error' => $e->getMessage(),
            ];
        }


    }

    public function show_custom_get_mollie_webhook($request, $id, $customId, $post)
    {
        // Validating the input
        Validator::make([
            'id' => $id,
            'transaction_id' => $request->id
        ], [
            'id' => 'required',
            'transaction_id' => 'required',
        ])->validate();

        // Setting the order
        $order = $post;
        $transactionId = $request->id;

        // Fetching the transaction
        $transaction = NikuPosts::where([
            ['post_parent', '=', $order->id],
            ['post_name', '=', $order->post_name],
            ['post_password', '=', $transactionId],
        ])->with('postmeta')->first();

        // Return a error when the transaction is not found
        if(empty($transaction)){
            return response()->json([
                'errors' => [
                    'transaction' => 'De transactie is niet gevonden',
                ]
            ], 422);
        }

        // Fetching the payment status
        $paymentMollie = Mollie::api()->payments()->get($transactionId);

        // Validating if the payment is expired
        if($paymentMollie->status == 'expired'){
            $transactionExpired = true;
        } else {
            $transactionExpired = false;
        }

        // Saving the transaction with the new status
        $transaction->status = $paymentMollie->status;
        $transaction->template = $transactionExpired;
        $transaction->save();

        // Lets change the status of the payment
        $order->post_mime_type = $paymentMollie->status;
        $order->save();

        $this->trigger_mollie_transaction_webhook($order, $paymentMollie, $transaction);

        return response()->json([
        	'message' => 'Payment status updated.'
        ], 200);

    }

    // Empty function to override in the checkout class
    public function trigger_mollie_transaction_created($order, $mollie, $user)
    {

    }

    // Empty function to override in the checkout class
    public function trigger_mollie_transaction_failed($error)
    {

    }

    // Empty function to override in the checkout class
    public function trigger_mollie_transaction_webhook($order, $paymentMollie, $transaction)
    {

    }

}
