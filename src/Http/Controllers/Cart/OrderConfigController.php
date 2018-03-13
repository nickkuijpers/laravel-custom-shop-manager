<?php

namespace Niku\Cms\Http\Controllers\Cart;

use App\Application\Custom\Controllers\Cart\CartController;
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
