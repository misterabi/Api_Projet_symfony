<?php

namespace App\Service;

use Symfony\Component\Validator\Validator\ValidatorInterface;

class ValidatorRole{


	public function isValidRole(String $role,$decoded){
		if(in_array($role, $decoded->roles)){
            return true;
        }
        return false;
	}
}