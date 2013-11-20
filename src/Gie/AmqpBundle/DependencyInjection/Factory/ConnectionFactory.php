<?php

namespace Gie\AmqpBundle\DependencyInjection\Factory;

use AMQPConnection;

/*
 *  Copyright 2013 Grzegorz Koziński
 */

/**
 * Factory for create AMQP connection service.
 *
 * @author Grzegorz Koziński <gkozinski@gmail.com>
 */
class ConnectionFactory
{
    /**
     * Cached connection
     * @var AMQPConnection[]
     */
    protected static $connections;

    /**
     * 
     * @param array $credentials
     * @param int $timeout
     * @return AMQPConnection
     */
    public static function get($credentials, $timeout)
    {
        $hash = md5(serialize($credentials). ' ' . $timeout);
        if (self::connectionExist($hash)) {
            return static::$connections[$hash];
        }
        
        static::$connections[$hash] = new AMQPConnection($credentials);
        static::$connections[$hash]->setTimeout($timeout);
        static::$connections[$hash]->connect();

        return static::$connections[$hash];
    }
    
    /**
     * 
     * @param string $hash
     * @return boolean
     */
    private static function connectionExist($hash)
    {
        if (!isset(static::$connections[$hash])) {
            return false;
        }
        
        if (!static::$connections[$hash] instanceof AMQPConnection) {
            return false;
        }
        
        return static::$connections[$hash]->isConnected();
    }
}
