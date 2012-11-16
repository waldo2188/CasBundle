<?php

namespace Sensio\Bundle\CasBundle\Security;

use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Sensio\Bundle\CasBundle\Service\Cas;

class CasAuthenticationListener implements ListenerInterface
{
    protected $cas;

    public function __construct(SecurityContext $securityContext, AuthenticationManagerInterface $authenticationManager, Cas $cas, LoggerInterface $logger = null)
    {
        $this->securityContext = $securityContext;
        $this->authenticationManager = $authenticationManager;
        $this->cas = $cas;
        $this->logger = $logger;
    }

    public function handle(GetResponseEvent $event)
    {
        if (!$this->cas->isValidationRequest($event->getRequest())) {
            return;
        }

        if (null !== $this->logger) {
            $this->logger->debug(sprintf('Checking secure context token: %s', $this->securityContext->getToken()));
        }

        list($username, $attributes) = $this->getTokenData($event->getRequest());

        if (null !== $token = $this->securityContext->getToken()) {
            if ($token instanceof CasAuthenticationToken && $token->isAuthenticated() && (string) $token === $username) {
                return;
            }
        }
        try {
            $token = $this->authenticationManager->authenticate(new CasAuthenticationToken($username, $attributes));

            if (null !== $this->logger) {
                $this->logger->debug(sprintf('Authentication success: %s', $token));
            }

            $this->securityContext->setToken($token);
        } catch (AuthenticationException $failed) {
            $this->securityContext->setToken(null);

            if (null !== $this->logger) {
                $this->logger->debug(sprintf("Cleared security context due to exception: %s", $failed->getMessage()));
            }
        }
    }

    protected function getTokenData(Request $request)
    {
        $validation = $this->cas->getValidation($request);

        if ($validation->isSuccess()) {
            return array($validation->getUsername(), $validation->getAttributes());
        }

        throw new BadCredentialsException('CAS validation failure : '.$validation->getFailureMessage());
    }
}
