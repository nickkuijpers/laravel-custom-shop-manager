<?php

namespace Niku\Cms\Http\Controllers\Cart;

use App\Application\Custom\Controllers\Cart\CartController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Mollie\Laravel\Facades\Mollie;
use Niku\Cms\Http\NikuPosts;

class OrderCreateController extends CartController
{
    public function handle(Request $request, $websiteId)
    {
        $cart = $this->getCart($request->cart_identifier);
        if(empty($cart)){
            return $this->abort('The shoppingcart could not be found.', 422);
        }

        $items = $this->getAllCartProducts($cart);
        if($items->isEmpty()){
            return $this->abort('There are no items in the shoppingcart.', 422);
        }

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

        $this->validate($productRequest, $validationRules);

        // Validating the checkout fields
        $checkoutFields = $this->GetCheckoutTemplate('default');

        $checkoutValidation = [];
        $checkoutKeys = [];
        foreach($checkoutFields->view['default']['customFields'] as $checkoutKey => $checkoutValue){
            if(!empty($checkoutValue['validation'])){
                $checkoutValidation[$checkoutKey] = $checkoutValue['validation'];
            }
            $checkoutKeys[] = $checkoutKey;
        }

        $this->validate($request, $checkoutValidation);

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
}
