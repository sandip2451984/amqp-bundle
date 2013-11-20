<?php

namespace Gie\AmqpBundle\DependencyInjection\Factory;

use AMQPChannel;
use AMQPQueue;

/*
 *  Copyright 2013 Grzegorz Koziński
 */

/**
 * Factory for create AMQP queue service.
 *
 * @author Grzegorz Koziński <gkozinski@gmail.com>
 */
class QueueFactory
{
    public static function get(AMQPChannel $channel, $settings, $exchanges)
    {
        $queue = new AMQPQueue($channel);

        $queue->setName($settings['name']);

        $durableFlag = $settings['durable'] ? AMQP_DURABLE : 0;
        $passiveFlag = $settings['passive'] ? AMQP_PASSIVE : 0;
        $exclusiveFlag = $settings['exclusive'] ? AMQP_EXCLUSIVE : 0;
        $autodeleteFlag = $settings['autodelete'] ? AMQP_AUTODELETE : 0;
        $queue->setFlags($durableFlag | $passiveFlag | $exclusiveFlag | $autodeleteFlag);

        $queue->declare();

        foreach ($settings['exchange'] as $exchangeName) {
            $queue->bind($exchangeName, $settings['routing_key']);
        }

        return $queue;
    }
}
