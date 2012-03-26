<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\NotificationBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class NotificationCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('sonata.notification.dispatcher')) {
            return;
        }

        $definition = $container->getDefinition('sonata.notification.dispatcher');

        foreach ($container->findTaggedServiceIds('sonata.notification') as $id => $events) {
            foreach ($events as $event) {
                $priority = isset($event['priority']) ? $event['priority'] : 0;

                if (!isset($event['type'])) {
                    throw new \InvalidArgumentException(sprintf('Service "%s" must define the "type" attribute on "sonata.notification" tags.', $id));
                }

                $definition->addMethodCall('addListener', array($event['type'], array(new Reference($id), 'process'), $priority));
            }
        }
    }
}
