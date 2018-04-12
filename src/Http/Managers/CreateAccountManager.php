<?php

namespace Niku\Cart\Http\Managers;

use Validator;
use Illuminate\Http\Request;
use Niku\Cms\Http\NikuPosts;
use Niku\Cart\Http\Traits\CartTrait;
use App\Application\Custom\Models\User;
use Niku\Cms\Http\Controllers\cmsController;
use Niku\Cms\Http\Controllers\Cms\CheckPostController;

class CreateAccountManager extends NikuPosts
{
	use CartTrait;

	// The label of the custom post type
	public $label = 'Create account';

	// Define the custom post type
	public $identifier = 'user';

	// Users can only view their own posts when this is set to true
	public $userCanOnlySeeHisOwnPosts = false;

	public $disableDefaultPostName = true;
	public $disableSanitizingPostName = true;
	public $makePostNameRandom = true;

	public $view;
	public $helpers;

	public $getPostByPostName = true;

	public $enableAllSpecificFieldsUpdate = false;    
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

    public function override_create_post($request)
    {                
        $cart = $this->fetchCart($request->cart_identifier);        

         // Validating the request
        $validationRules = $this->helpers->validatePostFields($request->all(), $request, $this);
        Validator::make($request->all(), $validationRules)->validate();        

        $sanitizedKeys = collect($this->helpers->getValidationsKeys($this))->keys()->toArray();
        $requestOnly = $request->only($sanitizedKeys);

        $customer = new User;
        $customer->email = data_get($requestOnly, 'email');        
        $customer->first_name = data_get($requestOnly, 'voornaam');
        $customer->last_name = data_get($requestOnly, 'achternaam');

        // Validating if the password does exist and if it is required
        if(array_key_exists('password', $requestOnly)){            
            $password = $requestOnly['password'];    
        } else {
            $password = str_random(30);                    
        }
        
        $customer->password = bcrypt($password);
        $customer->save();
    
        // Saving all the other metas
        $toSave = [];
        foreach($requestOnly as $key => $value){
            if(empty($value)){
                $toSave[$key] = $value;
            }            
        }

        // Saving all the metas
        $customer->saveMetas($toSave);        

        // Setting the user role
        $customer->assignRole('default');
        
        $configurationsRequest = $this->configurationsRequired($cart, $request);        
        if($configurationsRequest === true){
            $linkTo = 'configure';
        } else {
            $linkTo = 'checkout';
        }

        // Returning the response
        return response()->json([
            'redirect_to' => [
                'name' => $linkTo,
            ],
        ]);
    }
}
