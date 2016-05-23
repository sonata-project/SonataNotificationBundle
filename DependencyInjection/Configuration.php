<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\NotificationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
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

        $backendInfo = <<<'EOF'
Other backends you can use:

sonata.notification.backend.postpone
sonata.notification.backend.doctrine
sonata.notification.backend.rabbitmq
EOF;

        $iterationListenersInfo = <<<EOF
Listeners attached to the IterateEvent
Iterate event is thrown on each command iteration

Iteration listener class must implement Sonata\NotificationBundle\Event\IterationListener
EOF;

        $rootNode
            ->scalarNode('backend')
                ->info($backendInfo)
                ->defaultValue('sonata.notification.backend.runtime')
            ->end()
            ->append($this->getQueueNode())
            ->arrayNode('backends')
                ->children()
                    ->arrayNode('doctrine')
                        ->children()
                            ->scalarNode('message_manager')
                                ->defaultValue('sonata.notification.manager.message.default')
                            ->end()
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
                                ->info('Raising errors level')
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->integerNode('in_progress')
                                        ->defaultValue(10)
                                    ->end()
                                    ->integerNode('error')
                                        ->defaultValue(20)
                                    ->end()
                                    ->integerNode('open')
                                        ->defaultValue(100)
                                    ->end()
                                    ->integerNode('done')
                                        ->defaultValue(10000)
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('rabbitmq')
                        ->children()
                            ->scalarNode('exchange')
                                ->cannotBeEmpty()
                                ->isRequired()
                            ->end()
                            ->arrayNode('connection')
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->scalarNode('host')
                                        ->defaultValue('localhost')
                                    ->end()
                                    ->scalarNode('port')
                                        ->defaultValue(5672)
                                    ->end()
                                    ->scalarNode('user')
                                        ->defaultValue('guest')
                                    ->end()
                                    ->scalarNode('pass')
                                        ->defaultValue('guest')
                                    ->end()
                                    ->scalarNode('vhost')
                                        ->defaultValue('guest')
                                    ->end()
                                    ->scalarNode('console_url')
                                        ->defaultValue('http://localhost:55672/api')
                                    ->end()
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
                ->prototype('scalar')->end()
            ->end()
            ->arrayNode('class')
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('message')
                        ->defaultValue('Application\\Sonata\\NotificationBundle\\Entity\\Message')
                    ->end()
                ->end()
            ->end()
            ->arrayNode('admin')
                ->addDefaultsIfNotSet()
                ->children()
                    ->booleanNode('enabled')
                        ->defaultTrue()
                    ->end()
                    ->arrayNode('message')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('class')
                                ->cannotBeEmpty()
                                ->defaultValue('Sonata\\NotificationBundle\\Admin\\MessageAdmin')
                            ->end()
                            ->scalarNode('controller')
                                ->cannotBeEmpty()
                                ->defaultValue('SonataNotificationBundle:MessageAdmin')
                            ->end()
                            ->scalarNode('translation')
                                ->cannotBeEmpty()
                                ->defaultValue('SonataNotificationBundle')
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }

    /**
     * @return ArrayNodeDefinition|NodeDefinition
     */
    protected function getQueueNode()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('queues');

        $queuesInfo = <<<'EOF'
Example for using RabbitMQ
    - { queue: myQueue, recover: true, default: false, routing_key: the_routing_key, dead_letter_exchange: 'my.dead.letter.exchange' }
    - { queue: catchall, default: true }

Example for using Doctrine
    - { queue: sonata_page, types: [sonata.page.create_snapshot, sonata.page.create_snapshots] }
    - { queue: catchall, default: true }
EOF;

        $routingKeyInfo = <<<'EOF'
Only used by RabbitMQ

Direct exchange with routing_key
EOF;

        $recoverInfo = <<<'EOF'
Only used by RabbitMQ

If set to true, the consumer will respond with a `basic.recover` when an exception occurs,
otherwise it will not respond at all and the message will be unacknowledged
EOF;

        $deadLetterExchangeInfo = <<<'EOF'
Only used by RabbitMQ

If is set, failed messages will be rejected and sent to this exchange
EOF;

        $typesInfo = <<<'EOF'
Only used by Doctrine

Defines types handled by the message backend
EOF;

        $connectionNode = $node
            ->info($queuesInfo)
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
                    ->info($routingKeyInfo)
                    ->defaultValue('')
                ->end()
                ->booleanNode('recover')
                    ->info($recoverInfo)
                    ->defaultValue(false)
                ->end()
                ->scalarNode('dead_letter_exchange')
                    ->info($deadLetterExchangeInfo)
                    ->defaultValue(null)
                ->end()

                // Database configuration (Doctrine)
                ->arrayNode('types')
                    ->info($typesInfo)
                    ->defaultValue(array())
                    ->prototype('scalar')->end()
                ->end()
            ->end();

        return $node;
    }
}
