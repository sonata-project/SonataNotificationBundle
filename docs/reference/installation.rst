Installation
============

.. code-block:: bash

    composer require sonata-project/notification-bundle

Or if you wish to use doctrine backend, execute ``composer require sonata-project/notification-orm-pack``.
Symfony Flex will download recipes and install all necessary configuration
files and an entity class for notification messages.

Next, add the dependent bundles:

.. code-block:: bash

    composer require enqueue/amqp-lib --no-update # optional
    composer require liip/monitor-bundle --no-update # optional
    composer require friendsofsymfony/rest-bundle  --no-update # optional when using api with doctrine backend
    composer require nelmio/api-doc-bundle  --no-update # optional when using api with doctrine backend
    composer update

Now, add the new ``SonataNotificationBundle`` Bundle to ``bundles.php`` file::

    // config/bundles.php

    return [
        // ...
        Sonata\NotificationBundle\SonataNotificationBundle::class => ['all' => true],
    ];

Configuration
-------------

SonataNotificationBundle Configuration
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

To use the ``SonataNotificationBundle``, add the following lines to your application configuration
file.

Backend options:

 * ``sonata.notification.backend.runtime`` : direct call, no benefit but useful for testing purpose
 * ``sonata.notification.backend.postpone``: post-pone the messages to be dispatched on kernel.terminate
 * ``sonata.notification.backend.doctrine``: use database to store message, require a background task to be started and supervised, decent starting point for a small amount of async task
 * ``sonata.notification.backend.rabbitmq``: use the RabbitMQ engine to handle messaging, best performance

.. code-block:: yaml

    # config/packages/sonata_notification.yaml

    sonata_notification:
        backend: sonata.notification.backend.runtime

You can disable the admin if you don't need it :

.. code-block:: yaml

    # config/packages/sonata_notification.yaml

    sonata_notification:
        admin:
            enabled: false

Doctrine Configuration
~~~~~~~~~~~~~~~~~~~~~~

Add this bundle in the config mapping definition (or enable `auto_mapping`_):

.. code-block:: yaml

    # config/packages/doctrine.yaml

    doctrine:
        orm:
            entity_managers:
                default:
                    mappings:
                        SonataNotificationBundle: ~

        dbal:
            types:
                json: Sonata\Doctrine\Types\JsonType

.. _`auto_mapping`: http://symfony.com/doc/2.0/reference/configuration/doctrine.html#configuration-overview
