<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\NotificationBundle\Tests\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Sonata\NotificationBundle\DependencyInjection\SonataNotificationExtension;

final class SonataNotificationExtensionTest extends AbstractExtensionTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->container->setParameter('kernel.bundles', [
            'SonataDoctrineBundle' => true,
            'SonataAdminBundle' => true,
        ]);
    }

    public function testEmptyConfig(): void
    {
        $this->load();

        $this->assertContainerBuilderHasAlias('sonata.notification.backend', 'sonata.notification.backend.runtime');
        $this->assertContainerBuilderHasService('sonata.notification.consumer.swift_mailer');
        $this->assertContainerBuilderHasService('sonata.notification.consumer.logger');
        $this->assertContainerBuilderHasParameter('sonata.notification.backend', 'sonata.notification.backend.runtime');
    }

    public function testDoNotRegisterDefaultConsumers(): void
    {
        $this->load([
            'consumers' => [
                'register_default' => false,
            ],
        ]);

        $this->assertContainerBuilderNotHasService('sonata.notification.consumer.swift_mailer');
        $this->assertContainerBuilderNotHasService('sonata.notification.consumer.logger');
        $this->assertContainerBuilderNotHasService('sonata.notification.manager.message.default');
        $this->assertContainerBuilderNotHasService('sonata.notification.erroneous_messages_selector');
        $this->assertContainerBuilderNotHasService('sonata.notification.event.doctrine_optimize');
        $this->assertContainerBuilderNotHasService('sonata.notification.event.doctrine_backend_optimize');

        $this->assertContainerBuilderHasParameter('sonata.notification.event.iteration_listeners', []);
    }

    public function testDoctrineBackendNoConfig(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Please configure the sonata_notification.backends.doctrine section');

        $this->load([
            'backend' => 'sonata.notification.backend.doctrine',
        ]);
    }

    public function testDoctrineBackend(): void
    {
        $this->load([
            'backend' => 'sonata.notification.backend.doctrine',
            'backends' => [
                'doctrine' => null,
            ],
            'consumers' => [
                'register_default' => false,
            ],
        ]);

        $this->assertContainerBuilderHasService('sonata.notification.manager.message.default');
        $this->assertContainerBuilderHasService('sonata.notification.erroneous_messages_selector');
        $this->assertContainerBuilderHasService('sonata.notification.event.doctrine_optimize');
        $this->assertContainerBuilderHasService('sonata.notification.event.doctrine_backend_optimize');

        $this->assertContainerBuilderHasParameter('sonata.notification.event.iteration_listeners', [
            'sonata.notification.event.doctrine_backend_optimize',
        ]);
    }

    public function testRabbitMQBackendNoConfig(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Please configure the sonata_notification.backends.rabbitmq section');

        $this->load([
            'backend' => 'sonata.notification.backend.rabbitmq',
        ]);
    }

    public function testRabbitMQBackend(): void
    {
        $this->load([
            'backend' => 'sonata.notification.backend.rabbitmq',
            'backends' => [
                'rabbitmq' => [
                    'exchange' => 'logs',
                ],
            ],
            'consumers' => [
                'register_default' => false,
            ],
        ]);

        $this->assertContainerBuilderNotHasService('sonata.notification.manager.message.default');
        $this->assertContainerBuilderNotHasService('sonata.notification.erroneous_messages_selector');
        $this->assertContainerBuilderNotHasService('sonata.notification.event.doctrine_optimize');
        $this->assertContainerBuilderNotHasService('sonata.notification.event.doctrine_backend_optimize');

        $this->assertContainerBuilderHasParameter('sonata.notification.event.iteration_listeners', []);
    }

    protected function getContainerExtensions(): array
    {
        return [
            new SonataNotificationExtension(),
        ];
    }
}
