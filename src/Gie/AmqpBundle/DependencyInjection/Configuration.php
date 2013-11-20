<?php

namespace Gie\AmqpBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Form\Exception\InvalidConfigurationException;

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
        $rootNode = $treeBuilder->root('gie_amqp');

        $rootNode
            ->children()
                ->arrayNode('connection')
                    ->isRequired()
                    ->requiresAtLeastOneElement()
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('host')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                            ->scalarNode('login')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                            ->scalarNode('password')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                            ->scalarNode('vhost')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                            ->arrayNode('channel')
                                ->useAttributeAsKey('name')
                                ->prototype('array')
                                    ->append($this->addExchangeNode())
                                    ->append($this->addQueueNode())
                                ->end()
                            ->end()
                        ->end()
                        ->append($this->addIntegerNodeWithDefaultValue('port', 5672))
                        ->append($this->addIntegerNodeWithDefaultValue('timeout', 1))
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
    
    /**
     * Create node for integer parameters with default value
     *
     * @param  type                          $nodeName
     * @param  type                          $default
     * @return NodeDefinition
     * @throws InvalidConfigurationException
     */
    public function addIntegerNodeWithDefaultValue($nodeName, $default)
    {
        $builder = new TreeBuilder();
        $node = $builder->root($nodeName, 'scalar');

        $node
            ->defaultValue($default)
            ->validate()
                ->always()
                ->then(function($value) use ($nodeName) {
                    if (!is_int($value)) {
                        throw new InvalidConfigurationException(ucfirst($nodeName) . ' must be integer');
                    }
                    return $value;
                })
            ->end()
        ;

        return $node;
    }

    /**
     * Create exchange node
     *
     * @return NodeDefinition
     */
    public function addExchangeNode()
    {
        $exchangeType = [
            'direct'  => AMQP_EX_TYPE_DIRECT,
            'fanout'  => AMQP_EX_TYPE_FANOUT,
            'headers' => AMQP_EX_TYPE_HEADERS,
            'topic'   => AMQP_EX_TYPE_TOPIC
        ];

        $invalidTypeMsg = 'Invalid type item keys (allowed: ' .
                implode(', ', array_keys($exchangeType)) . ')';

        $builder = new TreeBuilder();
        $node = $builder->root('exchange');

        $node
            ->useAttributeAsKey('name')
            ->defaultValue(['default' => [
                'durable' => true,
                'passive' => false,
                'type' => 'direct'
            ]])
            ->prototype('array')
                ->children()
                    ->booleanNode('durable')
                        ->defaultTrue()
                    ->end()
                    ->booleanNode('passive')
                        ->defaultFalse()
                    ->end()
                    ->scalarNode('type')
                        ->defaultValue('direct')
                        ->beforeNormalization()
                            ->ifString()
                                ->then(function($value) use ($exchangeType) {
                                    return isset($exchangeType[$value]) ? $exchangeType[$value] : $value;
                                })
                        ->end()
                        ->validate()
                            ->ifNotInArray($exchangeType)
                                ->thenInvalid($invalidTypeMsg)
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }

    /**
     * Create queue node
     *
     * @return NodeDefinition
     */
    public function addQueueNode()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('queue');

        $node
            ->useAttributeAsKey('name')
            ->isRequired()
            ->prototype('array')
                ->children()
                    ->scalarNode('routing_key')
                        ->isRequired()
                        ->cannotBeEmpty()
                    ->end()
                    ->arrayNode('exchange')
                        ->defaultValue(['default'])
                        ->prototype('scalar')->end()
                    ->end()
                    ->booleanNode('durable')
                        ->defaultTrue()
                    ->end()
                    ->booleanNode('passive')
                        ->defaultFalse()
                    ->end()
                    ->booleanNode('exclusive')
                        ->defaultFalse()
                    ->end()
                    ->booleanNode('autodelete')
                        ->defaultFalse()
                    ->end()
                    ->scalarNode('publisher')
                        ->isRequired()
                        ->cannotBeEmpty()
                    ->end()
                ->end()
                ->append($this->addConsumerNode())
            ->end()
        ;

        return $node;
    }

    /**
     * Create consumer node
     *
     * @return NodeDefinition
     */
    public function addConsumerNode()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('consumer');

        $node
            ->useAttributeAsKey('name')
            ->prototype('array')
                ->append($this->addIntegerNodeWithDefaultValue('count', 50))
                ->children()
                    ->scalarNode('service')
                        ->isRequired()
                        ->cannotBeEmpty()
                    ->end()
                    ->scalarNode('class')
                        ->isRequired()
                        ->cannotBeEmpty()
                    ->end()
                    ->arrayNode('services')
                        ->useAttributeAsKey('name')
                        ->prototype('array')
                            ->children()
                                ->scalarNode('setter')
                                    ->isRequired()
                                    ->cannotBeEmpty()
                                ->end()
                                ->scalarNode('service')
                                    ->isRequired()
                                    ->cannotBeEmpty()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }
}
