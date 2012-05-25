Installation
============

To begin, add the dependent bundles to the vendor/bundles directory. Add the following lines to the file deps::

    [SonataNotificationBundle]
        git=git://github.com/sonata-project/SonataNotificationBundle.git
        target=/bundles/Sonata/NotificationBundle

    [PhpAmqplib]
        git=git://github.com/videlalvaro/php-amqplib.git
        target=/php-amqplib

    [SonataDoctrineExtensions]
        git=git@github.com:sonata-project/sonata-doctrine-extensions.git
        target=/sonata-doctrine-extensions

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

Update the ``autoload.php`` to add new namespaces:

.. code-block:: php

    <?php
    $loader->registerNamespaces(array(
        'Sonata'          => array(
            __DIR__ .'/../vendor/bundles',
            __DIR__.'/../vendor/sonata-doctrine-extensions/src',
        ),
        'PhpAmqpLib'      => __DIR__ . '/../vendor/php-amqplib/PhpAmqpLib',
        // ... other declarations
    ));

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
 * ``sonata.notification.backend.doctrine``: use database to store message, require a background task to be started and supervised, decent starting point for a small amount of async task
 * ``sonata.notification.backend.rabbitmq``: use the RabbitMQ engine to handle messaging, best performance

.. code-block:: yaml

    # app/config/config.yml
    sonata_notification:
        backend: sonata.notification.backend.runtime
