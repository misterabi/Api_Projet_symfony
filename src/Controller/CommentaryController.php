<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Commentary;
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

class CommentaryController extends AbstractController
{
    #[Route('/comment/{article_id}', name: 'app_commentary', methods: ['POST'])]
    public function add(
        Request $r,
        EntityManagerInterface $em,
        ValidatorToken $vt,
        ValidatorRole $vr,
        Validator $v
        ): Response
    {
        $lst_token_valid = $vt->isValid($r);
        if(!$lst_token_valid[0]){
            return new JsonResponse('Token invalide', 401);
        }
        if($vr->isValidRole("ROLE_USER",$lst_token_valid[1])){
            $commentary = new Commentary();
            $commentary->setCommentary($r->get('commentary'))
                ->setDateOfPublish(new \DateTime())
                ->setArticle($em->getRepository(Article::class)->findOneBy(['id' => $r->get('article_id')]))
                ->setAuthor($em->getRepository(User::class)->findOneBy(['id' => $lst_token_valid[1]->id]))
                ;
            $valid = $v->isValid($commentary);
            if($valid == true){
                $em->persist($commentary);
                $em->flush();
                return new JsonResponse('Commentaire ajouté', 201);
            }
            else{
                return new JsonResponse($valid, 400);
            }
        }
        else{
            return new JsonResponse('Vous n\'avez pas les droits', 403);
        }
    }

    #[Route('/comment/{id}', name: 'app_commentary_delete', methods: ['PATCH'])]
    public function modoration(
        Request $r,
        Commentary $commentary,
        EntityManagerInterface $em,
        ValidatorToken $vt,
        ValidatorRole $vr,
        Validator $v
    )
    {
        $lst_token_valid = $vt->isValid($r);
        if(!$lst_token_valid[0]){
            return new JsonResponse('Token invalide', 401);
        }
        if($vr->isValidRole("ROLE_ADMIN",$lst_token_valid[1])){
            if($r->get('state') !== null ){
                $commentary->setState($r->get('state'));
                $valid = $v->isValid($commentary);
                if($valid === true){
                    $em->persist($commentary);
                    $em->flush();
                    return new JsonResponse('Commentaire statuts : '.$r->get('state'), 201);
                }
                else{
                    return new JsonResponse($valid, 400);
                }
            }else{
                return new JsonResponse('Veuillez renseigner le statut', 400);
            }
        }
        else{
            return new JsonResponse('Vous n\'avez pas les droits', 403);
        }

    }
}
