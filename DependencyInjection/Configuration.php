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
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('sonata_notification')->children();

        $rootNode
            ->scalarNode('backend')->defaultValue('sonata.notification.backend.runtime')->end()
            ->append($this->getQueueNode())
            ->arrayNode('backends')
                ->children()
                    ->arrayNode('doctrine')
                        ->children()
                            ->scalarNode('message_manager')->defaultValue('sonata.notification.manager.message.default')->end()
                            ->scalarNode('max_age')->defaultValue(86400)->end() # max age in second
                            ->scalarNode('pause')->defaultValue(500000)->end()  # delay in microseconds
                            ->scalarNode('batch_size')->defaultValue(10)->end() # number of items on each iteration
                            ->arrayNode('states')
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
            ->end()
            ->arrayNode('iteration_listeners')
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
                ->scalarNode('queue')->cannotBeEmpty()->isRequired()->end() // queue name
                ->booleanNode('default')->defaultValue(false)->end()        // set the name of the default queue

                // RabbitMQ configuration
                ->scalarNode('routing_key')->defaultValue('')->end()        // only used by rabbitmq, direct exchange with routing_key
                ->booleanNode('recover')->defaultValue(false)->end()        // only used by rabbitmq
                ->scalarNode('dead_letter_exchange')->defaultValue(null)->end() // only used by rabbitmq

                // Database configuration (Doctrine)
                ->arrayNode('types')                                        // defines types handled by the message backend
                    ->defaultValue(array())
                    ->prototype('scalar')->end()
                ->end()
            ->end();

        return $node;
    }
}
