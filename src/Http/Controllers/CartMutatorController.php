<?php

namespace Niku\Cart\Http\Controllers;

use Niku\Cart\Http\Traits\CartTrait;
use Niku\Cms\Http\Controllers\cmsController;

class CartMutatorController extends cmsController
{
	use CartTrait;

	public $helpers;

	public function __construct()
	{
		$this->helpers = new cmsController;
	}
}
