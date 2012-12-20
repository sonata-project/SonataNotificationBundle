Advanced Configuration
======================

Full configuration options:

.. code-block:: yaml

    # app/config/config.yml
    sonata_notification:
        backend: sonata.notification.backend.runtime
        #backend: sonata.notification.backend.postpone
        #backend: sonata.notification.backend.doctrine
        #backend: sonata.notification.backend.rabbitmq

        consumer:
            swift_mailer:
                path:         %kernel.root_dir%/../vendor/swiftmailer

        backends:
            doctrine:
                max_age:      86400     # max age in second
                pause:        500000    # delay in microseconds
                states:                 # raising errors level
                    in_progress: 10
                    error:       20
                    open:        100
                    done:        10000
            rabbitmq:
                exchange:     router
			    queues: 
			        # if `recover` is set to true, the consumer will respond with a `basic.recover` when an exception occurs
			        # otherwise it will not respond at all and the message will be unacknowledged
			       - { queue: defaultQueue, recover: true|false, default: true|false, routing_key: the_routing_key}
                connection:
                    host:     localhost
                    user:     guest
                    pass:     guest
                    port:     5672
                    vhost:    /
                    console_url : http://some.other.host:55999/api

    doctrine:
        orm:
            entity_managers:
                default:
                    mappings:
                        SonataNotificationBundle: ~
