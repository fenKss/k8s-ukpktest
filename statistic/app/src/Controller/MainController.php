<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\KafkaService;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class MainController extends AbstractController
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }
    /**
     * @Route("/admin/tour/{id}/results", name="jwks")
     */
    public function jwks(int $id, Request $request, KafkaService $kafkaService)
    {
        $kafkaService->listen();
    }

    /**
     * @Route("errorn", name="error")
     */
    public function errors(): \Symfony\Component\HttpFoundation\JsonResponse
    {
        throw new BadRequestHttpException("Test exception");
        return $this->json([]);
    }

}
