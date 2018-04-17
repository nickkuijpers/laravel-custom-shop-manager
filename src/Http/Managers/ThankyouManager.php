<?php

namespace Niku\Cart\Http\Managers;

use Validator;
use Illuminate\Http\Request;
use Niku\Cms\Http\NikuPosts;
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
}
