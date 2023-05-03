<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class ValidatorToken extends AbstractController{


	public function isValid(Request $r){
        $list = [];
        $headers = $r->headers->all();
        if(isset($headers['token']) && !empty($headers['token'])){
            $jwt = current($headers['token']); //recupere la cellule 0 avec current
            $key = $this->getParameter('jwt_secret'); //recupere la clé secrete
            try{
                $decoded = JWT::decode($jwt, new Key($key, 'HS256')); //decode le token

            }catch(\Exception $e){
                array_push($list, false);
                array_push($list, false);
                return $list;
            }
            array_push($list, true);
            array_push($list, $decoded);
            return $list;
            

        }
        array_push($list, false);
        array_push($list, false);
        return $list;
	}
}