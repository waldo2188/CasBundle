<?php

namespace Sensio\Bundle\CasBundle\Service\Request;

use Sensio\Bundle\CasBundle\Service\Request\RequestInterface;
use Sensio\Bundle\CasBundle\Service\Request\Request;
use Sensio\Bundle\CasBundle\Service\Response\ResponseInterface;

class FileRequest extends Request implements RequestInterface
{
    public function send(ResponseInterface $response)
    {
        $this->response = $response;
        $this->response->setBody(file_get_contents($this->uri));

        return $this;
    }
}