<?php

namespace App\EventSubscriber;

use App\Service\UserService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class TokenSubscriber implements EventSubscriberInterface
{
    private ContainerInterface $container;
    private UserService        $userService;

    public function __construct(UserService $userService, ContainerInterface $container)
    {
        $this->container   = $container;
        $this->userService = $userService;
    }

    public function onKernelController(ControllerEvent $event)
    {
        /** @var \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage $tokenStorage */
        $tokenStorage = $this->container->get("security.token_storage");
        if ($tokenStorage->getToken() && $tokenStorage->getToken()->getUser()) {
            return;
        }
        $headers = $event->getRequest()->headers;
        $token   = $headers->get('x-auth-token');
        if (!$token) {
            return;
        }
        $username = $headers->get('x-username');
        $user     = $this->userService->getUser($token, $username);
        $token    = new UsernamePasswordToken(
            $user,
            $user->getPassword(),
            $user->getRoles()
        );
        $tokenStorage->setToken($token);
        /** @var \Symfony\Component\EventDispatcher\EventDispatcher $eventDispatcher */
        $eventDispatcher = $this->container->get("event_dispatcher");
        $eventDispatcher->dispatch(new InteractiveLoginEvent($event->getRequest(), $token), "security.interactive_login");
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }
}