<?php

namespace Niku\Cart\Http\Controllers\Cart;

use Niku\Cart\Http\Controllers\CartController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Niku\Cms\Http\NikuPosts;

class OrderConfigController extends CartController
{
    public function handle()
    {
        return $this->GetCheckoutTemplate('default')->view['default'];
    }
}
