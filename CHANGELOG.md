# Change Log
All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## [3.8.0](https://github.com/sonata-project/SonataNotificationBundle/compare/3.7.0...3.8.0) - 2020-06-26
### Removed
- [[#440](https://github.com/sonata-project/SonataNotificationBundle/pull/440)]
  Remove SonataCoreBundle dependencies
([@wbloszyk](https://github.com/wbloszyk))

## [3.7.0](https://github.com/sonata-project/SonataNotificationBundle/compare/3.6.2...3.7.0) - 2020-03-26
### Fixed
- Fix doctrine deprecations

### Removed
- SonataEasyExtendsBundle
- Support for Symfony < 3.4
- Support for Symfony >= 4, < 4.2

## [3.6.2](https://github.com/sonata-project/SonataNotificationBundle/compare/3.6.1...3.6.2) - 2019-07-20
### Changed
- Make `sonata.notification.consumer.metadata` service public

## [3.6.1](https://github.com/sonata-project/SonataNotificationBundle/compare/3.6.0...3.6.1) - 2019-03-13
### Fixed
`TypeError` when `AMQPBackend`'s `publish` method is called.

## [3.6.0](https://github.com/sonata-project/SonataNotificationBundle/compare/3.5.1...3.6.0) - 2018-05-25

### Added
- Added `Sonata\NotificationBundle\Backend\BackendInterface` service alias
- Added `Sonata\NotificationBundle\Entity\MessageManager` service alias

### Changed
- Update RestartCommand check count message only for not pulling mode
- sendEmail function to allow for the setting of a return path
- Do not use deprecated `AMQPBackendDispatcher::getChannel` method.
- made the `sonata.notification.dispatcher` service public to fix a bug when running `sonata:notification:start`

### Fixed
- Fix deprecation for symfony/config 4.2+
- Make services public

### Removed
- support for php 5 and php 7.0

## [3.5.1](https://github.com/sonata-project/SonataNotificationBundle/compare/3.5.0...3.5.1) - 2018-05-25
# Changed
- Force use existing translation strings in breadcrumb for Message entity in Admin panel
- `enqueue/amqp-lib` is an optional dependency now

# Fixed
- API and Admin services are only available when using Doctrine as a backend

## [3.5.0](https://github.com/sonata-project/SonataNotificationBundle/compare/3.4.0...3.5.0) - 2018-04-26
### Added
- Added possibility to add an attachment to SwiftMailer Consumer

### Fixed
- Data fetched from stats counts are now properly manipulated (in case of doctrine backend is used)
- Typo in message status

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
