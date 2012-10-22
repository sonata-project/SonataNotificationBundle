Installation
============

To begin, add the dependent bundles::

    php composer.phar require sonata-project/notification-bundle  # optional
    php composer.phar require videlalvaro/php-amqplib --no-update # optional
    php composer.phar require liip/monitor-bundle --no-update     # optional
    php composer.phar update


Now, add the new `SonataNotificationBundle` Bundle to the kernel

.. code-block:: php

    <?php
    public function registerbundles()
    {
        return array(
            // Application Bundles
            new Sonata\NotificationBundle\SonataNotificationBundle(),
        );
    }

Then add these bundles in the config mapping definition:

.. code-block:: yaml

    doctrine:
        dbal:
            # ...

            types:
                json: Sonata\Doctrine\Types\JsonType

        orm:
            # ...
            entity_managers:
                default:
                        # ...
                    mappings:
                        # ...
                        SonataNotificationBundle: ~

Configuration
-------------

To use the ``SonataNotificationBundle``, add the following lines to your application configuration
file.

Backend availables :

 * ``sonata.notification.backend.runtime`` : direct call, no benefit but useful for testing purpose
 * ``sonata.notification.backend.postpone``: post-pone the messages to be dispatched on kernel.terminate
 * ``sonata.notification.backend.doctrine``: use database to store message, require a background task to be started and supervised, decent starting point for a small amount of async task
 * ``sonata.notification.backend.rabbitmq``: use the RabbitMQ engine to handle messaging, best performance

.. code-block:: yaml

    # app/config/config.yml
    sonata_notification:
        backend: sonata.notification.backend.runtime
