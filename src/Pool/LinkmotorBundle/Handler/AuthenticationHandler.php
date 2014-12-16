<?php

namespace Pool\LinkmotorBundle\Handler;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Pool\LinkmotorBundle\Service\Limits;

class AuthenticationHandler extends ContainerAware implements AuthenticationSuccessHandlerInterface
{
    private $router;

    /**
     * @var Limits
     */
    private $limits;

    public function __construct($router, Limits $limits)
    {
        $this->router = $router;
        $this->limits = $limits;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        if (!$this->limits->userMayLogIn($token->getUser())) {
            return new RedirectResponse($this->router->generate('pool_linkmotor_account_limitedq>q'));
        }

        $session = $request->getSession();
        $url = $this->router->generate('pool_linkmotor_no_language_index');
        if ($session->has('_security.secured_area.target_path')) {
            $url = $session->get('_security.secured_area.target_path');
        }

        return new RedirectResponse($url);
    }
}
