<?php

namespace Gie\AmqpBundle\Tests\DependencyInjection;

use Gie\AmqpBundle\Tests\GeneralTestCase;
use AMQPConnection;
use AMQPChannel;
use AMQPExchange;
use AMQPQueue;

/*
 *  Copyright 2013 Grzegorz Koziński
 */

/**
 * GieAmqpExtensionTest
 *
 * @author Grzegorz Koziński <gkozinski@gmail.com>
 */
class GieAmqpExtensionTest extends GeneralTestCase
{
    /**
     * Testing connection service load based on configuration in config_test.yml
     */
    public function testConnectionService()
    {
        $connection = $this->getService('gie_amqp.connection.default');
        $this->assertTrue($connection instanceof AMQPConnection, 'Instance of AMQPConnection');
    }

    /**
     * Testing channel service load based on configuration in config_test.yml
     */
    public function testChannelService()
    {
        $channel = $this->getService('gie_amqp.connection.default.channel.default');
        $this->assertTrue($channel instanceof AMQPChannel, 'Instance of AMQPChannel');
    }

    /**
     * Testing exchange service load based on configuration in config_test.yml
     */
    public function testExchangeService()
    {
        $exchange = $this->getService('gie_amqp.connection.default.channel.default.exchange.default');
        $this->assertTrue($exchange instanceof AMQPExchange, 'Instance of AMQPExchange');
    }

    /**
     * Testing queue service load based on configuration in config_test.yml
     */
    public function testQueueService()
    {
        $queue = $this->getService('gie_amqp.connection.default.channel.default.queue.default');
        $this->assertTrue($queue instanceof AMQPQueue, 'Instance of AMQPQueue');
    }

    public function testConfiguration()
    {
        $config = $this->getConfigForTest();
        $this->extension->load([$config], $this->containerBuilder);

        $channelServiceId = 'gie_amqp.connection.default.channel.default';

        $exchange = $this->containerBuilder->get($channelServiceId . '.exchange.default');
        $this->assertEquals('default', $exchange->getName());
        $this->assertEquals(AMQP_EX_TYPE_DIRECT, $exchange->getType());
        $this->assertEquals(AMQP_DURABLE | AMQP_PASSIVE, $exchange->getFlags());

        $queue = $this->containerBuilder->get($channelServiceId . '.queue.default');
        $this->assertEquals('default', $queue->getName());
        $this->assertEquals(AMQP_DURABLE, $queue->getFlags());
    }
}
