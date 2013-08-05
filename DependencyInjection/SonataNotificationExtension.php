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
use Sonata\EasyExtendsBundle\Mapper\DoctrineCollector;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class SonataNotificationExtension extends Extension
{
    protected $amqpCounter = 0;

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
        $loader->load('selector.xml');
        $loader->load('event.xml');

        $bundles = $container->getParameter('kernel.bundles');
        if (isset($bundles['SonataDoctrineORMAdminBundle'])) { // for now, only support for ORM
            $loader->load('admin.xml');
        }

        if (isset($bundles['LiipMonitorBundle'])) {
            $loader->load('checkmonitor.xml');
        }

        $container->setAlias('sonata.notification.backend', $config['backend']);
        $container->setParameter('sonata.notification.backend', $config['backend']);

        $this->registerDoctrineMapping($config);
        $this->registerParameters($container, $config);
        $this->configureBackends($container, $config);
        $this->configureClass($container, $config);
        $this->configureListeners($container, $config);
        $this->configureAdmin($container, $config);
     }

    protected function configureListeners(ContainerBuilder $container, $config)
    {
        $ids = $config['iteration_listeners'];

        if ($config['doctrine_optimize']) {
            $config['doctrine_backend_optimize'] = false;
            $ids[] = 'sonata.notification.event.doctrine_optimize';
        }

        if ($config['doctrine_backend_optimize']) {
            $ids[] = 'sonata.notification.event.doctrine_backend_optimize';
        }

        $container->setParameter('sonata.notification.event.iteration_listeners', $ids);
    }

    /**
     * @param ContainerBuilder $container
     * @param array            $config
     */
    public function configureClass(ContainerBuilder $container, $config)
    {
        // admin configuration
        $container->setParameter('sonata.notification.admin.message.entity',       $config['class']['message']);

        // manager configuration
        $container->setParameter('sonata.notification.manager.message.entity',     $config['class']['message']);
    }

    /**
     * @param ContainerBuilder $container
     * @param array            $config
     */
    public function configureAdmin(ContainerBuilder $container, $config)
    {
        $container->setParameter('sonata.notification.admin.message.class',              $config['admin']['message']['class']);
        $container->setParameter('sonata.notification.admin.message.controller',         $config['admin']['message']['controller']);
        $container->setParameter('sonata.notification.admin.message.translation_domain', $config['admin']['message']['translation']);
    }

     /**
      * @param ContainerBuilder $container
      * @param array            $config
      */
    public function registerParameters(ContainerBuilder $container, $config)
    {
        $container->setParameter('sonata.notification.message.class', $config['class']['message']);
        $container->setParameter('sonata.notification.admin.message.entity', $config['class']['message']);
    }

    /**
      * @param ContainerBuilder $container
      * @param array            $config
      */
    public function configureBackends(ContainerBuilder $container, $config)
    {
        if (isset($config['backends']['rabbitmq']) && $config['backend']  === 'sonata.notification.backend.rabbitmq') {
            $this->configureRabbitmq($container, $config);
        } else {
            $container->removeDefinition('sonata.notification.backend.rabbitmq');
        }

        if (isset($config['backends']['doctrine'])) {

            $checkLevel = array(
                MessageInterface::STATE_DONE => $config['backends']['doctrine']['states']['done'],
                MessageInterface::STATE_ERROR => $config['backends']['doctrine']['states']['error'],
                MessageInterface::STATE_IN_PROGRESS => $config['backends']['doctrine']['states']['in_progress'],
                MessageInterface::STATE_OPEN => $config['backends']['doctrine']['states']['open'],
            );
            $pause = $config['backends']['doctrine']['pause'];
            $maxAge = $config['backends']['doctrine']['max_age'];
            $batchSize = $config['backends']['doctrine']['batch_size'];
            $container->setAlias('sonata.notification.manager.message', $config['backends']['doctrine']['message_manager']);

            $this->configureDoctrineBackends($container, $config, $checkLevel, $pause, $maxAge, $batchSize);
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param $config
     * @param $checkLevel
     * @param $pause
     * @param $maxAge
     * @param $batchSize
     * @throws \RuntimeException
     */
    protected function configureDoctrineBackends(ContainerBuilder $container, $config, $checkLevel, $pause, $maxAge, $batchSize)
    {
        $queues = $config['queues'];
        $qBackends = array();

        $definition = $container->getDefinition('sonata.notification.backend.doctrine');

        if (count($queues) == 0) {
            $defaultQueue = 'default';
            $id = $this->createQueueBackend($container, $definition->getArgument(0), $checkLevel, $pause, $maxAge, $batchSize, $defaultQueue);
            $qBackends[0] = array('type' => $defaultQueue, 'backend' => new Reference($id));
        } else {
            $defaultSet = false;
            foreach ($queues as $pos => $queue) {
                $id = $this->createQueueBackend($container, $definition->getArgument(0), $checkLevel, $pause, $maxAge, $batchSize, $queue['queue']);
                $qBackends[$pos] = array('type' => $queue['routing_key'], 'backend' =>  new Reference($id));
                if ($queue['default'] === true) {
                    if ($defaultSet === true) {
                        throw new \RuntimeException('You can only set one doctrine default queue in your sonata notification configuration.');
                    }
                    $defaultSet = true;
                    $defaultQueue = $queue['routing_key'];
                }
            }
            if ($defaultSet === false) {
                throw new \RuntimeException("You need to specify a valid default queue for the doctrine backend!");
            }
        }

        $id = $this->createQueueBackend($container, $definition->getArgument(0), $checkLevel, $pause, $maxAge, $batchSize);
        array_push($qBackends, array('type' => '', 'backend' => new Reference($id)));

        $definition
            ->replaceArgument(1, $queues)
            ->replaceArgument(2, $defaultQueue)
            ->replaceArgument(3, $qBackends)
        ;
    }

    /**
     * @param ContainerBuilder $container
     * @param $manager
     * @param $checkLevel
     * @param $pause
     * @param $maxAge
     * @param $batchSize
     * @param string $key
     * @return string
     */
    protected function createQueueBackend(ContainerBuilder $container, $manager, $checkLevel, $pause, $maxAge, $batchSize, $key = '')
    {
        if ($key === '') {
            $id = 'sonata.notification.backend.doctrine.default' . $this->amqpCounter++;
        } else {
            $id = 'sonata.notification.backend.doctrine.' . $key;
        }
        $definition = new Definition('Sonata\NotificationBundle\Backend\MessageManagerBackend', array($manager, $checkLevel, $pause, $maxAge, $batchSize));
        $definition->setPublic(false);
        if ($key !== '') {
            $definition->addArgument($key);
        }
        $container->setDefinition($id, $definition);

        return $id;
    }

    /**
     * @param ContainerBuilder $container
     * @param array            $config
     */
    protected function configureRabbitmq(ContainerBuilder $container, $config)
    {
        $queues = $config['queues'];
        $connection = $config['backends']['rabbitmq']['connection'];
        $exchange = $config['backends']['rabbitmq']['exchange'];
        $amqBackends = array();

        if (count($queues) == 0) {
            $defaultQueue = 'default';
            $id = $this->createAMQPBackend($container, $exchange, false, $defaultQueue);
            $amqBackends[0] = array('type' => $defaultQueue, 'backend' => new Reference($id));
        } else {
            $defaultSet = false;
            foreach ($queues as $pos => $queue) {
                $id = $this->createAMQPBackend($container, $exchange, $queue['queue'], $queue['recover'], $queue['routing_key'], $queue['dead_letter_exchange']);
                $amqBackends[$pos] = array('type' => $queue['routing_key'], 'backend' =>  new Reference($id));
                if ($queue['default'] === true) {
                    if ($defaultSet === true) {
                        throw new \RuntimeException('You can only set one rabbitmq default queue in your sonata notification configuration.');
                    }
                    $defaultSet = true;
                    $defaultQueue = $queue['routing_key'];
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
     * @param  ContainerBuilder $container
     * @param  string           $name
     * @param  string           $key
     * @param  string           $deadLetterExchange
     *
     * @return string
     */
    protected function createAMQPBackend(ContainerBuilder $container, $exchange, $name, $recover, $key = '', $deadLetterExchange = null)
    {
        if ($key === '') {
            $id = 'sonata.notification.backend.rabbitmq.default' . $this->amqpCounter++;
        } else {
            $id = 'sonata.notification.backend.rabbitmq.' . $key;
        }
        $definition = new Definition('Sonata\NotificationBundle\Backend\AMQPBackend', array($exchange, $name, $recover, $key, $deadLetterExchange));
        $definition->setPublic(false);
        $container->setDefinition($id, $definition);

        return $id;
    }

     /**
     * @param  array $config
     * @return void
     */
    public function registerDoctrineMapping(array $config)
    {
        $collector = DoctrineCollector::getInstance();

        $collector->addIndex($config['class']['message'], 'idx_state', array(
            'state',
        ));

        $collector->addIndex($config['class']['message'], 'idx_created_at', array(
            'created_at',
        ));
    }
}
