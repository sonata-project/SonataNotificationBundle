# Change Log
All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## [3.13.0](https://github.com/sonata-project/SonataNotificationBundle/compare/3.12.0...3.13.0) - 2021-06-27
### Changed
- [[#580](https://github.com/sonata-project/SonataNotificationBundle/pull/580)] Marked classes to be final in the next major version ([@franmomu](https://github.com/franmomu))

### Fixed
- [[#577](https://github.com/sonata-project/SonataNotificationBundle/pull/577)] Fixed API form handling ([@franmomu](https://github.com/franmomu))

## [3.12.0](https://github.com/sonata-project/SonataNotificationBundle/compare/3.11.0...3.12.0) - 2021-03-24
### Added
- [[#564](https://github.com/sonata-project/SonataNotificationBundle/pull/564)] Support for "symfony/config:^5.2" ([@phansys](https://github.com/phansys))
- [[#564](https://github.com/sonata-project/SonataNotificationBundle/pull/564)] Support for "symfony/dependency-injection:^5.2" ([@phansys](https://github.com/phansys))
- [[#564](https://github.com/sonata-project/SonataNotificationBundle/pull/564)] Support for "symfony/http-foundation:^5.2" ([@phansys](https://github.com/phansys))
- [[#545](https://github.com/sonata-project/SonataNotificationBundle/pull/545)] Add support for PHP 8.x ([@Yozhef](https://github.com/Yozhef))

### Changed
- [[#567](https://github.com/sonata-project/SonataNotificationBundle/pull/567)] Minimum supported version for "nelmio/api-doc-bundle" bumped to >=2.13.5 ([@phansys](https://github.com/phansys))

## [3.11.0](https://github.com/sonata-project/SonataNotificationBundle/compare/3.10.0...3.11.0) - 2021-01-27
### Added
- [[#522](https://github.com/sonata-project/SonataNotificationBundle/pull/522)] Added support for `doctrine/persistence` 2 ([@core23](https://github.com/core23))
- [[#478](https://github.com/sonata-project/SonataNotificationBundle/pull/478)] Support for `nelmio/api-doc-bundle` >= 3.6 ([@wbloszyk](https://github.com/wbloszyk))

## [3.10.0](sonata-project/SonataNotificationBundle/compare/3.9.0...3.10.0) - 2020-09-22
### Changed
- [[#457](https://github.com/sonata-project/SonataNotificationBundle/pull/457)]
  Support for deprecated "rest" routing type in favor for xml
([@wbloszyk](https://github.com/wbloszyk))
- [[#471](https://github.com/sonata-project/SonataNotificationBundle/pull/471)]
  Bump `sonata-project/datagrid-bundle` version
([@core23](https://github.com/core23))

### Fixed
- [[#458](https://github.com/sonata-project/SonataNotificationBundle/pull/458)]
  Make `ErroneousMessagesSelector` service public again
([@core23](https://github.com/core23))

## [3.9.0](https://github.com/sonata-project/SonataNotificationBundle/compare/3.8.0...3.9.0) - 2020-07-26
### Added
- [[#448](https://github.com/sonata-project/SonataNotificationBundle/pull/448)]
  Added public alias
`Sonata\NotificationBundle\Controller\Api\MessageController` for
`sonata.notification.controller.api.message` service
([@wbloszyk](https://github.com/wbloszyk))

### Changed
- [[#452](https://github.com/sonata-project/SonataNotificationBundle/pull/452)]
  SonataEasyExtendsBundle is now optional, using SonataDoctrineBundle is
preferred ([@jordisala1991](https://github.com/jordisala1991))
- [[#452](https://github.com/sonata-project/SonataNotificationBundle/pull/452)]
  Use Laminas instead of deprecated Zend
([@jordisala1991](https://github.com/jordisala1991))

### Deprecated
- [[#452](https://github.com/sonata-project/SonataNotificationBundle/pull/452)]
  Using SonataEasyExtendsBundle to add Doctrine mapping information
([@jordisala1991](https://github.com/jordisala1991))

### Fixed
- [[#448](https://github.com/sonata-project/SonataNotificationBundle/pull/448)]
  Fix RestFul API - `Class could not be determined for Controller identified`
Error ([@wbloszyk](https://github.com/wbloszyk))

### Removed
- [[#451](https://github.com/sonata-project/SonataNotificationBundle/pull/451)]
  Removed support for deprecated "rest" routing type
([@wbloszyk](https://github.com/wbloszyk))
- [[#450](https://github.com/sonata-project/SonataNotificationBundle/pull/450)]
  Support for PHP < 7.2 ([@wbloszyk](https://github.com/wbloszyk))

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
