<?php

namespace Sensio\Bundle\CasBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class CasAuthenticationToken extends AbstractToken
{

    protected $casAttributes;

    public function __construct($user, array $attributes = null, array $roles = array())
    {
        parent::__construct($roles);

        $this->setUser($user);
        $this->casAttributes = $attributes;

        parent::setAuthenticated(true);
    }

    public function getCredentials()
    {
        return '';
    }

    public function getCasAttributes()
    {
        return $this->casAttributes;
    }

}