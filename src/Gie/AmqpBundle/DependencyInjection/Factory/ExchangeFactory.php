<?php

namespace Gie\AmqpBundle\DependencyInjection\Factory;

use AMQPChannel;
use AMQPExchange;

/*
 *  Copyright 2013 Grzegorz Koziński
 */

/**
 * Factory for create AMQP exchange service.
 *
 * @author Grzegorz Koziński <gkozinski@gmail.com>
 */
class ExchangeFactory
{
    public static function get(AMQPChannel $channel, $settings)
    {
        $exchange = new AMQPExchange($channel);

        $exchange->setType($settings['type']);
        $exchange->setName($settings['name']);

        $durableFlag = $settings['durable'] ? AMQP_DURABLE : 0;
        $passiveFlag = $settings['passive'] ? AMQP_PASSIVE : 0;
        $exchange->setFlags($durableFlag | $passiveFlag);

        $exchange->declare();

        return $exchange;
    }
}
