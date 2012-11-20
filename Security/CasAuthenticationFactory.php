<?php

namespace Sensio\Bundle\CasBundle\Security;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;

class CasAuthenticationFactory implements SecurityFactoryInterface
{
    public function create(ContainerBuilder $container, $id, $config, $userProvider, $defaultEntryPoint)
    {
        $providerId = 'security.authentication.provider.cas.'.$id;
//        $container
//            ->register($providerId, '%security.authentication.provider.cas.class%')
//            ->setArguments(array(new Reference($userProvider), new Reference('security.account_checker')))
//        ;
        $container
            ->setDefinition($providerId, new DefinitionDecorator('cas.security.authentication.provider'))
            ->replaceArgument(0, new Reference($userProvider))
        ;

//        $listener = new Definition(
//            '%security.authentication.listener.cas.class%',
//            array(
//                new Reference('security.context'),
//                new Reference('security.authentication.manager'),
//                new Reference('sensio_cas'),
//                new Reference('logger', ContainerBuilder::IGNORE_ON_INVALID_REFERENCE),
//            )
//        );

        $listenerId = 'security.authentication.listener.cas.'.$id;
//        $container->setDefinition('', $listener);
//        $container->setAlias($listenerId, 'security.authentication.listener.cas');

        $listener = $container->setDefinition($listenerId, new DefinitionDecorator('cas.security.authentication.listener'));

//        return array($providerId, $listenerId, 'security.authentication.cas_entry_point');
//        return array($providerId, $listenerId, $defaultEntryPoint);
        return array($providerId, $listenerId, 'security.authentication.cas_entry_point');
    }

    public function getPosition()
    {
        return 'pre_auth';
    }

    public function getKey()
    {
        return 'cas';
    }

    public function addConfiguration(NodeDefinition $node)
    {
        $node
            ->children()
                ->scalarNode('provider')->end()
            ->end()
        ;
    }
}
