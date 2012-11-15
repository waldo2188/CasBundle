<?php

namespace Sensio\Bundle\CasBundle\Security;

use Symfony\Component\HttpKernel\Security\Logout\LogoutHandlerInterface;
use Symfony\Component\Security\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\CasBundle\Service\Cas;

class CasLogoutHandler implements LogoutHandlerInterface
{
    protected $cas;

    public function __construct(Cas $cas)
    {
        $this->cas = $cas;
    }

    public function logout(Request $request, Response $response, TokenInterface $token)
    {
        return $this->cas->getLogoutResponse($request);
    }
}