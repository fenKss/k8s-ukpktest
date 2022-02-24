<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;

class UserService
{
    /**
     * @var \App\Repository\UserRepository
     */
    private UserRepository $userRepository;
    /**
     * @var \Doctrine\Persistence\ManagerRegistry
     */
    private ManagerRegistry $doctrine;

    public function __construct(
        UserRepository $userRepository,
        ManagerRegistry $doctrine
    ) {
        $this->userRepository = $userRepository;
        $this->doctrine       = $doctrine;
    }

    public function getUser(string $authToken, ?string $username = null): User
    {
        return $this->userRepository->findOneBy([
                'authToken' => $authToken,
            ]) ?? $this->createUser($authToken, $username);

    }

    private function createUser(string $token, ?string $username = null): User
    {
        $user = (new User())->setAuthToken($token)
                            ->setUsername($username ?? $token)
                            ->setPassword($token);
        $em   = $this->doctrine->getManager();
        $em->persist($user);
        $em->flush();
        return $user;
    }
}