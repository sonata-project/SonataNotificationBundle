<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\NotificationBundle\Tests\DependencyInjection;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\Bundle\DoctrineBundle\Registry;
use PHPUnit\Framework\TestCase;
use Sonata\NotificationBundle\DependencyInjection\Compiler\NotificationCompilerPass;
use Sonata\NotificationBundle\DependencyInjection\SonataNotificationExtension;
use Sonata\NotificationBundle\SonataNotificationBundle;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SonataNotificationExtensionTest extends TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    protected function tearDown()
    {
        unset($this->container);
    }

    public function testEmptyConfig()
    {
        $container = $this->getContainerBuilder([
            'MonologBundle' => MonologBundle::class,
            'SwiftmailerBundle' => SwiftmailerBundle::class,
        ]);
        $extension = new SonataNotificationExtension();
        $extension->load([], $container);

        $this->assertAlias('sonata.notification.backend.runtime', 'sonata.notification.backend');
        $this->assertHasDefinition('sonata.notification.consumer.swift_mailer');
        $this->assertHasDefinition('sonata.notification.consumer.logger');
        $this->assertParameter('sonata.notification.backend.runtime', 'sonata.notification.backend');

        $container->compile();
    }

    public function testDoNotRegisterDefaultConsumers()
    {
        $container = $this->getContainerBuilder();
        $extension = new SonataNotificationExtension();
        $extension->load([
            [
                'consumers' => [
                    'register_default' => false,
                ],
            ],
        ], $container);

        $this->assertHasNoDefinition('sonata.notification.consumer.swift_mailer');
        $this->assertHasNoDefinition('sonata.notification.consumer.logger');
        $this->assertHasNoDefinition('sonata.notification.manager.message.default');
        $this->assertHasNoDefinition('sonata.notification.erroneous_messages_selector');
        $this->assertHasNoDefinition('sonata.notification.event.doctrine_optimize');
        $this->assertHasNoDefinition('sonata.notification.event.doctrine_backend_optimize');

        $this->assertParameter([], 'sonata.notification.event.iteration_listeners');

        $container->compile();
    }

    public function testDoctrineBackendNoConfig()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Please configure the sonata_notification.backends.doctrine section');

        $container = $this->getContainerBuilder([
            'DoctrineBundle' => DoctrineBundle::class,
        ]);
        $extension = new SonataNotificationExtension();

        $extension->load([
            [
                'backend' => 'sonata.notification.backend.doctrine',
            ],
        ], $container);
    }

    public function testDoctrineBackend()
    {
        $container = $this->getContainerBuilder([
            'DoctrineBundle' => DoctrineBundle::class,
        ]);
        $extension = new SonataNotificationExtension();
        $extension->load([
            [
                'backend' => 'sonata.notification.backend.doctrine',
                'backends' => [
                    'doctrine' => null,
                ],
                'consumers' => [
                    'register_default' => false,
                ],
            ],
        ], $container);

        $this->assertHasDefinition('sonata.notification.manager.message.default');
        $this->assertHasDefinition('sonata.notification.erroneous_messages_selector');
        $this->assertHasDefinition('sonata.notification.event.doctrine_optimize');
        $this->assertHasDefinition('sonata.notification.event.doctrine_backend_optimize');

        $this->assertParameter(
            ['sonata.notification.event.doctrine_backend_optimize'],
            'sonata.notification.event.iteration_listeners'
        );

        $container->compile();
    }

    public function testRabbitMQBackendNoConfig()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Please configure the sonata_notification.backends.rabbitmq section');

        $container = $this->getContainerBuilder();
        $extension = new SonataNotificationExtension();

        $extension->load([
            [
                'backend' => 'sonata.notification.backend.rabbitmq',
            ],
        ], $container);
    }

    public function testRabbitMQBackend()
    {
        $container = $this->getContainerBuilder();
        $extension = new SonataNotificationExtension();
        $extension->load([
            [
                'backend' => 'sonata.notification.backend.rabbitmq',
                'backends' => [
                    'rabbitmq' => [
                        'exchange' => 'logs',
                    ],
                ],
                'consumers' => [
                    'register_default' => false,
                ],
            ],
        ], $container);

        $this->assertHasNoDefinition('sonata.notification.manager.message.default');
        $this->assertHasNoDefinition('sonata.notification.erroneous_messages_selector');
        $this->assertHasNoDefinition('sonata.notification.event.doctrine_optimize');
        $this->assertHasNoDefinition('sonata.notification.event.doctrine_backend_optimize');

        $this->assertParameter([], 'sonata.notification.event.iteration_listeners');

        $container->compile();
    }

    private function getContainerBuilder(array $bundles = [])
    {
        $container = new ContainerBuilder();

        $containerBundles = array_merge(
            ['SonataNotificationBundle' => SonataNotificationBundle::class], $bundles
        );
        $container->setParameter('kernel.bundles', $containerBundles);

        $container->addCompilerPass(new NotificationCompilerPass());

        if (isset($containerBundles['MonologBundle'])) {
            $container->register('logger')
                ->setClass(Logger::class)
                ->setPublic(true);
        }
        if (isset($containerBundles['SwiftmailerBundle'])) {
            $container->register('mailer')
                ->setClass(\Swift_Mailer::class)
                ->setPublic(true);
        }
        if (isset($containerBundles['DoctrineBundle'])) {
            $container->register('doctrine')
                ->setClass(Registry::class)
                ->setPublic(true);
        }

        return $this->container = $container;
    }

    private function assertAlias($alias, $service)
    {
        $this->assertSame(
            $alias, (string) $this->container->getAlias($service), sprintf('%s alias is correct', $service)
        );
    }

    private function assertParameter($expectedValue, $name)
    {
        $this->assertSame(
            $expectedValue, $this->container->getParameter($name), sprintf('%s parameter is correct', $name)
        );
    }

    private function assertHasDefinition($definition)
    {
        $this->assertTrue(
            $this->container->hasDefinition($definition) ? true : $this->container->hasAlias($definition)
        );
    }

    private function assertHasNoDefinition($service)
    {
        $this->assertFalse(
            $this->container->hasDefinition($service) ? true : $this->container->hasAlias($service)
        );
    }
}
