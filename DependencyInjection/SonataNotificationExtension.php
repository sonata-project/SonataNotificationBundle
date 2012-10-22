<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\NotificationBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Definition;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

use Sonata\NotificationBundle\Model\MessageInterface;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class SonataNotificationExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('core.xml');
        $loader->load('doctrine_orm.xml');
        $loader->load('backend.xml');
        $loader->load('consumer.xml');

        $bundles = $container->getParameter('kernel.bundles');
        if (isset($bundles['LiipMonitorBundle'])) {
            $loader->load('checkmonitor.xml');
        }

        $container->setAlias('sonata.notification.backend', $config['backend']);

        $this->registerDoctrineMapping($config);
        $this->registerParameters($container, $config);
        $this->configureBackends($container, $config);
     }

     /**
      * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
      * @param $config
      * @return void
      */
    public function registerParameters(ContainerBuilder $container, $config)
    {
        $container->setParameter('sonata.notification.message.class', $config['class']['message']);
    }

    /**
      * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
      * @param $config
      * @return void
      */
    public function configureBackends(ContainerBuilder $container, $config)
    {
        if (isset($config['backends']['rabbitmq']) && isset($config['backends']['rabbitmq']['queues']) && isset($config['backends']['rabbitmq']['default_queue'])) {

            $default = $config['backends']['rabbitmq']['default_queue'];
            $connection = $config['backends']['rabbitmq']['connection'];
            $exchange = $config['backends']['rabbitmq']['exchange'];

            $defaultSet = false;
            foreach ($config['backends']['rabbitmq']['queues'] as $name => $queue) {
                if ($name === $default) {
                    $container->getDefinition('sonata.notification.backend.rabbitmq')
                        ->replaceArgument(0, $connection)
                        ->replaceArgument(1, $exchange)
                        ->replaceArgument(2, $queue['queue'])
                        ->replaceArgument(3, $queue['routing_key'])
                    ;
                    $defaultSet = true;
                }

                // runtime backend does not define different queues, simply alias the queues to the runtimebackend
                if ($config['backend'] === 'sonata.notification.backend.runtime') {
                    $container->setAlias('sonata.notification.backend.queue_' . $queue['queue'], $config['backend']);
                } else {
                    $definition = new Definition('Sonata\NotificationBundle\Backend\AMQPBackend', array($connection, $exchange, $queue['queue'], $queue['routing_key']));
                    $container->setDefinition('sonata.notification.backend.queue_' . $queue['queue'], $definition);
                }
            }

            if ($defaultSet === false) {
                throw new \RuntimeException('You need to defint an existing default_queue for the sonata.rabbitmq backend.');
            }
        } else {
            $container->removeDefinition('sonata.notification.backend.rabbitmq');
        }

        if (isset($config['backends']['doctrine'])) {
            $container->getDefinition('sonata.notification.backend.doctrine')
                ->replaceArgument(1, array(
                    MessageInterface::STATE_DONE => $config['backends']['doctrine']['states']['done'],
                    MessageInterface::STATE_ERROR => $config['backends']['doctrine']['states']['error'],
                    MessageInterface::STATE_IN_PROGRESS => $config['backends']['doctrine']['states']['in_progress'],
                    MessageInterface::STATE_OPEN => $config['backends']['doctrine']['states']['open'],
                ))
                ->replaceArgument(2, $config['backends']['doctrine']['pause'])
                ->replaceArgument(3, $config['backends']['doctrine']['max_age'])
            ;
        }
    }

     /**
     * @param array $config
     * @return void
     */
    public function registerDoctrineMapping(array $config)
    {

    }
}