<?php

namespace App\Application\Custom\Cart\PostTypes;

use Validator;
use Illuminate\Http\Request;
use Niku\Cms\Http\NikuPosts;
use Illuminate\Support\Facades\Auth;
use App\Application\Custom\Models\User;
use Niku\Cart\Http\Managers\CreateAccountManager;
use Niku\Cms\Http\Controllers\cmsController;

class CreateAccount extends CreateAccountManager
{	         
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
                        'active' => 1,
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

                            // 'contactgegevens_title' => [
                            //     'label' => 'Contactgegegevens',
                            //     'component' => 'niku-cms-title-customfield',
                            //     'css_class_row_wrapper' => 'col-md-12 col-sm-12',
                            //     'css_class_title_wrapper' => 'col-md-12',
                            //     'css_class_title' => 'h4',
                            //     'saveable' => false,          
                            //     'value' => '',              
                            // ],
            
                            // 'company' => [
                            //     'label' => 'Naam bedrijf',
                            //     'type' => 'text',
                            //     'value' => '',
                            //     'component' => 'niku-cms-text-customfield',
                            //     'css_class_row_wrapper' => 'col-md-12 col-sm-12',
                            //     'css_class_label' => 'col-md-12',
                            //     'css_class_input_wrapper' => 'col-md-12',
                            //     'validation' => 'required',
                            // ],
            
                            // 'aanhef' => [
                            //     'label' => 'Aanhef',
                            //     'type' => 'text',
                            //     'value' => 'dhr.',
                            //     'component' => 'niku-cms-select-customfield',
                            //     'options' => [
                            //         'dhr.' => 'Dhr.',
                            //         'mevr.' => 'Mevr.',
                            //     ],
                            //     'css_class_row_wrapper' => 'col-md-4 col-sm-4',
                            //     'css_class_label' => 'col-md-12',
                            //     'css_class_input_wrapper' => 'col-md-12',
                            //     'validation' => '',
                            // ],
            
                            // 'voornaam' => [
                            //     'label' => 'Voornaam',
                            //     'type' => 'text',
                            //     'value' => '',
                            //     'component' => 'niku-cms-text-customfield',
                            //     'css_class_row_wrapper' => 'col-md-4 col-sm-4',
                            //     'css_class_label' => 'col-md-12',
                            //     'css_class_input_wrapper' => 'col-md-12',
                            //     'validation' => 'required',
                            // ],

                            // 'achternaam' => [
                            //     'label' => 'Achternaam',
                            //     'type' => 'text',
                            //     'value' => '',
                            //     'component' => 'niku-cms-text-customfield',
                            //     'css_class_row_wrapper' => 'col-md-4 col-sm-4',
                            //     'css_class_label' => 'col-md-12',
                            //     'css_class_input_wrapper' => 'col-md-12',
                            //     'validation' => 'required',
                            // ],
            
                            // 'telefoonnummer' => [
                            //     'label' => 'Telefoonnummer',
                            //     'type' => 'text',
                            //     'value' => '',
                            //     'component' => 'niku-cms-text-customfield',
                            //     'css_class_row_wrapper' => 'col-md-6 col-sm-6',
                            //     'css_class_label' => 'col-md-12',
                            //     'css_class_input_wrapper' => 'col-md-12',
                            //     'validation' => 'required',
                            // ],

                            'account_title' => [
                                'label' => 'Accountgegevens',
                                'component' => 'niku-cms-title-customfield',
                                'css_class_row_wrapper' => 'col-md-12 col-sm-12',
                                'css_class_title_wrapper' => 'col-md-12',
                                'css_class_title' => 'h4',
                                'value' => '',
                            ],
            
                            'email' => [
                                'label' => 'E-mailadres',
                                'type' => 'text',
                                'value' => '',
                                'component' => 'niku-cms-text-customfield',
                                'css_class_row_wrapper' => 'col-md-6 col-sm-6',
                                'css_class_label' => 'col-md-12',
                                'css_class_input_wrapper' => 'col-md-12',
                                'validation' => 'required|email|unique:users,email',                                
                            ],

                            'password' => [
                                'label' => 'Wachtwoord',
                                'type' => 'text',
                                'value' => '',
                                'component' => 'niku-cms-password-customfield',
                                'css_class_row_wrapper' => 'col-md-6 col-sm-6',
                                'css_class_label' => 'col-md-12',
                                'css_class_input_wrapper' => 'col-md-12',
                                'validation' => 'required',
                            ],
            
                            // 'factuurgegevens_title' => [
                            //     'label' => 'Factuurgegevens',
                            //     'component' => 'niku-cms-title-customfield',
                            //     'css_class_row_wrapper' => 'col-md-12 col-sm-12',
                            //     'css_class_title_wrapper' => 'col-md-12',
                            //     'css_class_title' => 'h4',
                            //     'value' => '',
                            // ],
            
                            // 'adres' => [
                            //     'label' => 'Adres',
                            //     'type' => 'text',
                            //     'value' => '',
                            //     'component' => 'niku-cms-text-customfield',
                            //     'css_class_row_wrapper' => 'col-md-9 col-sm-9',
                            //     'css_class_label' => 'col-md-12',
                            //     'css_class_input_wrapper' => 'col-md-12',
                            //     'validation' => 'required',
                            // ],
            
                            // 'nummer' => [
                            //     'label' => 'Nummer',
                            //     'type' => 'text',
                            //     'value' => '',
                            //     'component' => 'niku-cms-text-customfield',
                            //     'css_class_row_wrapper' => 'col-md-3 col-sm-3',
                            //     'css_class_label' => 'col-md-12',
                            //     'css_class_input_wrapper' => 'col-md-12',
                            //     'validation' => 'required',
                            // ],
            
                            // 'postcode' => [
                            //     'label' => 'Postcode',
                            //     'type' => 'text',
                            //     'value' => '',
                            //     'component' => 'niku-cms-text-customfield',
                            //     'css_class_row_wrapper' => 'col-md-4 col-sm-4',
                            //     'css_class_label' => 'col-md-12',
                            //     'css_class_input_wrapper' => 'col-md-12',
                            //     'validation' => 'required',
                            // ],
            
                            // 'plaats' => [
                            //     'label' => 'Plaats',
                            //     'type' => 'text',
                            //     'value' => '',
                            //     'component' => 'niku-cms-text-customfield',
                            //     'css_class_row_wrapper' => 'col-md-4 col-sm-4',
                            //     'css_class_label' => 'col-md-12',
                            //     'css_class_input_wrapper' => 'col-md-12',
                            //     'validation' => 'required',
                            // ],
            
                            // 'land' => [
                            //     'label' => 'Land',
                            //     'component' => 'niku-cms-select-customfield',
                            //     'css_class_row_wrapper' => 'col-md-4 col-sm-4',
                            //     'css_class_label' => 'col-md-12',
                            //     'css_class_input_wrapper' => 'col-md-12',
                            //     'options' => [
                            //         'nl' => 'Nederland',
                            //         'be' => 'Belgie',
                            //         'de' => 'Duitsland',
                            //     ],
                            //     'value' => 'nl',
                            //     'validation' => '',
                            // ],
            
                            // 'btw_nummer' => [
                            //     'label' => 'BTW nummer',
                            //     'type' => 'text',
                            //     'value' => '',
                            //     'component' => 'niku-cms-text-customfield',
                            //     'css_class_row_wrapper' => 'col-md-12 col-sm-12',
                            //     'css_class_label' => 'col-md-12',
                            //     'css_class_input_wrapper' => 'col-md-12',
                            //     'validation' => '',
                            // ],             
        
                            'submit' => [
                                'label' => 'Account aanmaken',
                                'component' => 'niku-cart-create-account-submit-customfield',                                                                        
                                'validation' => '',
                                'api_url' => '',
                                'mutator' => 'App\Application\Custom\Cart\Mutators\CreateAccount\CreateAccountUrlMutator',  
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
