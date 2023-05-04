<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\User;
use App\Service\Validator;
use App\Service\ValidatorRole;
use App\Service\ValidatorToken;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArticleController extends AbstractController
{
    #[Route('/article/{id}', name: 'app_article', methods: ['GET'])]
    public function index($id,EntityManagerInterface $em): Response
    {
        $article = $em->getRepository(Article::class)->findOneById($id);

        if($article == null){
            return new JsonResponse('Article introuvable', 404);
        }
        return new JsonResponse($article);
    }

    #[Route('/article', name: 'app_articles', methods: ['POST'])]
    public function add(
        Request $r,
        EntityManagerInterface $em,
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
            $article = new Article();
            $article->setTitle($r->get('title'))
                ->setContent($r->get('content'))
                ->setDateOfCreation(new \DateTime())
                ->setCategory($em->getRepository(Category::class)->findOneBy(['id' => $r->get('category')]))
                ->setAuthor($em->getRepository(User::class)->findOneBy(['id' => $lst_token_valid[1]->getId()]));
            if($r->get('state') != null){
                $article->setState($r->get('state'));
                $article->setDateOfParution(new \DateTime());
            }
            $valid = $v->isValid($article);
            if($valid === true){
                $em->persist($article);
                $em->flush();
                return new JsonResponse('Article crée', 201);
            }
            else{
                return new JsonResponse($valid, 400);
            }
        }
        else{
            return new JsonResponse('Vous n\'avez pas les droits', 403);
        }       
    }

    #[Route('/article/{id}', name: 'app_article_update', methods: ['PATCH'])]
    public function update(
        Request $r, 
        EntityManagerInterface $em, 
        Article $article = null, 
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
            $update = false;
            if($article == null){
                return new JsonResponse('Article introuvable', 404);
            }
            if($r->get('title') != null){
                $article->setTitle($r->get('title'));
                $update = true;
            }
            if($r->get('content') != null){
                $article->setContent($r->get('content'));
                $update = true;
            }
            if($r->get('category') != null){
                $article->setCategory($em->getRepository(Category::class)->findOneBy(['id' => $r->get('category')]));
                $update = true;
            }
            if($r->get('author') != null){
                $article->setAuthor($em->getRepository(User::class)->findOneBy(['id' => $lst_token_valid[1]->getId()]));
                $update = true;
            }
            if($r->get('state') != null){
                $article->setState($r->get('state'));
                $update = true;
            }
            $valid = $v->isValid($article);
            if($valid === true && $update === true){
                $em->persist($article);
                $em->flush();
                return new JsonResponse('Article Modifier', 201);
            }else if($update == false){
                return new JsonResponse('Veuillez remplir au moins un champ', 400);
            }
            else{
                return new JsonResponse($valid, 400);
            }
            
        }
        return new JsonResponse('Vous n\'avez pas les droits', 403);    
    }

    #[Route('/article/{id}', name: 'app_article_delete', methods: ['DELETE'])]
    public function delete(
        Request $r, 
        EntityManagerInterface $em, 
        Article $article = null, 
        ValidatorToken $vt,
        ValidatorRole $vr,
    ):Response
    {
        $lst_token_valid = $vt->isValid($r);
        if(!$lst_token_valid[0]){
            return new JsonResponse('Token invalide', 401);
        }
        if($vr->isValidRole("ROLE_ADMIN",$lst_token_valid[1])){
            $em->remove($article);
            $em->flush();
            return new JsonResponse('Article supprimé', 200);
        }
        return new JsonResponse('Vous n\'avez pas les droits', 403);

    }
}
