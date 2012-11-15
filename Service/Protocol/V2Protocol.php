<?php

namespace Sensio\Bundle\CasBundle\Service\Protocol;

use Sensio\Bundle\CasBundle\Service\Protocol\Protocol;
use Sensio\Bundle\CasBundle\Service\Protocol\ProtocolInterface;

class V2Protocol extends Protocol implements ProtocolInterface
{
    public function getValidationUri($service, $ticket)
    {
        return $this->buildUri('serviceValidate', array(
            'service' => $this->cleanUri($service),
            'ticket' => $ticket,
        ));
    }
}