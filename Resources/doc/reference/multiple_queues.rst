Multiple queues
===============

Some notification backends support running multiple queues (currently rabbitmq only).

This makes it possible to send different messages to different queues - for example to avoid messages which take longer
to consume to block messages which take only a short amount of time.

To enable multiple queues, simply define a `queues` node in your configuration:

.. code-block:: yaml

    # app/config/config.yml
    sonata_notification: 
        backend: sonata.notification.backend.rabbitmq
        default_queue: catchall
        queues: 
            - { queue: transcoder, routing_key: start.transcode.video }
            - { queue: catchall, routing_key: send.some.mail }
        backends: 
        rabbitmq: 
            exchange:     router
            connection:
                host:     %rabbitmq_host%
                user:     %rabbitmq_user%
                pass:     %rabbitmq_pass%
                port:     %rabbitmq_port%
                vhost:    %rabbitmq_vhost%
                
                
This will define 2 different queues: `transcoder` and `catchall` and 3 routing keys

    - `start.transcode.audio`
    - `start.transcode.video`
    - `send.some.mail`
    
Each routing key is bound to a queue. In the above example you will need to create 2 consumers, where each
consumer will handle messages sent by a specific queue:

    - `php app/console sonata:notification:start --env=prod --iteration=250 --type=start.transcode.video`
    - `php app/console sonata:notification:start --env=prod --iteration=250 --type=send.some.mail`
    
    
When publishing a message with the type `start.transcode.video`, those
messages will be handled by the first consumer. Messages with the routing key `send.some.mail` will
be handled by the `catchall` consumer.

If a message is published which cannot be mapped to a queue because it's not defined in the configuration,
it will be published to the queue defined in the `default_queue` configuration node.

