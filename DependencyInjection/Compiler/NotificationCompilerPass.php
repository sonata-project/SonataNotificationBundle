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

use Sonata\NotificationBundle\Event\IterateEvent;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

class NotificationCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('sonata.notification.dispatcher')) {
            return;
        }

        $definition = $container->getDefinition('sonata.notification.dispatcher');

        $informations = array();

        foreach ($container->findTaggedServiceIds('sonata.notification.consumer') as $id => $events) {
            foreach ($events as $event) {
                $priority = isset($event['priority']) ? $event['priority'] : 0;

                if (!isset($event['type'])) {
                    throw new \InvalidArgumentException(sprintf('Service "%s" must define the "type" attribute on "sonata.notification" tags.', $id));
                }

                if (!isset($informations[$event['type']])) {
                    $informations[$event['type']] = array();
                }

                $informations[$event['type']][] = $id;

                $definition->addMethodCall('addListenerService', array($event['type'], array($id, 'process'), $priority));
            }
        }

        $container->getDefinition('sonata.notification.consumer.metadata')->replaceArgument(0, $informations);

        if ($container->getParameter('sonata.notification.event.iteration_listeners')) {
            $ids = $container->getParameter('sonata.notification.event.iteration_listeners');

            foreach ($ids as $serviceId) {
                $definition = $container->getDefinition($serviceId);

                $class = new \ReflectionClass($definition->getClass());
                if (!$class->implementsInterface('Sonata\NotificationBundle\Event\IterationListener')) {
                    throw new RuntimeException('Iteration listeners must implement Sonata\NotificationBundle\Event\IterationListener');
                }

                $definition->addTag('kernel.event_listener', array( 'event' => IterateEvent::EVENT_NAME, 'method' => 'iterate'));
            }
        }
    }
}
