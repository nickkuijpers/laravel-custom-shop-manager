<?php

namespace Niku\Cart;

use Illuminate\Support\Facades\Route;

class Cart
{
    public static function cartRoutes()
    {
		Route::group([
			'as' => '/cart',
			'prefix' => '/cart',
		], function(){			

			Route::group([
				'as' => '/cart',
			], function(){

				// Route::post('/create', '\Niku\Cart\Http\Controllers\Cart\CartCreateController@handle');
				// Route::post('/show', '\Niku\Cart\Http\Controllers\Cart\CartShowController@handle');

				// Route::post('/items/add', '\Niku\Cart\Http\Controllers\Cart\CartItemsAddController@handle');
				// Route::post('/items/update', '\Niku\Cart\Http\Controllers\Cart\CartItemsUpdateController@handle');
				// Route::post('/items/config', '\Niku\Cart\Http\Controllers\Cart\CartItemsConfigController@handle');
				// Route::post('/items/config-status', '\Niku\Cart\Http\Controllers\Cart\CartItemsConfigStatusController@handle');
				// Route::post('/items/delete', '\Niku\Cart\Http\Controllers\Cart\CartItemsDeleteController@handle');
			});

			Route::group([
				'as' => '/order',
				'prefix' => '/order',
			], function(){
				Route::post('/fields', '\Niku\Cart\Http\Controllers\Cart\OrderConfigController@handle')->name('orderconfigs');
				Route::post('/create', '\Niku\Cart\Http\Controllers\Cart\OrderCreateController@handle')->name('list');
				Route::post('/show', '\Niku\Cart\Http\Controllers\Cart\OrderShowController@handle')->name('show');
				Route::post('/payment/callback', '\Niku\Cart\Http\Controllers\Cart\OrderPaymentCallbackController@handle')->name('paymentcallback');
			});

		});
    }
}
