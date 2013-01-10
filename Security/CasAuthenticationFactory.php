<?php

namespace Sensio\Bundle\CasBundle\Security;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class CasAuthenticationFactory implements SecurityFactoryInterface
{

    protected $defaultSuccessHandlerOptions = array(
        'always_use_default_target_path' => false,
        'default_target_path' => "/",
    );
    protected $defaultFailureHandlerOptions = array(
        'failure_path' => null,
    );

    public function create(ContainerBuilder $container, $id, $config, $userProviderId, $defaultEntryPoint)
    {
        $defaultEntryPoint = 'security.authentication.cas_entry_point';


        // authentication provider
        $authProviderId = $this->createAuthProvider($container, $id, $config, $userProviderId);

        // authentication listener
        $listenerId = $this->createListener($container, $id, $config, $userProviderId);

        return array($authProviderId, $listenerId, $defaultEntryPoint);
    }

    protected function createAuthProvider(ContainerBuilder $container, $id, $config, $userProviderId)
    {
        $provider = 'security.authentication.provider.cas.' . $id;
        $container
                ->setDefinition($provider, new DefinitionDecorator('security.authentication.provider.cas'))
                ->replaceArgument(0, new Reference($userProviderId))
        ;

        return $provider;
    }

    protected function createListener($container, $id, $config, $userProvider)
    {
        $listenerId = $this->getListenerId();
        $listener = new DefinitionDecorator($listenerId);

        // replace argument in the DI for the service security.authentication.listener.cas in cas.xml
        $listener->replaceArgument(4, new Reference($this->createAuthenticationSuccessHandler($container, $id, $config)));
        $listener->replaceArgument(5, new Reference($this->createAuthenticationFailureHandler($container, $id, $config)));


        $listenerId .= '.' . $id;
        $container->setDefinition($listenerId, $listener);

        return $listenerId;
    }

    protected function getListenerId()
    {
        return 'security.authentication.listener.cas';
    }

    protected function createAuthenticationSuccessHandler($container, $id, $config)
    {
        if (isset($config['success_handler'])) {
            return $config['success_handler'];
        }

        $successHandlerId = 'security.authentication.success_handler.' . $id . '.' . str_replace('-', '_', $this->getKey());

        $successHandler = $container->setDefinition($successHandlerId, new DefinitionDecorator('security.authentication.success_handler'));
        $successHandler->replaceArgument(1, array_intersect_key($config, $this->defaultSuccessHandlerOptions));
        $successHandler->addMethodCall('setProviderKey', array($id));

        return $successHandlerId;
    }

    protected function createAuthenticationFailureHandler($container, $id, $config)
    {
        if (isset($config['failure_handler'])) {
            return $config['failure_handler'];
        }

        $id = 'security.authentication.failure_handler.' . $id . '.' . str_replace('-', '_', $this->getKey());

        $failureHandler = $container->setDefinition($id, new DefinitionDecorator('security.authentication.failure_handler'));
        $failureHandler->replaceArgument(2, array_intersect_key($config, $this->defaultFailureHandlerOptions));

        return $id;
    }

    public function getPosition()
    {
        return 'pre_auth';
    }

    public function getKey()
    {
        /* This key is used in security.yml under
         * firewalls:
         *      main:
         *          pattern: ^/
         *          cas: true
         */
        return 'cas';
    }

    /**
     * Allow to add configuration in the security.yml file
     * 
     * @param \Symfony\Component\Config\Definition\Builder\NodeDefinition $node
     */
    public function addConfiguration(NodeDefinition $node)
    {

        $builder = $node->children();

        $builder
                ->scalarNode('provider')->end()
                ->scalarNode('failure_path')->defaultValue(null)->end()
                ->scalarNode('default_target_path')->defaultValue(null)->end()
                ->scalarNode('always_use_default_target_path')->defaultValue(false)->end()
        ;
    }

}
