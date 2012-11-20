<?php

namespace Sensio\Bundle\CasBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Sensio\Bundle\CasBundle\Security\CasAuthenticationFactory;

class SensioCasBundle extends Bundle
{

    public function build(ContainerBuilder $container)
      {
          parent::build($container);

          $extension = $container->getExtension('security');
          $extension->addSecurityListenerFactory(new CasAuthenticationFactory());
      }

}
