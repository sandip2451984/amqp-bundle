<?php

namespace Gie\AmqpBundle\Tests\Consumer;

use Gie\AmqpBundle\Tests\GeneralTestCase;
use Gie\AmqpBundle\Consumer\ConsumerExample;

/*
 *  Copyright 2013 Grzegorz Koziński
 */

/**
 * ConsumerExampleTest
 *
 * @author Grzegorz Koziński <gkozinski@gmail.com>
 */
class ConsumerExampleTest extends GeneralTestCase
{
    const CHANNEL_ID = 'gie_amqp.connection.default.channel.default';
    
    public function tearDown()
    {
        $queue = $this->getService(self::CHANNEL_ID . '.queue.' . 'test_example');
        $queue->delete();

        parent::tearDown();
    }

    public function testConsumerService()
    {
        $consumer = $this->getConsumer();
        $this->assertTrue($consumer instanceof ConsumerExample, 'Instance of ConsumerExample');
    }

    public function testConsumerReceive()
    {
        $publisher = $this->getPublisher();
        for ($iter = 1; $iter <= 3; $iter++) {
            $publisher->publish('message' . $iter);
        }

        $consumer = $this->getConsumer();

        for ($iter = 3; $iter <= 1; $iter--) {
            $consumer->consume();
            $this->assertEquals('message' . $iter, $consumer->getBody());
        }
    }
    
    /**
     * {@inheritDoc}
     */
    protected function getConfigForTest()
    {
        $addToConfig = [
            'connection' => [
                'default' => [
                    'channel' => [
                        'default' => [
                            'queue' => [
                                'test_example' => [
                                    'routing_key' => 'test.example',
                                    'exchange' => ['default'],
                                    'publisher' => 'test.publisher.example',
                                    'consumer' => [
                                        'default' => [
                                            'service' => 'test.consumer.example',
                                            'class' => 'Gie\AmqpBundle\Consumer\ConsumerExample',
                                        ]
                                    ]
                                ],                                
                            ],
                        ],
                    ],
                ],
            ],
        ];
        
        return array_merge_recursive(parent::getConfigForTest(), $addToConfig);
    }

    private function getConsumer()
    {
        return $this->getService('test.consumer.example');
    }

    private function getPublisher()
    {
        return $this->getService('test.publisher.example.default');
    }
}
