<?php

namespace Sensio\Bundle\CasBundle\Service\Response;

interface ResponseInterface
{
    function isSuccess();
    function getUsername();
    function getAttributes();
}