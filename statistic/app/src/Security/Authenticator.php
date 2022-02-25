<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class Authenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    private UrlGeneratorInterface $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function authenticate(Request $request): Passport
    {
        $username = $request->request->get('email', '');
        $request->getSession()->set(Security::LAST_USERNAME, $username);

        return new Passport(new UserBadge($username),
            new PasswordCredentials($request->request->get('password', '')), []
        );
    }

    public function onAuthenticationSuccess(
        Request $request,
        TokenInterface $token,
        string $firewallName
    ): ?Response {
        /** @var \App\Entity\User $user */
        $user     = $token->getUser();
        $response = (new RedirectResponse($request->query->get('ru', '/')));
        $response->headers->add([
            "x-auth-token" => $user->getAuthToken(),
            "x-username" => $user->getUserIdentifier(),
        ]);
        $response->headers->setCookie(Cookie::create('xau', $user->getAuthToken()));
        return $response;
    }

    public function onAuthenticationFailure(
        Request $request,
        AuthenticationException $exception
    ): Response {
        if ($request->hasSession()) {
            $request->getSession()->set(Security::AUTHENTICATION_ERROR, $exception);
        }
        $redirect_uri = $request->query->get('ru');
        $host         = $request->headers->get('x-forwarded-host');
        $url          = $request->getScheme()."://$host/auth/login?ru=$redirect_uri";

        return new RedirectResponse($url);
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
