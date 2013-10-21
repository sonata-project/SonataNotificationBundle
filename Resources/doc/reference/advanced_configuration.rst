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
                message_manager: sonata.notification.manager.message.default
                max_age:         86400     # max age in second
                pause:           500000    # delay in microseconds
                batch_size:      10        # number of messages on each iteration
                states:                    # raising errors level
                    in_progress: 10
                    error:       20
                    open:        100
                    done:        10000

            rabbitmq:
                exchange:     router
                connection:
                    host:     localhost
                    user:     guest
                    pass:     guest
                    port:     5672
                    vhost:    /
                    console_url : http://some.other.host:55999/api
        queues:
            # RABBITMQ CONFIGURATION
            # if `recover` is set to true, the consumer will respond with a `basic.recover` when an exception occurs
            # otherwise it will not respond at all and the message will be unacknowledged
            #
            # if dead_letter_exchange is set,failed messages will be rejected and sent to this exchange
            - { queue: defaultQueue, recover: true|false, default: true|false, routing_key: the_routing_key, dead_letter_exchange: 'my.dead.letter.exchange'}
            - { queue: catchall, default: true}

            # DOCTRINE CONFIGURATION
            - { queue: sonata_page, types: [sonata.page.create_snapshot, sonata.page.create_snapshots]}
            - { queue: catchall, default: true}

        # Listeners attached to the IterateEvent
        # Iterate event is thrown on each command iteration
        #
        # iteration listener class must implement Sonata\NotificationBundle\Event\IterationListener
        iteration_listeners:
            - the_service_id
            - the_other_service_id

    doctrine:
        orm:
            entity_managers:
                default:
                    mappings:
                        SonataNotificationBundle: ~
                        ApplicationSonataNotificationBundle: ~
