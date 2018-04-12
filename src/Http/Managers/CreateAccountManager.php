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
	
	public $label = 'Create account';
	public $identifier = 'user';
	public $disableDefaultPostName = true;		
	public $getPostByPostName = true;

	public $enableAllSpecificFieldsUpdate = false;    

	public $view;
	public $helpers;

	public function __construct()
	{
		$this->helpers = new cmsController;
		$this->view = $this->view();
	}

	// public function override_edit_response($postId, $request, $response)
	// {

	// }

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
