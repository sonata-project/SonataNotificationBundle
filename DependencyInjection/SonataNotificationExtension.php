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

use Symfony\Component\DependencyInjection\Reference;

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
        $container->setParameter('sonata.notification.backend', $config['backend']);

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
        if (isset($config['backends']['rabbitmq'])) {
            $this->configureRabbitmq($container, $config);
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
     * @param ContainerBuilder $container
     * @param array $config
     */
    protected function configureRabbitmq(ContainerBuilder $container, $config)
    {
        $queues = $config['queues'];
        $defaultQueue = $config['default_queue'];
        $connection = $config['backends']['rabbitmq']['connection'];
        $exchange = $config['backends']['rabbitmq']['exchange'];
        $amqBackends = array();

        if(count($queues) == 0) {
            $id = $this->createAMQPBackend($container, $exchange);
            $amqBackends[$defaultQueue] = new Reference($id);
        } else {
            $defaultSet = false;
            foreach ($queues as $queue) {
                $id = $this->createAMQPBackend($container, $exchange, $queue['queue'], $queue['routing_key']);
                $amqBackends[$queue['queue']] = new Reference($id);
                if ($defaultQueue === $queue['queue']) {
                    $defaultSet = true;
                }
            }
            if ($defaultSet === false) {
                throw new \RuntimeException("You need to specify a valid default queue for the rabbitmq backend!");
            }
        }

        $container->getDefinition('sonata.notification.backend.rabbitmq')
            ->replaceArgument(0, $connection)
            ->replaceArgument(1, $queues)
            ->replaceArgument(2, $defaultQueue)
            ->replaceArgument(3, $amqBackends)
        ;
    }

    /**
     * @param ContainerBuilder $container
     * @param string $name
     * @param string $key
     * @return string
     */
    protected function createAMQPBackend(ContainerBuilder $container, $exchange, $name = 'default', $key = '')
    {
        $id = 'sonata.notification.backend.rabbitmq.' . $name;
        $definition = new Definition('Sonata\NotificationBundle\Backend\AMQPBackend', array($exchange, $name, $key));
        $definition->setPublic(false);
        $container->setDefinition($id, $definition);

        return $id;
    }

     /**
     * @param array $config
     * @return void
     */
    public function registerDoctrineMapping(array $config)
    {

    }
}
