<?php

namespace App\Application\Custom\Cart\PostTypes;

use Validator;
use Illuminate\Http\Request;
use Niku\Cart\Http\Managers\CheckoutManager;

class Checkout extends CheckoutManager
{
    public $authenticationRequired = true;

	// Setting up the template structure
    public function view()
    {
    	return [
    		'default' => [

                'label' => 'Afrekenen',
                'description' => 'Vult u de benodigde gegevens in',
                'css_class_customfields_wrapper' => 'col-md-9',

                'customFields' => [

                    'steps' => [
                        'component' => 'niku-cart-steps-customfield',
                        'saveable' => false,
                        'value' => 'Afrekenen',
                        'mutator' => 'App\Application\Custom\Cart\Mutators\StepsMutator',
                        'active' => 4,
                    ],

                    'title' => [
                        'component' => 'niku-cart-title-customfield',
                        'saveable' => false,
                        'value' => 'Afrekenen',
                    ],

                    'form_wrapper' => [
                        'component' => 'niku-cart-form-wrapper-customfield',
                        'css_class_row_wrapper' => 'row',
                        'saveable' => false,
                        'value' => '',
                        'customFields' => [

                            'contactgegevens_title' => [
                                'label' => 'Contactgegegevens',
                                'component' => 'niku-cms-title-customfield',
                                'css_class_row_wrapper' => 'col-md-12 col-sm-12',
                                'css_class_title_wrapper' => 'col-md-12',
                                'css_class_title' => 'h4',
                                'saveable' => false,
                                'value' => '',
                            ],

                            'company' => [
                                'label' => 'Naam bedrijf',
                                'type' => 'text',
                                'value' => '',
                                'component' => 'niku-cms-readonly-customfield',
                                'css_class_row_wrapper' => 'col-md-12 col-sm-12',
                                'css_class_label' => 'col-md-12',
                                'css_class_input_wrapper' => 'col-md-12',
                                'validation' => '',
                            ],

                            'aanhef' => [
                                'label' => 'Aanhef',
                                'type' => 'text',
                                'value' => 'dhr.',
                                'component' => 'niku-cms-readonly-customfield',
                                'options' => [
                                    'dhr.' => 'Dhr.',
                                    'mevr.' => 'Mevr.',
                                ],
                                'css_class_row_wrapper' => 'col-md-4 col-sm-4',
                                'css_class_label' => 'col-md-12',
                                'css_class_input_wrapper' => 'col-md-12',
                                'validation' => '',
                            ],

                            'voornaam' => [
                                'label' => 'Voornaam',
                                'type' => 'text',
                                'value' => '',
                                'component' => 'niku-cms-readonly-customfield',
                                'css_class_row_wrapper' => 'col-md-4 col-sm-4',
                                'css_class_label' => 'col-md-12',
                                'css_class_input_wrapper' => 'col-md-12',
                                'validation' => '',
                            ],

                            'achternaam' => [
                                'label' => 'Achternaam',
                                'type' => 'text',
                                'value' => '',
                                'component' => 'niku-cms-readonly-customfield',
                                'css_class_row_wrapper' => 'col-md-4 col-sm-4',
                                'css_class_label' => 'col-md-12',
                                'css_class_input_wrapper' => 'col-md-12',
                                'validation' => '',
                            ],

                            'telefoonnummer' => [
                                'label' => 'Telefoonnummer',
                                'type' => 'text',
                                'value' => '',
                                'component' => 'niku-cms-readonly-customfield',
                                'css_class_row_wrapper' => 'col-md-6 col-sm-6',
                                'css_class_label' => 'col-md-12',
                                'css_class_input_wrapper' => 'col-md-12',
                                'validation' => '',
                            ],

                            'email' => [
                                'label' => 'E-mailadres',
                                'type' => 'text',
                                'value' => '',
                                'component' => 'niku-cms-readonly-customfield',
                                'css_class_row_wrapper' => 'col-md-6 col-sm-6',
                                'css_class_label' => 'col-md-12',
                                'css_class_input_wrapper' => 'col-md-12',
                                'validation' => '',
                            ],

                            'factuurgegevens_title' => [
                                'label' => 'Factuurgegevens',
                                'component' => 'niku-cms-title-customfield',
                                'css_class_row_wrapper' => 'col-md-12 col-sm-12',
                                'css_class_title_wrapper' => 'col-md-12',
                                'css_class_title' => 'h4',
                                'value' => '',
                            ],

                            'adres' => [
                                'label' => 'Adres',
                                'type' => 'text',
                                'value' => '',
                                'component' => 'niku-cms-readonly-customfield',
                                'css_class_row_wrapper' => 'col-md-9 col-sm-9',
                                'css_class_label' => 'col-md-12',
                                'css_class_input_wrapper' => 'col-md-12',
                                'validation' => '',
                            ],

                            'nummer' => [
                                'label' => 'Nummer',
                                'type' => 'text',
                                'value' => '',
                                'component' => 'niku-cms-readonly-customfield',
                                'css_class_row_wrapper' => 'col-md-3 col-sm-3',
                                'css_class_label' => 'col-md-12',
                                'css_class_input_wrapper' => 'col-md-12',
                                'validation' => '',
                            ],

                            'postcode' => [
                                'label' => 'Postcode',
                                'type' => 'text',
                                'value' => '',
                                'component' => 'niku-cms-readonly-customfield',
                                'css_class_row_wrapper' => 'col-md-4 col-sm-4',
                                'css_class_label' => 'col-md-12',
                                'css_class_input_wrapper' => 'col-md-12',
                                'validation' => '',
                            ],

                            'plaats' => [
                                'label' => 'Plaats',
                                'type' => 'text',
                                'value' => '',
                                'component' => 'niku-cms-readonly-customfield',
                                'css_class_row_wrapper' => 'col-md-4 col-sm-4',
                                'css_class_label' => 'col-md-12',
                                'css_class_input_wrapper' => 'col-md-12',
                                'validation' => '',
                            ],

                            'land' => [
                                'label' => 'Land',
                                'component' => 'niku-cms-readonly-customfield',
                                'css_class_row_wrapper' => 'col-md-4 col-sm-4',
                                'css_class_label' => 'col-md-12',
                                'css_class_input_wrapper' => 'col-md-12',
                                'options' => [
                                    'nl' => 'Nederland',
                                    'be' => 'Belgie',
                                    'de' => 'Duitsland',
                                ],
                                'value' => 'nl',
                                'validation' => '',
                            ],

                            'btw_nummer' => [
                                'label' => 'BTW nummer',
                                'type' => 'text',
                                'value' => '',
                                'component' => 'niku-cms-readonly-customfield',
                                'css_class_row_wrapper' => 'col-md-12 col-sm-12',
                                'css_class_label' => 'col-md-12',
                                'css_class_input_wrapper' => 'col-md-12',
                                'validation' => '',
                            ],

                            'betaalmethode_title' => [
                                'label' => 'Betaalmethode',
                                'component' => 'niku-cms-title-customfield',
                                'css_class_row_wrapper' => 'col-md-12 col-sm-12',
                                'css_class_title_wrapper' => 'col-md-12',
                                'css_class_title' => 'h4',
                                'value' => '',
                            ],

                            // 'payment_method' => [
                            //     'label' => 'Betaalmethode',
                            //     'component' => 'niku-cart-payment-methods-customfield',
                            //     'css_class_row_wrapper' => 'col-md-12 col-sm-12',
                            //     'css_class_label' => 'col-md-12',
                            //     'css_class_input_wrapper' => 'col-md-12',
                            //     'mutator' => 'App\Application\Custom\Cart\Mutators\PaymentMethodsMutator',
                            //     'value' => '',
                            //     'validation' => 'required',
                            // ],

                            'shoppingcart' => [
                                'validation' => '',
                                'saveable' => false,
                                'component' => 'niku-cart-shoppingcart-customfield',
                                'saveable' => false,
                                'main_table_wrapper' => 'margintopmedium',
                                'cart_items_update_api_url' => '',
                                'cart_items_delete_api_url' => '',
                                'mutator' => 'App\Application\Custom\Cart\Mutators\Checkout\ShoppingcartMutator',
                                'to_checkout_button' => false,
                                'value' => '',
                            ],

                            'betalen' => [
                                'label' => 'Betalen',
                                'component' => 'niku-cart-checkout-submit-customfield',
                                'validation' => '',
                                'api_url' => '',
                                'mutator' => 'App\Application\Custom\Cart\Mutators\Checkout\CheckoutUrlApiMutator',
                                'saveable' => false,
                                'value' => '',
                            ],

                        ]
                    ],

                ],
            ],

    	];
    }
}
