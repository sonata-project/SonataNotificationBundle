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

Add these bundles in the config mapping definition (or enable `auto_mapping`_):

.. code-block:: yaml

    # config/packages/doctrine.yaml

    doctrine:
        orm:
            entity_managers:
                default:
                    mappings:
                        ApplicationSonataNotificationBundle: ~
                        SonataNotificationBundle: ~

        dbal:
            types:
                json: Sonata\Doctrine\Types\JsonType

Extending the Bundle
--------------------

.. note::

    You can skip this section if you are using Flex and installed a bundle
    with ``sonata-project/notification-orm-pack``.

At this point, the bundle is functional, but not quite ready yet. You need to
generate the correct entities for the media:

.. code-block:: bash

    bin/console sonata:easy-extends:generate SonataNotificationBundle --dest=src --namespace_prefix=App

With provided parameters, the files are generated in ``src/Application/Sonata/NotificationBundle``.

.. note::

    The command will generate domain objects in ``App\Application`` namespace.
    So you can point entities' associations to a global and common namespace.
    This will make Entities sharing easier as your models will allow to
    point to a global namespace. For instance the message will be
    ``App\Application\Sonata\NotificationBundle\Entity\Message``.

Now, add the new ``Application`` Bundle into the ``bundles.php``::

    // config/bundles.php

    return [
        // ...
        App\Application\Sonata\NotificationBundle\ApplicationSonataNotificationBundle::class => ['all' => true],
    ];

And configure ``SonataNotificationBundle`` to use the newly generated Message class:

.. code-block:: php

    # config/packages/sonata_notification.yaml

    sonata_notification:
        class:
            message: App\Application\Sonata\NotificationBundle\Entity\Message

The only thing left is to update your schema:

.. code-block:: bash

    bin/console doctrine:schema:update --force

.. _`auto_mapping`: http://symfony.com/doc/2.0/reference/configuration/doctrine.html#configuration-overview
