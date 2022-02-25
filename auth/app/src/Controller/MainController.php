<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class MainController extends AbstractController
{
    private \App\Repository\UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }
    /**
     * @Route(".well-known/jwks.json", name="jwks")
     */
    public function jwks()
    {
        return $this->json([]);
    }

    /**
     * @Route("errorn", name="error")
     */
    public function errors(): \Symfony\Component\HttpFoundation\JsonResponse
    {
        throw new BadRequestHttpException("Test exception");
        return $this->json([]);
    }

    /**
     * @Route("/traefik")
     * @Route("/auth/traefik")
     */
    public function main(Request $request): Response
    {
        if ($user = $this->user($request)){
            $response = new Response();
            $response->headers->add([
                "x-auth-token" => $user->getAuthToken(),
                "x-username" => $user->getUsername(),
            ]);
            return $response;
        }

        $redirect_uri = $request->headers->get('x-forwarded-uri');
        $host = $request->headers->get('x-forwarded-host') ?? $request->headers->get('host');
        $url = $request->getScheme()."://$host/auth/login?ru=$redirect_uri";
        return new RedirectResponse($url, Response::HTTP_FOUND);
    }

    private function user(Request $request) :?User
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        if ($user){
            return $user;
        }
        $token = $request->headers->get('x-auth-token');
        if (!$token) {
            return null;
        }
        return $this->userRepository->findOneBy([
            'authToken' => $token
        ]);
    }

}
