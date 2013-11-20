<?php

namespace Gie\AmqpBundle\DependencyInjection\Factory;

use AMQPConnection;
use AMQPChannel;

/*
 *  Copyright 2013 Grzegorz Koziński
 */

/**
 * Factory for create AMQP channel service.
 *
 * @author Grzegorz Koziński <gkozinski@gmail.com>
 */
class ChannelFactory
{
    public static function get(AMQPConnection $connection)
    {
        return new AMQPChannel($connection);
    }
}
