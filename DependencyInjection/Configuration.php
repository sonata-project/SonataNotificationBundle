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
            ->scalarNode('default_queue')->cannotBeEmpty()->isRequired()->end()
            ->arrayNode('backends')
                ->addDefaultsIfNotSet()
                ->children()
                    ->arrayNode('doctrine')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('max_age')->defaultValue(86400)->end() # max age in second
                            ->scalarNode('pause')->defaultValue(500000)->end()  # delay in microseconds
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
                        ->addDefaultsIfNotSet()
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
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('consumers')
                ->addDefaultsIfNotSet()
            ->end()
            ->arrayNode('class')
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('message')->defaultValue('Sonata\\NotificationBundle\\Entity\\Message')->end()
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

        $connectionNode->children()
            ->scalarNode('queue')->cannotBeEmpty()->isRequired()->end()
            ->scalarNode('routing_key')->cannotBeEmpty()->isRequired()->end()
        ->end();

        return $node;

    }
}
