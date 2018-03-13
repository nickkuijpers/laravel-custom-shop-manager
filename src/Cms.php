<?php

namespace Niku\Cms;

use Illuminate\Support\Facades\Route;
use Niku\Cms\CmsRoutes;

class Cms
{
    public static function postTypeRoutes($postTypeConfig = [])
    {
		// Crud listing all posts by post type
		Route::post('/{post_type}', '\Niku\Cms\Http\Controllers\Cms\ListPostsController@init')->name('list');

		Route::group([
			'as' => '/{websiteId}',
			'prefix' => '/{websiteId}',
		], function(){

			Route::group([
				'as' => '/products',
				'prefix' => '/products',
			], function(){
				Route::post('/show', '\Niku\Cms\Http\Controllers\ProductsShowController@handle')->name('list');
			});

				Route::group([
					'as' => '/cart',
				], function(){

					Route::post('/create', '\Niku\Cms\Http\Controllers\CartCreateController@handle');
					Route::post('/show', '\Niku\Cms\Http\Controllers\CartShowController@handle');

					Route::post('/items/add', '\Niku\Cms\Http\Controllers\CartItemsAddController@handle');
					Route::post('/items/update', '\Niku\Cms\Http\Controllers\CartItemsUpdateController@handle');
					Route::post('/items/config', '\Niku\Cms\Http\Controllers\CartItemsConfigController@handle');
					Route::post('/items/config-status', '\Niku\Cms\Http\Controllers\CartItemsConfigStatusController@handle');
					Route::post('/items/delete', '\Niku\Cms\Http\Controllers\CartItemsDeleteController@handle');
				});

				Route::group([
					'as' => '/order',
					'prefix' => '/order',
				], function(){
					Route::post('/fields', '\Niku\Cms\Http\Controllers\OrderConfigController@handle')->name('orderconfigs');
					Route::post('/create', '\Niku\Cms\Http\Controllers\OrderCreateController@handle')->name('list');
					Route::post('/show', '\Niku\Cms\Http\Controllers\OrderShowController@handle')->name('show');
					Route::post('/payment/callback', '\Niku\Cms\Http\Controllers\OrderPaymentCallbackController@handle')->name('paymentcallback');
				});

			});
    }
}
