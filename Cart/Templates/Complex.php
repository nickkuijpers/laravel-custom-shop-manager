<?php

namespace App\Application\Custom\Cart\Templates;

use Niku\Cart\Http\Managers\AddToCartManager;

class Complex extends AddToCartManager
{
    public $singularProduct = false;
    public $disableQuantity = true;

	// Setting up the template structure
    public function view()
    {
    	return [
    		'default' => [

                'label' => 'Afrekenen',
                'description' => 'Vult u de benodigde gegevens in',
                'css_class_customfields_wrapper' => 'col-md-9',

                'customFields' => [

                    'first_name' => [
                        'label' => 'Voornaam',
                        'component' => 'niku-cms-text-customfield',
                        'value' => '',
                        'validation' => 'required',
                        'css_class' => 'col-md-4 col-sm-4',
                        'hide_label' => 'true',
                        'validation_location' => [
                            'addtocart' => true,
                            'shoppingcart' => true,
                            'configuration' => true,
                        ],
                    ],
                    'last_name' => [
                        'label' => 'Achternaam',
                        'component' => 'niku-cms-text-customfield',
                        'value' => '',
                        'validation' => 'required',
                        'css_class' => 'col-md-4 col-sm-4',
                        'hide_label' => 'true',
                        'validation_location' => [
                            'addtocart' => false,
                            'shoppingcart' => true,
                            'configuration' => true,
                        ],
                    ],
                    'email' => [
                        'label' => 'E-mailadres',
                        'component' => 'niku-cms-text-customfield',
                        'value' => '',
                        'validation' => 'required|email',
                        'css_class' => 'col-md-4 col-sm-4',
                        'hide_label' => 'true',
                        'validation_location' => [
                            'addtocart' => true,
                            'shoppingcart' => true,
                            'configuration' => true,
                        ],
                    ],

                    'quantity' => [
                        'component' => 'niku-cart-quantity-customfield',
                        'value' => 1,
                        'validation' => 'required|integer',
                        'validation_location' => [
                            'addtocart' => false,
                            'shoppingcart' => true,
                            'configuration' => true,
                        ],
                    ],

                    'submit' => [
                        'label' => 'TOEVOEGEN',
                        'component' => 'niku-cart-addtocart-customfield',
                        'value' => '',
                        'validation' => '',
                        'css_class' => 'col-md-4 col-sm-4',
                        'hide_label' => 'true',
                        'saveable' => false,
                        'validation_location' => [
                            'addtocart' => true,
                            'shoppingcart' => true,
                            'configuration' => true,
                        ],
                    ],

                ],
            ],

    	];
    }
}
