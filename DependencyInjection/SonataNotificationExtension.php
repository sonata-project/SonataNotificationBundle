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

        if ($config['consumers']['register_default']) {
            $loader->load('default_consumers.xml');
        }

        $bundles = $container->getParameter('kernel.bundles');

        if (isset($bundles['FOSRestBundle']) && isset($bundles['NelmioApiDocBundle'])) {
            $loader->load('api_controllers.xml');
        }

        if ($config['admin']['enabled'] && isset($bundles['SonataDoctrineORMAdminBundle'])) { // for now, only support for ORM
            $loader->load('admin.xml');
        }

        if (isset($bundles['LiipMonitorBundle'])) {
            $loader->load('checkmonitor.xml');
        }

        $this->checkConfiguration($config);

        $container->setAlias('sonata.notification.backend', $config['backend']);
        $container->setParameter('sonata.notification.backend', $config['backend']);

        $this->registerDoctrineMapping($config);
        $this->registerParameters($container, $config);
        $this->configureBackends($container, $config);
        $this->configureClass($container, $config);
        $this->configureListeners($container, $config);
        $this->configureAdmin($container, $config);
    }

    /**
     * @param array $config
     */
    protected function checkConfiguration(array $config)
    {
        if (isset($config['backends']) && count($config['backends']) > 1) {
            throw new \RuntimeException('more than one backend configured, you can have only one backend configuration');
        }

        if (!isset($config['backends']['rabbitmq']) && $config['backend']  === 'sonata.notification.backend.rabbitmq') {
            throw new \RuntimeException('Please configure the sonata_notification.backends.rabbitmq section');
        }

        if (!isset($config['backends']['doctrine']) && $config['backend']  === 'sonata.notification.backend.doctrine') {
            throw new \RuntimeException('Please configure the sonata_notification.backends.doctrine section');
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param array            $config
     */
    protected function configureListeners(ContainerBuilder $container, array $config)
    {
        $ids = $config['iteration_listeners'];

        // this one clean the unit of work after every iteration
        // it must be set on any backend ...
        $ids[] = 'sonata.notification.event.doctrine_optimize';

        if (isset($config['backends']['doctrine']) && $config['backends']['doctrine']['batch_size'] > 1) {
            // if the backend is doctrine and the batch size > 1, then
            // the unit of work must be cleaned wisely to avoid any issue
            // while persisting entities
            $ids = array(
                'sonata.notification.event.doctrine_backend_optimize'
            );
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
        $container->setParameter('sonata.notification.message.class',        $config['class']['message']);
        $container->setParameter('sonata.notification.admin.message.entity', $config['class']['message']);
    }

    /**
      * @param ContainerBuilder $container
      * @param array            $config
      */
    public function configureBackends(ContainerBuilder $container, $config)
    {
        // set the default value, will be erase if required
        $container->setAlias('sonata.notification.manager.message', 'sonata.notification.manager.message.default');

        if (isset($config['backends']['rabbitmq']) && $config['backend']  === 'sonata.notification.backend.rabbitmq') {
            $this->configureRabbitmq($container, $config);

            $container->removeDefinition('sonata.notification.backend.doctrine');
        } else {
            $container->removeDefinition('sonata.notification.backend.rabbitmq');
        }

        if (isset($config['backends']['doctrine']) && $config['backend']  === 'sonata.notification.backend.doctrine') {
            $checkLevel = array(
                MessageInterface::STATE_DONE         => $config['backends']['doctrine']['states']['done'],
                MessageInterface::STATE_ERROR        => $config['backends']['doctrine']['states']['error'],
                MessageInterface::STATE_IN_PROGRESS  => $config['backends']['doctrine']['states']['in_progress'],
                MessageInterface::STATE_OPEN         => $config['backends']['doctrine']['states']['open'],
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
     * @param array            $config
     * @param boolean          $checkLevel
     * @param integer          $pause
     * @param integer          $maxAge
     * @param integer          $batchSize
     *
     * @throws \RuntimeException
     */
    protected function configureDoctrineBackends(ContainerBuilder $container, array $config, $checkLevel, $pause, $maxAge, $batchSize)
    {
        $queues = $config['queues'];
        $qBackends = array();

        $definition = $container->getDefinition('sonata.notification.backend.doctrine');

        // no queue defined, set a default one
        if (count($queues) == 0) {
            $queues = array(array(
                'queue'   => 'default',
                'default' => true,
                'types'   => array()
            ));
        }

        $defaultSet = false;
        $declaredQueues = array();

        foreach ($queues as $pos => &$queue) {
            if (in_array($queue['queue'], $declaredQueues)) {
                throw new \RuntimeException('The doctrine backend does not support 2 identicals queue name, please rename one queue');
            }

            $declaredQueues[] = $queue['queue'];

            // make the configuration compatible with old code and rabbitmq
            if (isset($queue['routing_key']) && strlen($queue['routing_key']) > 0) {
                $queue['types'] = array($queue['routing_key']);
            }

            if (empty($queue['types']) && $queue['default'] === false) {
                throw new \RuntimeException('You cannot declared a doctrine queue with no type defined with default = false');
            }

            if (!empty($queue['types']) && $queue['default'] === true) {
                throw new \RuntimeException('You cannot declared a doctrine queue with types defined with default = true');
            }

            $id = $this->createDoctrineQueueBackend($container, $definition->getArgument(0), $checkLevel, $pause, $maxAge, $batchSize, $queue['queue'], $queue['types']);
            $qBackends[$pos] = array(
                'types'   => $queue['types'],
                'backend' => new Reference($id)
            );

            if ($queue['default'] === true) {
                if ($defaultSet === true) {
                    throw new \RuntimeException('You can only set one doctrine default queue in your sonata notification configuration.');
                }

                $defaultSet = true;
                $defaultQueue = $queue['queue'];
            }
        }

        if ($defaultSet === false) {
            throw new \RuntimeException("You need to specify a valid default queue for the doctrine backend!");
        }

        $definition
            ->replaceArgument(1, $queues)
            ->replaceArgument(2, $defaultQueue)
            ->replaceArgument(3, $qBackends)
        ;
    }

    /**
     * @param ContainerBuilder $container
     * @param string           $manager
     * @param boolean          $checkLevel
     * @param integer          $pause
     * @param integer          $maxAge
     * @param integer          $batchSize
     * @param string           $key
     * @param array            $types
     *
     * @return string
     */
    protected function createDoctrineQueueBackend(ContainerBuilder $container, $manager, $checkLevel, $pause, $maxAge, $batchSize, $key, array $types = array())
    {
        if ($key == '') {
            $id = 'sonata.notification.backend.doctrine.default_' . $this->amqpCounter++;
        } else {
            $id = 'sonata.notification.backend.doctrine.' . $key;
        }

        $definition = new Definition('Sonata\NotificationBundle\Backend\MessageManagerBackend', array($manager, $checkLevel, $pause, $maxAge, $batchSize, $types));
        $definition->setPublic(false);

        $container->setDefinition($id, $definition);

        return $id;
    }

    /**
     * @param ContainerBuilder $container
     * @param array            $config
     */
    protected function configureRabbitmq(ContainerBuilder $container, array $config)
    {
        $queues = $config['queues'];
        $connection = $config['backends']['rabbitmq']['connection'];
        $exchange = $config['backends']['rabbitmq']['exchange'];
        $amqBackends = array();

        if (count($queues) == 0) {
            $queues = array(array(
                'queue'       => 'default',
                'default'     => true,
                'routing_key' => '',
                'recover'     => false,
                'dead_letter_exchange' => null,
            ));
        }

        $declaredQueues = array();

        $defaultSet = false;
        foreach ($queues as $pos => $queue) {
            if (in_array($queue['queue'], $declaredQueues)) {
                throw new \RuntimeException('The RabbitMQ backend does not support 2 identicals queue name, please rename one queue');
            }

            $declaredQueues[] = $queue['queue'];

            $id = $this->createAMQPBackend($container, $exchange, $queue['queue'], $queue['recover'], $queue['routing_key'], $queue['dead_letter_exchange']);

            $amqBackends[$pos] = array(
                'type' => $queue['routing_key'],
                'backend' =>  new Reference($id)
            );

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

        $container->getDefinition('sonata.notification.backend.rabbitmq')
            ->replaceArgument(0, $connection)
            ->replaceArgument(1, $queues)
            ->replaceArgument(2, $defaultQueue)
            ->replaceArgument(3, $amqBackends)
        ;
    }

    /**
     * @param  ContainerBuilder $container
     * @param  string           $exchange
     * @param  string           $name
     * @param  string           $recover
     * @param  string           $key
     * @param  string           $deadLetterExchange
     *
     * @return string
     */
    protected function createAMQPBackend(ContainerBuilder $container, $exchange, $name, $recover, $key = '', $deadLetterExchange = null)
    {
        $id = 'sonata.notification.backend.rabbitmq.' . $this->amqpCounter++;

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
