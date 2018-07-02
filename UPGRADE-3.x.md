UPGRADE 3.x
===========

UPGRADE FROM 3.5 to 3.6
=======================

### RabbitMQ status provider

Using `"guzzlehttp/guzzle": "^3.8"` client for providing status of RabbitMQ queue tagged as deprecated, but still works (for BC).

You need to install [HttplugBundle](http://docs.php-http.org/en/latest/integrations/symfony-bundle.html#installation)
and configure it for use with any [HttpClient](https://packagist.org/providers/php-http/client-implementation) 
to enable use of an abstract php-http client for providing status of RabbitMQ queue.

UPGRADE FROM 3.2 to 3.3
=======================

### AMQP

You might face some issues (though we tried to keep everything BC) if you are extending `AMQPBackend`, `AMQPBackendDispatcher` or `AMQPMessageIterator` classes 
or rely on their `__construct` method signature.

If you want to migrate to another amqp interop compatible transport, say `enqueue/amqp-ext`, the BC layer want work and the exception is thrown.

UPGRADE FROM 3.0 to 3.1
=======================

### Tests

All files under the ``Tests`` directory are now correctly handled as internal test classes. 
You can't extend them anymore, because they are only loaded when running internal tests. 
More information can be found in the [composer docs](https://getcomposer.org/doc/04-schema.md#autoload-dev).
