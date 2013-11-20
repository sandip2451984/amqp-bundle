<?php

namespace Gie\AmqpBundle\Tests\DependencyInjection;

use Gie\AmqpBundle\DependencyInjection\Configuration;
use Gie\AmqpBundle\Tests\GeneralTestCase;
use Symfony\Component\Config\Definition\Processor;

/*
 *  Copyright 2013 Grzegorz Koziński
 */

/**
 * ConfigurationTest
 *
 * @author Grzegorz Koziński <gkozinski@gmail.com>
 */
class ConfigurationTest extends GeneralTestCase
{
    /**
     *
     * @var Processor
     */
    protected $processor;

    /**
     *
     * @var Configuration
     */
    protected $configuration;

    public function setUp()
    {
        parent::setUp();
        $this->processor = new Processor();
        $this->configuration = new Configuration();
    }

    public function testDefaultValues()
    {
        $configEmptyDefaults = $this->getConfigWithEmptyDefaults();
        $configWithDefaults = $this->getConfigWithDefaults();

        $this->assertEquals($configWithDefaults, $this->process($configEmptyDefaults));
    }

    /**
     * @expectedException Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The child node "host" at path "gie_amqp.connection.default" must be configured.
     * @group unittest
     */
    public function testNoHostException()
    {
        $config = $this->getConfigWithEmptyDefaults();
        unset($config['connection']['default']['host']);
        $this->process($config);
    }

    /**
     * @expectedException Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The child node "login" at path "gie_amqp.connection.default" must be configured.
     * @group unittest
     */
    public function testNoLoginException()
    {
        $config = $this->getConfigWithEmptyDefaults();
        unset($config['connection']['default']['login']);
        $this->process($config);
    }

    /**
     * @expectedException Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The child node "password" at path "gie_amqp.connection.default" must be configured.
     * @group unittest
     */
    public function testNoPasswordException()
    {
        $config = $this->getConfigWithEmptyDefaults();
        unset($config['connection']['default']['password']);
        $this->process($config);
    }

    /**
     * @expectedException Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The child node "vhost" at path "gie_amqp.connection.default" must be configured.
     * @group unittest
     */
    public function testNoVhostException()
    {
        $config = $this->getConfigWithEmptyDefaults();
        unset($config['connection']['default']['vhost']);
        $this->process($config);
    }

    /**
     * @expectedException Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The child node "routing_key" at path "gie_amqp.connection.default.channel.default.queue.default" must be configured.
     * @group unittest
     */
    public function testNoRoutingKeyException()
    {
        $config = $this->getConfigWithEmptyDefaults();
        unset($config['connection']['default']['channel']['default']['queue']['default']['routing_key']);
        $this->process($config);
    }

    /**
     * @expectedException Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The child node "publisher" at path "gie_amqp.connection.default.channel.default.queue.default" must be configured.
     * @group unittest
     */
    public function testNoPublisherException()
    {
        $config = $this->getConfigWithEmptyDefaults();
        unset($config['connection']['default']['channel']['default']['queue']['default']['publisher']);
        $this->process($config);
    }

    /**
     * @expectedException Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The child node "class" at path "gie_amqp.connection.default.channel.default.queue.default.consumer.default" must be configured.
     * @group unittest
     */
    public function testNoConsumerClassException()
    {
        $config = $this->getConfigWithEmptyDefaults();
        unset($config['connection']['default']['channel']['default']['queue']['default']['consumer']['default']['class']);
        $this->process($config);
    }

    /**
     * @expectedException Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage Port must be integer
     * @group unittest
     */
    public function testNoIntegerForPortException()
    {
        $config = $this->getConfigWithEmptyDefaults();
        $config['connection']['default']['port'] = 'test';
        $this->process($config);
    }

    /**
     * @expectedException Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage Timeout must be integer
     * @group unittest
     */
    public function testNoIntegerForTimeoutException()
    {
        $config = $this->getConfigWithEmptyDefaults();
        $config['connection']['default']['timeout'] = 'test';
        $this->process($config);
    }

    /**
     * @expectedException Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage Count must be integer
     * @group unittest
     */
    public function testNoIntegerForConsumerCountException()
    {
        $config = $this->getConfigWithEmptyDefaults();
        $config['connection']['default']['channel']['default']['queue']['default']['consumer']['default']['count'] = 'test';
        $this->process($config);
    }

    /**
     * @expectedException Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage Invalid type item keys (allowed: direct, fanout, headers, topic)
     * @group unittest
     */
    public function testWrongValueForExchangeTypeException()
    {
        $config = $this->getConfigWithEmptyDefaults();
        $config['connection']['default']['channel']['default']['exchange']['default']['type'] = 'test';
        $this->process($config);
    }

    private function getConfigWithEmptyDefaults()
    {
        $config = [
            'connection' => [
                'default' => [
                    'host' => 'localhost',
                    'login' => 'login',
                    'password' => 'password',
                    'vhost' => 'vhost',
                    'channel' => [
                        'default' => [
                            'queue' => [
                                'default' => [
                                    'routing_key' => 'routing_key',
                                    'publisher' => 'publisher_class',
                                    'consumer' => [
                                        'default' => [
                                            'service' => 'service_name',
                                            'class' => 'ClassName',
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        return $config;
    }

    private function getConfigWithDefaults()
    {
        $defauts = ['connection' => ['default' => [
            'channel' => [
                'default' => [
                    'exchange' => ['default' => [
                        'durable' => true,
                        'passive' => false,
                        'type' => 'direct',
                    ]],
                    'queue' => ['default' => [
                        'exchange' => ['default'],
                        'durable' => true,
                        'passive' => false,
                        'exclusive' => false,
                        'autodelete' => false,
                        'consumer' => ['default' => [
                            'count' => 50,
                            'services' => [],
                        ]]
                    ]],
                ]
            ],
            'port' => 5672,
            'timeout' => 1,
        ]]];

        return array_merge_recursive($this->getConfigWithEmptyDefaults(), $defauts);
    }

    private function process($config)
    {
        return $this->processor->processConfiguration($this->configuration, [$config]);
    }
}
