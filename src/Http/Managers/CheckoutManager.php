<?php

namespace Niku\Cart\Http\Managers;

use Validator;
use Illuminate\Http\Request;
use Niku\Cms\Http\NikuPosts;
use Niku\Cart\Http\Traits\CartTrait;
use Niku\Cms\Http\Controllers\cmsController;

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

	/**
	 * Doing validations before being able to checkout
	 */
	public function on_edit_check($postTypeModel, $postId, $request)
	{
		$cart = $this->getCart($postId);
		if(empty($cart)){
			return $this->abort('The shoppingcart could not be found.', 422);
		}

		$items = $this->getAllCartProducts($cart);
		if($items->isEmpty()){
			return $this->abort('There are no items in the shoppingcart.', 422);
		}

		$validateProducts = $this->validateProducts($items);
	}

	public function override_edit_response($postId, $request, $response)
	{
		$cart = $this->getCart($postId);
		$items = $this->getAllCartProducts($cart);


		$title = '';
		if(!empty($checkoutFields) && !empty($checkoutFields->postTitle)){
			foreach($checkoutFields->postTitle as $postTitle){
				$title .= $request->get($postTitle) . ' ';
			}
			$title = trim($title);
			$cart->post_title = $title;
		}

		// Lets move it from the shoppingcart to the order post type
		$cart->post_type = 'orders';
		$cart->status = 'in_progress';
		$cart->save();

		// Lets recalculate the total price of all the items in the shoppingcart
		$priceTotal = 0;
		foreach($items as $key => $value){
			$priceTotal += number_format($value->getMeta('price_total'), 2, '.', '');
		}

		// Lets whitelist the payment method
		switch($request->paymentMethod){
			default:
				$paymentMethod = 'ideal';
			break;
		}

		// Save the default requirements of the order and payment information
		$cart->saveMetas([
			'price_total' => $priceTotal,
			'payment_method' => $paymentMethod,
			'payment_status' => 'in_progress',
		]);

		// Lets create a customer
		$customer = new NikuPosts;
		$customer->post_type = 'customers';
		$customer->post_title = $title;
		$customer->post_name = $title;
		$customer->save();

		foreach($request->only($checkoutKeys) as $checkoutKey => $checkoutValue){

			// Saving it to the database
			$object = [
				'meta_key' => $checkoutKey,
				'meta_value' => $checkoutValue,
				'group' => 'checkout',
			];

			// Update or create the meta key of the post
			$customer->postmeta()->updateOrCreate([
				'meta_key' => $checkoutKey
			], $object);

		}

		// Lets attach the customer to the order
		$customer->taxonomies()->attach($cart);

		$this->triggerEvent('order_customer_created', $checkoutFields, [
			'cart' => $cart,
			'customer' => $customer,
		]);

		// Lets receive the redirect path by the users website config
		$website = $this->getWebsite($websiteId);
		$redirectUrlPath = $website->post_title . $website->getMeta('embed_redirect_path_thankyou');

		// Lets set some required values
		$redirectUrl = $redirectUrlPath . "?identifier=" . $cart->post_name;
		$webhookUrl = config('app.payment_webhook_url') . "api/cart/" . $website->post_name . "/order/payment/callback?identifier=" . $cart->post_name;
		$description = 'Bestelling ' . $cart->id;

		$this->triggerEvent('order_created', $checkoutFields, [
			'cart' => $cart,
			'customer' => $customer,
		]);

		// Lets create a Mollie transaction
		try {

			$paymentMollie = Mollie::api()->payments()->create([
				"amount"      => $priceTotal,
				"description" => $description,
				"redirectUrl" => $redirectUrl,
				"webhookUrl" => $webhookUrl,
			]);

			// Lets validate if there is a duplicate transaction id and if so, append it.
			$transactionCount = 1;

			 // Lets create a Mollie transaction
			$mollie = new NikuPosts;
			$mollie->post_type = 'transactions';
			$mollie->post_title = $description;
			$mollie->post_name = $cart->post_name . '_' . $paymentMollie->id;
			$mollie->save();

			// Lets save the mollie transactions meta
			$mollie->saveMetas([
				'price_total' => $priceTotal,
				'price_total_received_by_payment_provider' => $paymentMollie->amount,
				'ip_address' => $request->ip(),
				'payment_identifier' => $paymentMollie->id,
				'payment_status' => $paymentMollie->status,
				'payment_created' => $paymentMollie->createdDatetime,
				'payment_links' => json_encode($paymentMollie->links),
			]);

			// Lets attach the customer to the order
			$mollie->taxonomies()->attach($cart);

			$this->triggerEvent('order_create_payment_transaction_succeed', $checkoutFields, [
				'cart' => $cart,
				'transaction' => $mollie,
			]);

			return response()->json([
				'status' => 'succesful',
				'redirect_url' => $paymentMollie->links->paymentUrl
			]);
		}
		//catch exception
		catch( \Mollie_API_Exception $e) {

			$this->triggerEvent('order_create_payment_transaction_failure', $checkoutFields, [
				'cart' => $cart,
				'error' => $e->getMessage(),
			]);

			// Need to create an event order has failed
			return response()->json([
				'status' => 'failed',
				'message' =>  'Message: ' .$e->getMessage(),
				'redirect_url' => $redirectUrl
			], 500);
		}

	}

	protected function getCart($cartIdentifier)
	{
		$cart = NikuPosts::where([
			['post_type', '=', 'shoppingcart'],
			['id', '=', $cartIdentifier],
		])->with('postmeta')->first();

		return $cart;
	}

	protected function getAllCartProducts($cart)
	{
		$cartProducts = $cart->posts()->where([
			['post_type', '=', 'shoppingcart-products']
		])->with('postmeta')->get();

		return $cartProducts;
	}

	protected function GetProductTemplate($template)
	{
		// Receive the config variable where we have whitelisted all models
		$cartTemplates = config('niku-cart');

		// Validating if the model exists in the array
		if(!array_key_exists($template, $cartTemplates['templates'])){
			return false;
		}

		return (new $cartTemplates['templates'][$template]['class']);
	}

	protected function validateProducts($items)
	{
		// Validaitng all the product configurations
		$validationRules = [];
		$productRequest = new Request;

		foreach($items as $productValue){

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

							$validationRules[$newCustomKey] = $productTemplate->view['default']['customFields'][$customKey]['validation'];
							$productRequest[$newCustomKey] = $meta->meta_value;
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
							$productRequest[$newCustomKey] = $meta->meta_value;
						}
					}
				}
			}
		}

		Validator::make($productRequest->all(), $validationRules)->validate();
	}

}
