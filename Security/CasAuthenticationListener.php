<?php

namespace Sensio\Bundle\CasBundle\Security;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationFailureHandler;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Sensio\Bundle\CasBundle\Service\Cas;

class CasAuthenticationListener implements ListenerInterface
{

    protected $cas;
    protected $securityContext;
    protected $authenticationManager;
    protected $failureHandler;
    protected $authenticationSuccessHandler;
    protected $authenticationFailureHandler;

    public function __construct(SecurityContextInterface $securityContext, $authenticationManager, Cas $cas, LoggerInterface $logger = null, DefaultAuthenticationSuccessHandler $defaultAuthenticationSuccessHandler, DefaultAuthenticationFailureHandler $defaultAuthenticationFailureHandler)
    {

        $this->securityContext = $securityContext;
        $this->authenticationManager = $authenticationManager;
        $this->cas = $cas;
        $this->logger = $logger;
        $this->authenticationSuccessHandler = $defaultAuthenticationSuccessHandler;
        $this->authenticationFailureHandler = $defaultAuthenticationFailureHandler;
    }

    public function handle(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if (!$this->cas->isValidationRequest($request)) {
            return;
        }

        if (null !== $this->logger) {
            $this->logger->debug(sprintf('Checking secure context token: %s', $this->securityContext->getToken()));
        }

        list($username, $attributes) = $this->getTokenData($request);

        if (null !== $token = $this->securityContext->getToken()) {
            if ($token instanceof CasAuthenticationToken && $token->isAuthenticated() && (string) $token === $username) {
                return;
            }
        }

        $token = new CasAuthenticationToken($username, $attributes);

        try {

            $token = $this->authenticationManager->authenticate($token);

            if (null !== $this->logger) {
                $this->logger->debug(sprintf('Authentication success: %s', $token));
            }

            $this->securityContext->setToken($token);
        } catch (AuthenticationException $failed) {
            $this->securityContext->setToken(null);

            if (null !== $this->logger) {
                $this->logger->debug(sprintf("Cleared security context due to exception: %s", $failed->getMessage()));
            }
        } catch (\Exception $e) {

            $response = $this->authenticationFailureHandler->onAuthenticationFailure($request, $e);

            if ($response != null) {
                return $event->setResponse($response);
            }

            throw $e;
        }

        $response = $this->authenticationSuccessHandler->onAuthenticationSuccess($request, $token);

        if ($response != null) {
            return $event->setResponse($response);
        }
    }

    protected function getTokenData(Request $request)
    {
        $validation = $this->cas->getValidation($request);

        if ($validation->isSuccess()) {
            return array($validation->getUsername(), $validation->getAttributes());
        }

        throw new BadCredentialsException('CAS validation failure : ' . $validation->getFailureMessage());
    }

}
