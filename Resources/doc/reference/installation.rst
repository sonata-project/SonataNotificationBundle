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
                        ApplicationSonataNotificationBundle: ~

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

Extending the Bundle
--------------------
At this point, the bundle is functional, but not quite ready yet. You need to
generate the correct entities for the media:

    php app/console sonata:easy-extends:generate SonataNotificationBundle

If you specify no parameter, the files are generated in app/Application/Sonata...
but you can specify the path with ``--dest=src``

.. note::

    The command will generate domain objects in an ``Application`` namespace.
    So you can point entities' associations to a global and common namespace.
    This will make Entities sharing easier as your models will allow to
    point to a global namespace. For instance the user will be
    ``Application\Sonata\NotificationBundle\Entity\Message``.

Now, add the new `Application` Bundle into the kernel:

.. code-block:: php

    <?php

    // AppKernel.php
    class AppKernel {
        public function registerbundles()
        {
            return array(
                // Application Bundles
                // ...
                new Application\Sonata\NotificationBundle\ApplicationSonataNotificationBundle(),
                // ...

            )
        }
    }