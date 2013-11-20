<?php

namespace Gie\AmqpBundle;

use AMQPExchange;

/*
 *  Copyright 2013 Grzegorz Koziński
 */

/**
 * Publisher
 *
 * @author Grzeogorz Koziński <gkozinski@gmail.com>
 */
class Publisher
{
    /**
     *
     * @var AMQPExchange 
     */
    protected $exchange;
    
    /**
     *
     * @var string 
     */
    protected $routingKey;

    /**
     * 
     * @param AMQPExchange $exchange
     * @param string $routingKey
     */
    public function __construct(AMQPExchange $exchange, $routingKey)
    {
        $this->exchange = $exchange;
        $this->routingKey = $routingKey;
    }

    /**
     * 
     * @param mixed $message
     * @param array $attributes
     * @return boolean
     */
    public function publish($message, $attributes = [])
    {       
        if (is_array($message) || is_object($message)) {
            $message = $this->serializeData($message);
            $attributes['content_type'] = 'application/json';
        }

        return $this->publishToExchange($message, $attributes);
    }

    /**
     * 
     * @param mixed $data
     * @return string
     */
    private function serializeData($data)
    {
        return igbinary_serialize($data);
    }

    /**
     * 
     * @param string $message
     * @param string $attributes
     * @return boolean
     */
    private function publishToExchange($message, $attributes)
    {
        return $this->exchange->publish(
                $message,
                $this->routingKey,
                AMQP_NOPARAM,
                $attributes
        );
    }
}
