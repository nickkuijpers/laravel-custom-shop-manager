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

    public $config = [
        'skip_to_route_name' => false,
    ];

	public function __construct()
	{
		$this->helpers = new cmsController;
		$this->view = $this->view();
    }
    
    public function override_show_post($id, $request)
    {
        $collection = ['templates' => $this->view];
        $collection['config'] = $this->config;
    
        $user = $request->user('api');
        if($user){
            $post = User::where([
                [ 'id' , '=', $user->id]
            ])->with('meta')->first();        
            $collection['post'] = $post;
            $toMerge = [
                'first_name' => [ 
                    'value' => $post->first_name 
                ],
                'last_name' => [ 
                    'value' => $post->last_name 
                ],
                'email' => [ 
                    'value' => $post->email 
                ],
                'address' =>[
                  'value' => $post->getMeta('address')
                ]
            ];

            $collection = $this->helpers->addValuesToCollection($collection, $toMerge);
            $collection = $this->helpers->showMutator($this, $collection, $request);

            // We need to unset the password if we are logged in already
            unset($collection['templates']['default']['customFields']['form_wrapper']['customFields']['password']);            

        } else {
            $toMerge = [];

            $collection = $this->helpers->addValuesToCollection($collection, $toMerge);
            $collection = $this->helpers->showMutator($this, $collection, $request);
        }        
        
        return $collection;
    }

    public function override_edit_post($id, $request) 
    {        
        $cart = $this->fetchCart($request->cart_identifier);        
        
        $user = $request->user('api');
        $user = User::where([
            ['id', '=', $user->id],
        ])->first();
        
        if(!$user){
            return response()->json([
                'errors' => [
                    'user' => ['Je bent niet ingelogd.'],
                ],
                'message' => 'You are not authenticated',
            ], 422);
        }

        // Validating the request
        $validationRules = $this->helpers->validatePostFields($request->all(), $request, $this);

        // Make sure a user can edit his own e-mail
        $validationRules['email'] = 'required|email|unique:users,email,' . $user->id;                          
        
        // Unset the password
        unset($validationRules['password']);

        // Execute the validations
        Validator::make($request->all(), $validationRules)->validate();

        $sanitizedKeys = collect($this->helpers->getValidationsKeys($this))->keys()->toArray();
        $requestOnly = $request->only($sanitizedKeys);

        // Lets save the changes
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        $user->save();        
        
        $toSave = [];
        foreach($requestOnly as $key => $value){
            if(empty($value)){
                $toSave[$key] = $value;
            }            
        }
        $user->saveMetas($toSave);        
    
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
