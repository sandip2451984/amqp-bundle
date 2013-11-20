<?php

namespace Gie\AmqpBundle\Tests;

use Gie\AmqpBundle\Publisher;
use Gie\AmqpBundle\Tests\GeneralTestCase;

/*
 *  Copyright 2013 Grzegorz Koziński
 */

/**
 * PublisherTest
 *
 * @author Grzegorz Koziński <gkozinski@gmail.com>
 */
class PublisherTest extends GeneralTestCase
{
    const CHANNEL_ID = 'gie_amqp.connection.default.channel.default';
    
    public function tearDown()
    {
        $queuesToDelete = [
            'test_simple',
            'test_array',
            'test_object',
        ];
        
        foreach ($queuesToDelete as $queueName) {
            $queue = $this->getService(self::CHANNEL_ID . '.queue.' . $queueName);
            $queue->delete();
        }

        parent::tearDown();
    }

    public function testPublisherService()
    {
        $publisher = $this->getService('test.publisher.default.default');
        $this->assertTrue($publisher instanceof Publisher, 'Wrong publisher service');
    }

    public function testSimpleTextPublish()
    {
        $contentToSend = 'Test simple text';
        $receivedContent = $this->sendAndReceiveMessage($contentToSend, 'simple');

        $this->assertEquals($contentToSend, $receivedContent);
    }

    public function testArrayPublish()
    {
        $arrayToSend = $this->getSimpleArray();
        $expectedContent = igbinary_serialize($arrayToSend);
        $receivedContent = $this->sendAndReceiveMessage($arrayToSend, 'array');

        $this->assertEquals($expectedContent, $receivedContent);
    }

    public function testObjectPublish()
    {
        $objectToSend = $this->getSimpleObject();
        $expectedContent = igbinary_serialize($objectToSend);
        $receivedContent = $this->sendAndReceiveMessage($objectToSend, 'object');

        $this->assertEquals($expectedContent, $receivedContent);
    }

    private function getConsumer($type)
    {
        return $this->getService('test.consumer.' . $type);
    }

    private function getPublisher($type)
    {
        return $this->getService('test.publisher.' . $type . '.default');
    }

    private function getSimpleArray()
    {
        return [
            'test' => [
                0 => 'value0',
                1 => 'value1'
        ]];
    }

    private function getSimpleObject()
    {
        $simpleObject = new \stdClass;
        $simpleObject->property = 'test';
        return $simpleObject;
    }

    private function sendAndReceiveMessage($message, $type)
    {
        $publisher = $this->getPublisher($type);
        $publisher->publish($message);
        $consumer = $this->getConsumer($type);
        $consumer->consume();

        return $consumer->getBody();
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
                                'test_simple' => [
                                    'routing_key' => 'test.publisher.simple',
                                    'exchange' => ['default'],
                                    'publisher' => 'test.publisher.simple',
                                    'consumer' => [
                                        'default' => [
                                            'service' => 'test.consumer.simple',
                                            'class' => 'Gie\AmqpBundle\Consumer\ConsumerExample',
                                        ]
                                    ]
                                ],
                                'test_array' => [
                                    'routing_key' => 'test.publisher.array',
                                    'exchange' => ['default'],
                                    'publisher' => 'test.publisher.array',
                                    'consumer' => [
                                        'default' => [
                                            'service' => 'test.consumer.array',
                                            'class' => 'Gie\AmqpBundle\Consumer\ConsumerExample',
                                        ]
                                    ]
                                ],
                                'test_object' => [
                                    'routing_key' => 'test.publisher.object',
                                    'exchange' => ['default'],
                                    'publisher' => 'test.publisher.object',
                                    'consumer' => [
                                        'default' => [
                                            'service' => 'test.consumer.object',
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
}
