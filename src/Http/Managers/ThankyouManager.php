<?php

namespace Niku\Cart\Http\Managers;

use Validator;
use Illuminate\Http\Request;
use Niku\Cms\Http\NikuPosts;
use Mollie\Laravel\Facades\Mollie;
use Illuminate\Support\Facades\Log;
use Niku\Cart\Http\Traits\CartTrait;
use App\Application\Custom\Models\User;
use Niku\Cms\Http\Controllers\cmsController;
use App\Application\Custom\Cart\PostTypes\Checkout;
use Niku\Cms\Http\Controllers\Cms\CheckPostController;

class ThankyouManager extends NikuPosts
{
	use CartTrait;

	public $label = 'Bedankt';
	public $identifier = 'order';
	public $disableDefaultPostName = true;
	public $getPostByPostName = true;

	public $enableAllSpecificFieldsUpdate = false;

	public $view;
	public $helpers;

    public $config = [
        'skip_to_route_name' => false,
    ];

	public function __construct()
	{
		$this->helpers = new cmsController;
		$this->view = $this->view();
    }

    public function edit_custom_post_check_status($request)
    {
        Validator::make($request->all(), [
            'cart_identifier' => 'required',
        ])->validate();

        $order = $this->fetchOrder($request->cart_identifier);
        if(empty($order)){
            return $this->abort('The shoppingcart could not be found.', 422);
        }

        $paymentStatus = $order->post_mime_type;

        switch($paymentStatus){
            case 'paid':
                $paymentStatus = 'Betaald';
            break;
            case 'open':
                $paymentStatus = 'Open';
            break;
            case 'in_progress':
                $paymentStatus = 'In afwachting';
            break;
            default:
                $paymentStatus = 'In afwachting';
            break;
        }

        return response()->json([
            'payment_status' => $paymentStatus,
        ]);
    }

    public function show_custom_get_mollie_webhook($request, $id, $customId, $post)
    {
        // Lets log the webhook
        Log::info($request);

        // Validating the input
        $validator = Validator::make([
            'id' => $id,
            'transaction_id' => $request->id
        ], [
            'id' => 'required',
            'transaction_id' => 'required',
        ]);

        if(!$validator->passes()){
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

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

        $toSave = [];
        $toSave['payment_code'] = $transaction->post_password;
        $order->saveMeta($toSave);

        $this->trigger_payment_changed_method($order, $paymentMollie, $transaction);

        return response()->json([
        	'message' => 'Payment status updated.'
        ], 200);
    }

    // Empty function to override in the checkout class
    public function trigger_payment_changed_method($order, $paymentMollie, $transaction)
    {

    }
}
