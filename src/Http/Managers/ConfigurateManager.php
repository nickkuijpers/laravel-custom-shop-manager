<?php

namespace Niku\Cart\Http\Managers;

use Validator;
use Illuminate\Http\Request;
use Niku\Cms\Http\NikuPosts;
use Niku\Cart\Http\Traits\CartTrait;
use Niku\Cms\Http\Controllers\cmsController;
use Niku\Cms\Http\Controllers\Cms\ShowPostController;

class ConfigurateManager extends NikuPosts
{
    use CartTrait;

    // The label of the custom post type
	public $label = 'Configurate';

    // Define the custom post type
    public $identifier = 'shoppingcart';

    // Users can only view their own posts when this is set to true
    public $userCanOnlySeeHisOwnPosts = false;

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

    public function override_show_post($id, $request, $postType)
    {		
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
					'errors' => 'You must be authenticated',
				], 431);
			}
        }

        $collection = ['templates' => $this->view];
        $collection['config'] = $this->config;
    
        switch($id) {
            case '0';
                 $toMerge = [];
            break;
            default: 
                $post = NikuPosts::where([
                    [ 'post_name' , '=', $id]
                ])->with('postmeta')->first();
                $collection['post'] = $post;
                $toMerge = [];
            break;
        }
    
        $collection = $this->helpers->addValuesToCollection($collection, $toMerge);        
        $collection = $this->helpers->showMutator($this, $collection, $request);
    
        return $collection;
    }
  
}
