.. index::
    single: Installation
    single: Configuration

Installation
============

Prerequisites
-------------

PHP ^7.2 and Symfony ^4.4 are needed to make this bundle work, there are
also some Sonata dependencies that need to be installed and configured beforehand.

Optional dependencies:

* `SonataAdminBundle <https://sonata-project.org/bundles/admin>`_

And the persistence bundle (currently, not all the implementations of the Sonata persistence bundles are available):

* `SonataDoctrineOrmAdminBundle <https://sonata-project.org/bundles/doctrine-orm-admin>`_

Follow also their configuration step; you will find everything you need in
their own installation chapter.

.. note::

    If a dependency is already installed somewhere in your project or in
    another dependency, you won't need to install it again.

Install Symfony Flex packs
--------------------------

With this method you can directly setup all the entities required to make this bundle work
with the different persistence bundles supported.

If you picked ``SonataDoctrineOrmAdminBundle``, install the Sonata Media ORM pack::

    composer require sonata-project/notification-orm-pack

Install without Symfony Flex packs
----------------------------------

Add ``SonataNotificationBundle`` via composer::

    composer require sonata-project/notification-bundle

If you want to use the REST API, you also need ``friendsofsymfony/rest-bundle`` and ``nelmio/api-doc-bundle``::

    composer require friendsofsymfony/rest-bundle nelmio/api-doc-bundle

There are other optional dependencies::

    composer require enqueue/amqp-lib
    composer require liip/monitor-bundle

Next, be sure to enable the bundles in your ``config/bundles.php`` file if they
are not already enabled::

    // config/bundles.php

    return [
        // ...
        Sonata\NotificationBundle\SonataNotificationBundle::class => ['all' => true],
    ];

Configuration
=============

SonataNotificationBundle Configuration
--------------------------------------

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

Doctrine ORM Configuration
--------------------------

Add the bundle in the config mapping definition (or enable `auto_mapping`_)::

    # config/packages/doctrine.yaml

    doctrine:
        orm:
            entity_managers:
                default:
                    mappings:
                        SonataNotificationBundle: ~

And then create the corresponding entity, ``src/Entity/SonataNotificationMessage``::

    // src/Entity/SonataNotificationMessage.php

    use Doctrine\ORM\Mapping as ORM;
    use Sonata\NotificationBundle\Entity\BaseMessage;

    /**
     * @ORM\Entity
     * @ORM\Table(name="notification__message")
     */
    class SonataNotificationMessage extends BaseMessage
    {
        /**
         * @ORM\Id
         * @ORM\GeneratedValue
         * @ORM\Column(type="integer")
         */
        protected $id;
    }

The only thing left is to update your schema::

    bin/console doctrine:schema:update --force

Next Steps
----------

At this point, your Symfony installation should be fully functional, without errors
showing up from SonataNotificationBundle. If, at this point or during the installation,
you come across any errors, don't panic:

    - Read the error message carefully. Try to find out exactly which bundle is causing the error.
      Is it SonataNotificationBundle or one of the dependencies?
    - Make sure you followed all the instructions correctly, for both SonataNotificationBundle and its dependencies.
    - Still no luck? Try checking the project's `open issues on GitHub`_.

.. _`open issues on GitHub`: https://github.com/sonata-project/SonataNotificationBundle/issues
.. _`auto_mapping`: http://symfony.com/doc/4.4/reference/configuration/doctrine.html#configuration-overviews
