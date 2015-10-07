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

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('sonata_notification')->children();

        $iterationListenersInfo = <<<EOF
            Listeners attached to the IterateEvent
            Iterate event is thrown on each command iteration

            Iteration listener class must implement Sonata\NotificationBundle\Event\IterationListener
EOF;

        $rootNode
            ->scalarNode('backend')->defaultValue('sonata.notification.backend.runtime')->end()
            ->append($this->getQueueNode())
            ->arrayNode('backends')
                ->children()
                    ->arrayNode('doctrine')
                        ->children()
                            ->scalarNode('message_manager')->defaultValue('sonata.notification.manager.message.default')->end()
                            ->scalarNode('max_age')
                                ->info('The max age in seconds')
                                ->defaultValue(86400)
                            ->end()
                            ->scalarNode('pause')
                                ->info('The delay in microseconds')
                                ->defaultValue(500000)
                            ->end()
                            ->scalarNode('batch_size')
                                ->info('The number of items on each iteration')
                                ->defaultValue(10)
                            ->end()
                            ->arrayNode('states')
                                ->info('raising errors level')
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->scalarNode('in_progress')->defaultValue('10')->end()
                                    ->scalarNode('error')->defaultValue('20')->end()
                                    ->scalarNode('open')->defaultValue('100')->end()
                                    ->scalarNode('done')->defaultValue('10000')->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('rabbitmq')
                        ->children()
                            ->scalarNode('exchange')->cannotBeEmpty()->isRequired()->end()
                            ->arrayNode('connection')
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->scalarNode('host')->defaultValue('localhost')->end()
                                    ->scalarNode('port')->defaultValue(5672)->end()
                                    ->scalarNode('user')->defaultValue('guest')->end()
                                    ->scalarNode('pass')->defaultValue('guest')->end()
                                    ->scalarNode('vhost')->defaultValue('guest')->end()
                                    ->scalarNode('console_url')->defaultValue('http://localhost:55672/api')->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('consumers')
                ->addDefaultsIfNotSet()
                ->children()
                    ->booleanNode('register_default')
                        ->info('If set to true, SwiftMailerConsumer and LoggerConsumer will be registered as services')
                        ->defaultTrue()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('iteration_listeners')
                ->info($iterationListenersInfo)
                ->defaultValue(array())
                ->prototype('scalar')
                ->end()
            ->end()
            ->arrayNode('class')
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('message')->defaultValue('Application\\Sonata\\NotificationBundle\\Entity\\Message')->end()
                ->end()
            ->end()
            ->arrayNode('admin')
                ->addDefaultsIfNotSet()
                ->children()
                    ->booleanNode('enabled')->defaultTrue()->end()
                    ->arrayNode('message')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('class')->cannotBeEmpty()->defaultValue('Sonata\\NotificationBundle\\Admin\\MessageAdmin')->end()
                            ->scalarNode('controller')->cannotBeEmpty()->defaultValue('SonataNotificationBundle:MessageAdmin')->end()
                            ->scalarNode('translation')->cannotBeEmpty()->defaultValue('SonataNotificationBundle')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }

    protected function getQueueNode()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('queues');

        $connectionNode = $node
            ->requiresAtLeastOneElement()
            ->prototype('array')
        ;

        $connectionNode
            ->children()
                ->scalarNode('queue')
                    ->info('The name of the queue')
                    ->cannotBeEmpty()
                    ->isRequired()
                ->end()
                ->booleanNode('default')
                    ->info('Set the name of the default queue')
                    ->defaultValue(false)
                ->end()

                // RabbitMQ configuration
                ->scalarNode('routing_key')
                    ->info('only used by rabbitmq, direct exchange with routing_key')
                    ->defaultValue('')
                ->end()
                ->booleanNode('recover')
                    ->info('only used by rabbitmq')
                    ->defaultValue(false)
                ->end()
                ->scalarNode('dead_letter_exchange')
                    ->info('only used by rabbitmq')
                    ->defaultValue(null)
                ->end()

                // Database configuration (Doctrine)
                ->arrayNode('types')
                    ->info('defines types handled by the message backend')
                    ->defaultValue(array())
                    ->prototype('scalar')->end()
                ->end()
            ->end();

        return $node;
    }
}
