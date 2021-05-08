UPGRADE 3.x
===========

UPGRADE FROM 3.x to 3.x
=======================

### Close API

Most of the classes have been marked as `@final` and they will be final in `4.0`.

### Upgrade to SonataDatagridBundle 3.0

There is a minimal BC Break on `MessageManager::getPager`. If you are extending this method you should add parameter and return type hints.

### Support for NelmioApiDocBundle > 3.6 is added

Controllers for NelmioApiDocBundle v2 were moved under `Sonata\NotificationBundle\Controller\Api\Legacy\` namespace and controllers for NelmioApiDocBundle v3 were added as replacement. If you extend them, you MUST ensure they are using the corresponding inheritance.

UPGRADE FROM 3.8 to 3.9
=======================

### SonataEasyExtends is deprecated

Registering `SonataEasyExtendsBundle` bundle is deprecated, it SHOULD NOT be registered.
Register `SonataDoctrineBundle` bundle instead.

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
