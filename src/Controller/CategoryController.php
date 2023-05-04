<?php

namespace App\Controller;

use App\Entity\Category;
use App\Service\Validator;
use App\Service\ValidatorRole;
use App\Service\ValidatorToken;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends AbstractController
{
    #[Route('/categories', name: 'app_categories', methods: ['GET'])]
    public function index(EntityManagerInterface $em): Response
    {
        $category = $em->getRepository(Category::class)->find3Last();
        return new JsonResponse($category);
    }

    #[Route('/category/{id}', name: 'app_category', methods: ['GET'])]
    public function show(EntityManagerInterface $em, int $id): Response
    {
        $category = $em->getRepository(Category::class)->findOneById($id);

        if($category == null){
            return new JsonResponse('Catégorie introuvable', 404);
        }
        return new JsonResponse($category, 200);

    }

    #[Route('/category', name: 'app_category_admin', methods: ['POST'])]
    public function add(
        Request $r,
        EntityManagerInterface $em,
        ValidatorToken $vt,
        ValidatorRole $vr,
        Validator $v):Response
    {
        $lst_token_valid = $vt->isValid($r);
        if(!$lst_token_valid[0]){
            return new JsonResponse('Token invalide', 401);
        }
        if($vr->isValidRole("ROLE_ADMIN",$lst_token_valid[1])){
            $category = new Category();
            $category->setTitle($r->get('title'));
            $valid = $v->isValid($category);
            if($valid === true){
                $em->persist($category);
                $em->flush();
                return new JsonResponse('Categorie crée', 201);
            }
            else{
                return new JsonResponse($valid, 400);
            }
        }
        else{
            return new JsonResponse('Vous n\'avez pas les droits', 403);
        }
        
    }

    #[Route('/category/{id}', name: 'app_category_admin_patch', methods: ['PATCH'])]
    public function patch(
        Request $r, 
        EntityManagerInterface $em, 
        Category $category = null, 
        ValidatorToken $vt,
        ValidatorRole $vr,
        Validator $v

    ):Response
    {
        $lst_token_valid = $vt->isValid($r);
        if(!$lst_token_valid[0]){
            return new JsonResponse('Token invalide', 401);
        }
        if($vr->isValidRole("ROLE_ADMIN",$lst_token_valid[1])){

            if($category == null){
                return new JsonResponse('Categorie introuvable', 404);
            }
            
            $category->setTitle($r->get('title'));

            $valid = $v->isValid($category);
            if($valid === true){
                $em->persist($category);
                $em->flush();
                return new JsonResponse('Categorie Modifier', 201);
            }
            else{
                return new JsonResponse($valid, 400);
            }
            return new JsonResponse('Veuillez remplir le champ title', 400);

        }
        return new JsonResponse('Vous n\'avez pas les droits', 403);        
    }


    #[Route('/category/{id}', name: 'app_category_admin_delete', methods: ['DELETE'])]
    public function delete(
        Request $r, 
        EntityManagerInterface $em, 
        Category $category = null, 
        ValidatorToken $vt,
        ValidatorRole $vr,
    ):Response
    {
        $lst_token_valid = $vt->isValid($r);
        if(!$lst_token_valid[0]){
            return new JsonResponse('Token invalide', 401);
        }
        if($vr->isValidRole("ROLE_ADMIN",$lst_token_valid[1])){
            if($category == null){
                return new JsonResponse('Catégorie introuvable', 404);
            }   
            $em->remove($category);
            $em->flush();
            return new JsonResponse('Category supprimé', 200);
        }
        return new JsonResponse('Vous n\'avez pas les droits', 403);

    }
}
