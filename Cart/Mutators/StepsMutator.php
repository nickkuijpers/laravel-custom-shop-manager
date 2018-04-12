<?php

namespace App\Application\Custom\Cart\Mutators;

use Niku\Cms\Http\NikuPosts;
use Niku\Cms\Http\NikuTaxonomies;
use Mollie\Laravel\Facades\Mollie;
use Niku\Cart\Http\Controllers\CartMutatorController;

class StepsMutator extends CartMutatorController
{	  	    
    public function out($customField, $collection, $key, $postTypeModel, $holdValue, $request)    
    {             
        
        $steps = collect([
            'gegevens' => [
                'active' => true,
                'title' => 'Gegevens',
                'linkable' => true,
                'link_to' => [
                    'name' => 'create-account',
                ],
                'index' => 1,
            ],
            'configure' => [
                'active' => true,
                'title' => 'Configureren',
                'linkable' => true,
                'link_to' => [
                    'name' => 'configure',
                ],
                'index' => 2,
            ],
            'betaalwijze' => [
                'active' => true,
                'title' => 'Betaalwijze',
                'linkable' => true,
                'link_to' => [
                    'name' => 'payment-method',
                ],
                'index' => 3,
            ],
            'checkout' => [
                'active' => true,
                'title' => 'Controleren',
                'linkable' => true,
                'link_to' => [
                    'name' => 'checkout',
                ],
                'index' => 4,
            ],
            'betalen' => [
                'active' => false,
                'title' => 'Betalen',
                'linkable' => false,                
                'index' => 5,
            ],
            'opleveren' => [
                'active' => false,
                'title' => 'Opleveren',
                'linkable' => true,
                'link_to' => [
                    'name' => 'thankyou',
                ],
                'index' => 6,
            ],
        ]);

        $activeItemsCount = $customField['active'];

        // Lets mutate the steps
        $steps = $steps->map(function($item, $key) use ($activeItemsCount) {                        
            if($activeItemsCount < $item['index']){
                $item['active'] = false;
                $item['linkable'] = false;
            } else {
                $item['active'] = true;
                $item['linkable'] = true;
            }            
            return $item;
        });

        // If we are at the thank you page, we need to disable all previous links
        if($activeItemsCount === 6){
            $steps = $steps->map(function($item, $key) use ($activeItemsCount) {
                $item['linkable'] = false;
                return $item;
            });
        }
        
        // Return the fields
        $customField['steps'] = $steps;
        return $customField;   
    }
}
