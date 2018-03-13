<?php

namespace App\Application\Custom\Cart\Templates;

use App\Application\Custom\Cart\CartConfig;
use Niku\Cms\Http\NikuPosts;

class Simple extends CartConfig
{
	// The label of the custom post type
	public $label = 'Standaard';

	public $identifier = 'standaard';

	public $configPosition = [
		'configuration_page' => false,
		'add_to_cart_page' => false,
	];

	public $displayQuantity = true;
	public $configPerQuantity = true;
	public $singularity = false;

	// Setting up the template structure
	public $view = [
		'default' => [

			'label' => 'Product informatie',
			'description' => 'Vult u de benodigde gegevens in',

			'configurations' => [

			],

			'customFields' => [

			],
		],

	];
}
