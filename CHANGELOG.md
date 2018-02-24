# Change Log
All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## [3.4.0](https://github.com/sonata-project/SonataNotificationBundle/compare/3.3.1...3.4.0) - 2018-02-23
### Changed
- Require symfony/security-core instead of symfony/security
- Refactored bundle configuration
- Notification backend services are marked as public

### Fixed
- `each()` is deprecated since PHP 7.2
- Remove var **definition** override
- Commands not working on symfony4

### Removed
- Removed compatibility with older versions of FOSRestBundle (<2.1)

## [3.3.1](https://github.com/sonata-project/SonataNotificationBundle/compare/3.3.0...3.3.1) - 2018-01-26
### Changed
- Auto-register all aliases as public
- Auto-register consumer as public service
 
### Fixed
- `isRequired()` was removed since a default is specified
- MessageAdmin loads correct ChoiceType for the state filter (instead of ChoiceFilter)

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
