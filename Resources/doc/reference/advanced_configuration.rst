Advanced Configuration
======================

Full configuration options:

.. code-block:: yaml

    # app/config/config.yml
    sonata_notification:
        backend: sonata.notification.backend.runtime
        #backend: sonata.notification.backend.doctrine
        #backend: sonata.notification.backend.rabbitmq

        consumer:
            swift_mailer:
                path:         %kernel.root_dir%/../vendor/swiftmailer

        backends:
            rabbitmq:
                exchange:     router
                queue:        msgs
                connection:
                    host:     localhost
                    user:     guest
                    pass:     guest
                    port:     5672
                    vhost:    /

    doctrine:
        orm:
            entity_managers:
                default:
                    mappings:
                        SonataNotificationBundle: ~