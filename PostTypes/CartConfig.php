<?php

namespace App\Application\Custom\Cart;

use Niku\Cms\Http\NikuPosts;

class CartConfig
{
     public $events = [
        'item_added_to_cart' => [
            'App\Application\Custom\Events\Cart\ItemAddedToCartEvent',
        ],
        'item_deleted_from_cart' => [
            'App\Application\Custom\Events\Cart\ItemDeletedFromCartEvent',
        ],
    ];
}
