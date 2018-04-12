<?php

namespace App\Application\Custom\Cart\Mutators;

use Niku\Cms\Http\NikuPosts;
use Niku\Cms\Http\NikuTaxonomies;
use Niku\Cart\Http\Controllers\CartMutatorController;

class ErrorsMutator extends CartMutatorController
{	  	
    public function out($customField, $collection, $key, $postTypeModel, $holdValue, $request)    
    {                      
        $value = json_decode($holdValue, true);        
        if(is_array($value) && count($value) >= 1){
            $customField['showErrors'] = true;
        } else {
            $customField['showErrors'] = false;            
        }

        $customField['value'] = $value;
        return $customField;
    }

}
