Add CAS authentication to Symfony2
==================================

-  More informations about CAS_ (Central Authentication Service).
-  Unlike SimpleCasBundle_, it's based on the Symfony2 security component.
-  Proxy features are not yet available.


Install the Bundle
------------------

1. Add the sources in your composer.json

    .. code-block:: text

         "require": {
            // ...
            "sensio/cas-bundle": "master"
        },
        "repositories": [
            {
                "type": "vcs",
                "url": "git://github.com/waldo2188/CasBundle.git"
            }
        ]

2. Then add it to your AppKernel class::

        // in AppKernel::registerBundles()
        $bundles = array(
            // ...
            new Sensio\Bundle\CasBundle\SensioCasBundle(),
            // ...
        );

Configuration
-------------

config.yml

    .. code-block:: yaml

        cas.config:
            uri:     https://my.cas.server:443/ # URI of the cas server
            version: 2                          # version of the used CAS protocol
            cert:    /path/to/my/cert.pem       # ssl cert file path (if needed)
            request: curl                       # request adapter (curl, http or file)


security.yml

    .. code-block:: yaml

        security:
            providers:
                mon_beau_provider:
                    id: security_user_provider_entity # id of the service who provide entity

        firewalls:
            my_firewall:
                pattern:  ^/
                cas: true

services.xml
    .. code-block:: xml

        <service id="security_user_provider_entity" class="%security.user.provider.entity.class%" public="false">
            <argument /> <!-- The class of the service must implement \Symfony\Component\Security\Core\User\UserProviderInterface -->
        </service>


.. _CAS:             http://www.jasig.org/cas
.. _SimpleCasBundle: https://github.com/jmikola/SimpleCASBundle
