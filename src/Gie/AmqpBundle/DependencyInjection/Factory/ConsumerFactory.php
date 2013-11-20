<?php

namespace Gie\AmqpBundle\DependencyInjection\Factory;

use AMQPQueue;

/*
 *  Copyright 2013 Grzegorz Koziński
 */

/**
 * Factory for create consumer service.
 *
 * @author Grzegorz Koziński <gkozinski@gmail.com>
 */
class ConsumerFactory
{
    /**
     *
     * @param string $class
     * @param AMQPQueue $queue
     * @param integer $count
     * @param array $services
     * @return \Gie\AmqpBundle\DependencyInjection\Factory\class
     */
    public static function get($class, AMQPQueue $queue, $count, $services = [])
    {
        if (!empty($services)) {
            $consumer = new $class($queue, $count);
            foreach ($services as $setter=>$service) {
                $consumer->$setter($service);
            }

            return $consumer;
        }

        return new $class($queue, $count);
    }
}
