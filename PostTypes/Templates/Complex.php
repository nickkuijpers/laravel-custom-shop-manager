<?php

namespace App\Application\Custom\Cart\Templates;

use App\Application\Custom\Cart\CartConfig;
use Niku\Cms\Http\NikuPosts;

class Complex extends CartConfig
{
	// The label of the custom post type
	public $label = 'Licenties';

	public $identifier = 'licentie';

	public $configPosition = [
		'configuration_page' => true,
		'add_to_cart_page' => false,
	];

	public $displayQuantity = true;
	public $configPerQuantity = true;
	public $singularity = false;

	// Setting up the template structure
	public $view = [
		'default' => [

			'label' => 'Licentie informatie',
			'description' => 'Vult u de benodigde gegevens in',

			'customFields' => [
				'first_name' => [
					'label' => 'Voornaam',
					'component' => 'niku-cms-text-customfield',
					'value' => '',
					'validation' => 'required',
					'css_class' => 'col-md-4 col-sm-4',
					'hide_label' => 'true',
				],
				'last_name' => [
					'label' => 'Achternaam',
					'component' => 'niku-cms-text-customfield',
					'value' => '',
					'validation' => 'required',
					'css_class' => 'col-md-4 col-sm-4',
					'hide_label' => 'true',
				],
				'email' => [
					'label' => 'E-mailadres',
					'component' => 'niku-cms-text-customfield',
					'value' => '',
					'validation' => 'required|email',
					'css_class' => 'col-md-4 col-sm-4',
					'hide_label' => 'true',
				],

			],
		],

	];
}
