<?php

namespace Gie\AmqpBundle\DependencyInjection\Factory;

use Gie\AmqpBundle\Publisher;
use AMQPExchange;
use AMQPQueue;

/*
 *  Copyright 2013 Grzegorz Koziński
 */

/**
 * Factory for create publisher service.
 *
 * @author Grzegorz Koziński <gkozinski@gmail.com>
 */
class PublisherFactory
{
    public static function get(AMQPExchange $exchange, $routingKey, AMQPQueue $queue)
    {
        return new Publisher($exchange, $routingKey);
    }
}
