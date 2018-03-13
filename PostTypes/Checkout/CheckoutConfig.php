<?php

namespace App\Application\Custom\Cart\Checkout;

use Niku\Cms\Http\NikuPosts;

class CheckoutConfig
{
	// The label of the custom post type
	public $label = 'Afrekenen';

	public $identifier = 'default';

	public $events = [
		'order_create_payment_transaction_succeed' => [
			//
		],
		'order_create_payment_transaction_failure' => [
			// 'App\Application\Custom\Events\Checkout\OrderCreatePaymentTransactionFailureEvent',
		],
		'order_created' => [
			// 'App\Application\Custom\Events\Checkout\OrderCreatedEvent',
		],
		'order_customer_created' => [
			// 'App\Application\Custom\Events\Checkout\OrderCustomerCreatedEvent',
		],
		'order_payment_callback_paid' => [
			'App\Application\Custom\Events\Checkout\OrderCreatePaymentTransactionSucceedEvent',
		],
		'order_payment_callback_cancelled' => [
			'App\Application\Custom\Events\Checkout\OrderCreatePaymentTransactionSucceedEvent',
		],
		'order_payment_callback_expired' => [
			'App\Application\Custom\Events\Checkout\OrderCreatePaymentTransactionSucceedEvent',
		],
		'order_payment_callback_pending' => [
			'App\Application\Custom\Events\Checkout\OrderCreatePaymentTransactionSucceedEvent',
		],

	];

	// Select the custom fields used as post title divided by space
	public $postTitle = ['aanhef', 'contact_person', 'company'];

	// Setting up the template structure
	public $view = [
		'default' => [

			'label' => 'Afrekenen',
			'description' => 'Vult u de benodigde gegevens in',
			'css_class_customfields_wrapper' => 'col-md-9',

			'customFields' => [

				'contactgegevens_title' => [
					'label' => 'Contactgegegevens',
					'component' => 'niku-cms-title-customfield',
					'css_class_row_wrapper' => 'col-md-12 col-sm-12',
					'css_class_title_wrapper' => 'col-md-12',
					'css_class_title' => 'h4',
				],

				'company' => [
					'label' => 'Naam bedrijf',
					'type' => 'text',
					'value' => '',
					'component' => 'niku-cms-text-customfield',
					'css_class_row_wrapper' => 'col-md-12 col-sm-12',
					'css_class_label' => 'col-md-12',
					'css_class_input_wrapper' => 'col-md-12',
					'validation' => 'required',
				],

				'aanhef' => [
					'label' => 'Aanhef',
					'type' => 'text',
					'value' => 'dhr.',
					'component' => 'niku-cms-select-customfield',
					'options' => [
						'dhr.' => 'Dhr.',
						'mevr.' => 'Mevr.',
					],
					'css_class_row_wrapper' => 'col-md-6 col-sm-6',
					'css_class_label' => 'col-md-12',
					'css_class_input_wrapper' => 'col-md-12',
					'validation' => 'required',
				],

				'contact_person' => [
					'label' => 'Contactpersoon',
					'type' => 'text',
					'value' => '',
					'component' => 'niku-cms-text-customfield',
					'css_class_row_wrapper' => 'col-md-6 col-sm-6',
					'css_class_label' => 'col-md-12',
					'css_class_input_wrapper' => 'col-md-12',
					'validation' => 'required',
				],

				'telefoonnummer' => [
					'label' => 'Telefoonnummer',
					'type' => 'text',
					'value' => '',
					'component' => 'niku-cms-text-customfield',
					'css_class_row_wrapper' => 'col-md-6 col-sm-6',
					'css_class_label' => 'col-md-12',
					'css_class_input_wrapper' => 'col-md-12',
					'validation' => 'required',
				],

				'email' => [
					'label' => 'E-mailadres',
					'type' => 'text',
					'value' => '',
					'component' => 'niku-cms-text-customfield',
					'css_class_row_wrapper' => 'col-md-6 col-sm-6',
					'css_class_label' => 'col-md-12',
					'css_class_input_wrapper' => 'col-md-12',
					'validation' => 'required',
				],

				'factuurgegevens_title' => [
					'label' => 'Factuurgegevens',
					'component' => 'niku-cms-title-customfield',
					'css_class_row_wrapper' => 'col-md-12 col-sm-12',
					'css_class_title_wrapper' => 'col-md-12',
					'css_class_title' => 'h4',
				],

				'adres' => [
					'label' => 'Adres',
					'type' => 'text',
					'value' => '',
					'component' => 'niku-cms-text-customfield',
					'css_class_row_wrapper' => 'col-md-9 col-sm-9',
					'css_class_label' => 'col-md-12',
					'css_class_input_wrapper' => 'col-md-12',
					'validation' => 'required',
				],

				'nummer' => [
					'label' => 'Nummer',
					'type' => 'text',
					'value' => '',
					'component' => 'niku-cms-text-customfield',
					'css_class_row_wrapper' => 'col-md-3 col-sm-3',
					'css_class_label' => 'col-md-12',
					'css_class_input_wrapper' => 'col-md-12',
					'validation' => 'required',
				],

				'postcode' => [
					'label' => 'Postcode',
					'type' => 'text',
					'value' => '',
					'component' => 'niku-cms-text-customfield',
					'css_class_row_wrapper' => 'col-md-4 col-sm-4',
					'css_class_label' => 'col-md-12',
					'css_class_input_wrapper' => 'col-md-12',
					'validation' => 'required',
				],

				'plaats' => [
					'label' => 'Plaats',
					'type' => 'text',
					'value' => '',
					'component' => 'niku-cms-text-customfield',
					'css_class_row_wrapper' => 'col-md-4 col-sm-4',
					'css_class_label' => 'col-md-12',
					'css_class_input_wrapper' => 'col-md-12',
					'validation' => 'required',
				],

				'land' => [
					'label' => 'Land',
					'component' => 'niku-cms-select-customfield',
					'css_class_row_wrapper' => 'col-md-4 col-sm-4',
					'css_class_label' => 'col-md-12',
					'css_class_input_wrapper' => 'col-md-12',
					'options' => [
						'nl' => 'Nederland',
						'be' => 'Belgie',
						'de' => 'Duitsland',
					],
					'value' => 'nl',
					'validation' => 'required',
				],

				'btw_nummer' => [
					'label' => 'BTW nummer',
					'type' => 'text',
					'value' => '',
					'component' => 'niku-cms-text-customfield',
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
				],

				'payment_method' => [
					'label' => 'Betaalmethode',
					'component' => 'niku-cms-radio-customfield',
					'css_class_row_wrapper' => 'col-md-12 col-sm-12',
					'css_class_label' => 'col-md-12',
					'css_class_input_wrapper' => 'col-md-12',
					'options' => [
						'ideal' => 'IDEAL',
						'sofort' => 'Sofort',
					],
					'value' => 'ideal',
					'validation' => 'required',
				],
			],
		],

	];
}
