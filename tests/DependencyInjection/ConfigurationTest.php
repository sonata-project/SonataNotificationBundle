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

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionConfigurationTestCase;
use Sonata\NotificationBundle\Admin\MessageAdmin;
use Sonata\NotificationBundle\DependencyInjection\Configuration;
use Sonata\NotificationBundle\DependencyInjection\SonataNotificationExtension;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

final class ConfigurationTest extends AbstractExtensionConfigurationTestCase
{
    public function testDefault(): void
    {
        $this->assertProcessedConfigurationEquals([
            'backend' => 'sonata.notification.backend.runtime',
            'queues' => [],
            'consumers' => [
                'register_default' => true,
            ],
            'iteration_listeners' => [],
            'class' => [
                'message' => 'App\Entity\Message',
            ],
            'admin' => [
                'enabled' => false,
                'message' => [
                    'class' => MessageAdmin::class,
                    'controller' => 'SonataNotificationBundle:MessageAdmin',
                    'translation' => 'SonataNotificationBundle',
                ],
            ],
        ], [
            __DIR__.'/../Fixtures/configuration.yaml',
        ]);
    }

    protected function getContainerExtension(): ExtensionInterface
    {
        return new SonataNotificationExtension();
    }

    protected function getConfiguration(): ConfigurationInterface
    {
        return new Configuration();
    }
}
