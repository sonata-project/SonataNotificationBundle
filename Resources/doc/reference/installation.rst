Installation
============

To begin, add the dependent bundles to the vendor/bundles directory. Add the following lines to the file deps::

    [SonataNotificationBundle]
        git=http://github.com/sonata-project/SonataNotificationBundle.git
        target=/bundles/Sonata/NotificationBundle


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
        'Sonata'                             => __DIR__,

        // ... other declarations
    ));

Then add these bundles in the config mapping definition:

.. code-block:: yaml

    # app/config/config.yml
    SonataNotificationBundle: ~

Configuration
-------------

To use the ``SonataNotificationBundle``, add the following lines to your application configuration
file.

.. code-block:: yaml

    # app/config/config.yml
    sonata_notification:
        iterator: sonata.notification.iterator.mysql
        producer: sonata.notification.producer.model

