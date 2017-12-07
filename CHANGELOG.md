# Change Log
All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## [3.3.0](https://github.com/sonata-project/SonataNotificationBundle/compare/3.2.0...3.3.0) - 2017-12-07
### Added
- Added Russian translations

### Changed
- Migrate from php-amqplib/php-amqplib to enqueue/amqp-lib package

### Fixed
- It is now allowed to install Symfony 4

### Removed
- Support for old versions of PHP and Symfony.
- Support deprecations for old form alias usage

## [3.2.0](https://github.com/sonata-project/SonataNotificationBundle/compare/3.1.0...3.2.0) - 2017-09-14
### Added
- Support for CC/BCC fields in `SwiftMailerConsumer`
- Added prefetch count configuration in AMQPBackend

### Fixed
- Fixed hardcoded paths to classes in `.xml.skeleton` files of config
- Use `EventDispatcher` instead of deprecated `ContainerAwareEventDispatcher`

## [3.1.0](https://github.com/sonata-project/SonataNotificationBundle/compare/3.0.0...3.1.0) - 2017-02-03
### Added
- Add dead letter handling in AMQPBackend
- Added per-queue message TTL in AMQPBackend

### Changed
- Changes the name of the vendor videlalvaro/php-amqplib to its new name php-amqplib/php-amqplib
- dependency from `guzzle/guzzle` to `guzzlehttp/guzzle`, because it is deprecated
- array `QueryParam` parameter to map
- FosRest `SerializationContext` to `Context`

### Fixed
- Fix deprecated usage of `Admin` class
- Fixed duplicate translation of batch actions
- Missing italian translation

### Removed
- internal test classes are now excluded from the autoloader
