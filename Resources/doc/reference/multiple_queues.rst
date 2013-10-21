Multiple queues
===============

Some notification backends (doctrine and rabbitmq) support running multiple queues.

This makes it possible to send different messages to different queues - for example to avoid messages which take longer
to consume to block messages which take only a short amount of time.

.. note::

    Depends on the backend used, the configuration can be differents and the message handling can also be different.

RabbitMQ
~~~~~~~~

To enable multiple queues, simply define a `queues` node in your configuration:

.. code-block:: yaml

    # app/config/config.yml
    sonata_notification:
        backend: sonata.notification.backend.rabbitmq
        queues:
            - { queue: transcoder, routing_key: start.transcode.video }
            - { queue: catchall, default: true }

        backends:
            rabbitmq:
                exchange:     router
                connection:
                    host:     %rabbitmq_host%
                    user:     %rabbitmq_user%
                    pass:     %rabbitmq_pass%
                    port:     %rabbitmq_port%
                    vhost:    %rabbitmq_vhost%


This will define 2 different queues: `transcoder` and `catchall` and where the `transcoder` queue is bound to a routing key:

    - `start.transcode.video`

In the above example you will need to start 2 processes, where each process will handle messages sent by a specific queue:

    - `php app/console sonata:notification:start --env=prod --iteration=250 --type=start.transcode.video`
    - `php app/console sonata:notification:start --env=prod --iteration=250`


When publishing a message with the type `start.transcode.video`, those messages will be handled by the first consumer.
Any other messagetype will be handled by the `catchall` consumer, as it has been set to be the default one.

Doctrine
~~~~~~~~

To enable multiple queues, simply define a `queues` node in your configuration:

.. code-block:: yaml

    # app/config/config.yml
    sonata_notification:
        backend: sonata.notification.backend.doctrine
        backends:
            doctrine:
                max_age:      86400     # max age in second
                pause:        500000    # delay in microseconds
                states:                 # raising errors level
                    in_progress: 10
                    error:       20
                    open:        100
                    done:        10000

        queues:
            - { queue: sonata_page, types: [sonata.page.create_snapshot, sonata.page.create_snapshots]}
            - { queue: catchall, default: true }

This will define 2 different queues: `sonata_page` and `catchall` and where the `sonata_page` queue is bound to two messages types:

    - `sonata.page.create_snapshot`
    - `sonata.page.create_snapshots`

In the above example you will need to create 2 processes, where each process will handle messages sent by a specific queue:

    - `php app/console sonata:notification:start --env=prod --iteration=250 --type=sonata.page.create_snapshot`
    - `php app/console sonata:notification:start --env=prod --iteration=250`


When publishing a message with the type `sonata.page.create_snapshot` or `sonata.page.create_snapshots`, those messages will be handled by the first consumer.
Any other message types will be handled by the `catchall` consumer, as it has been set to be the default one.
