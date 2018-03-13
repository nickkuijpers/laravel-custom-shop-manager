<?php

namespace Niku\Cms\Http\Controllers\Cart;

use Illuminate\Http\Request;
use Niku\Cms\Http\NikuPosts;
use App\Http\Controllers\Controller;

class CartCreateController extends Controller
{
    public function handle(Request $request)
    {
        // Before we can save it, we need to validate that it
        // is really unique by searching the databsae for it.
        $done = 0;
        while (!$done) {

            // If there is no shopping cart available yet, we need to
            // create a unique session so we can identify the cart.
            $randomString = uniqid(str_random(40));

            // Set done when the result is 0 from the query we do
            // to validate if the unique string is unique.
            $cartSearchUnique = NikuPosts::where([
                ['post_type', '=', 'shoppingcart'],
                ['post_name', '=', $randomString],
            ])->count();

            // If there is any result, we do it again!
            if($cartSearchUnique === 0){
                $done = 1;
            }
        }

        // Add it with a random and prefix to custom post type
        $post = new NikuPosts;
        $post->post_name = $randomString;
        $post->post_type = 'shoppingcart';
        $post->save();

        $post->saveMetas([
            'ip_address' => $request->ip(),
            'time_created' => now(),
        ]);

        // Lets return the response
        return response()->json([
            'cart_identifier' => $post->post_name
        ], 200);
    }
}
