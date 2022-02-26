<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/api/user", name="user_api")
 */
class ApiController extends AbstractController
{
    /**
     * @Route("", name="index")
     */
    public function index(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user){
            return new Response('Unathorized', Response::HTTP_UNAUTHORIZED);
        }
        return $this->json($user);
    }

}