Monitoring
==========

The bundle ships with built-in health checks to be used by the .. _LiipMonitorBundle: https://github.com/liip/LiipMonitorBundle , 
see the sonata.notification.backend.heath_check service.

The rabbitmq backend uses the default URL for the .. _management plugin: http://www.rabbitmq.com/management.html API (http://localhost:55672/api)

If you need to change the default URL, you can configure it by setting the "console_url" configuration value:

.. code-block:: yaml

    sonata_notification: 
        backend: sonata.notification.backend.rabbitmq
    
        backends: 
          rabbitmq: 
              exchange:     router
              connection:
                  host:         %rabbitmq_host%
                  user:         %rabbitmq_user%
                  pass:         %rabbitmq_pass%
                  port:         %rabbitmq_port%
                  vhost:        %rabbitmq_vhost%
                  console_url : http://some.other.host:55999/api
