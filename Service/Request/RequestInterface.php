<?php

namespace Sensio\Bundle\CasBundle\Service\Request;

use Sensio\Bundle\CasBundle\Service\Response\ResponseInterface;

interface RequestInterface
{
    function __construct($uri);
    function setHeaders(array $headers = array());
    function setCookies(array $cookies = array());
    function setCertFile($file = null);
    function send(ResponseInterface $response);
    function getResponse();
}