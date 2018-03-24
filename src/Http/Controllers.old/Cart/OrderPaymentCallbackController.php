<?php

namespace Niku\Cart\Http\Controllers\Cart;

use Niku\Cart\Http\Controllers\CartController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Mollie\Laravel\Facades\Mollie;
use Niku\Cms\Http\NikuPosts;

class OrderPaymentCallbackController extends CartController
{
    public function handle(Request $request, $websiteIdentifier)
    {
        $order = $this->getOrder($request->identifier);
        if(empty($order)){
            return $this->abort('The order could not be found.', 422);
        }

        $website = $this->getWebsite($websiteIdentifier);
        if(!$website){
            return $this->abort('Website does not exist or is inactive.', 422);
        }

    	$transaction = $this->getTransaction($request->identifier . '_' . $request->get('id'));
        if(!$transaction){
            return $this->abort('Transaction does not exist.', 422);
        }

         // Receiving the payment status
        $paymentMollie = Mollie::api()->payments()->get($transaction->getMeta('payment_identifier'));

        $checkoutFields = $this->GetCheckoutTemplate('default');

        switch($paymentMollie->status){
            case 'paid':
                $status = 'paid';
            break;
            case 'cancelled':
                $status = 'cancelled';
            break;
            case 'expired':
                $status = 'expired';
            break;
            default:
                $status = 'pending';
            break;
        }

        $transaction->status = $status;
        $transaction->save();

        $transaction->saveMetas([
        	'payment_status' => $status,
        ]);

        // Lets fire a payment status based event
        $this->triggerEvent('order_payment_callback_' . $status, $checkoutFields, [
            'payment_response' => $paymentMollie,
            'transaction' => $transaction,
            'status' => $status,
            'order' => $order,
        ]);

        return response()->json([
        	'message' => 'Payment status updated.'
        ]);
    }
}
