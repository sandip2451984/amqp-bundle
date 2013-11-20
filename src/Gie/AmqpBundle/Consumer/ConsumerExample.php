<?php

namespace Gie\AmqpBundle\Consumer;

use Gie\AmqpBundle\ConsumerTrait;
use Gie\AmqpBundle\ConsumerInterface;
use AMQPEnvelope;

/*
 *  Copyright 2013 Grzegorz Koziński
 */

/**
 * Consumer example
 *
 * @author Grzeogorz Koziński <gkozinski@gmail.com>
 */
class ConsumerExample implements ConsumerInterface
{
    use ConsumerTrait;

    /**
     *
     * @var string
     */
    private $body;

    /**
     *
     * @param AMQPEnvelope $message
     * @return boolean
     */
    public function run(AMQPEnvelope $message)
    {
        $this->body = $message->getBody();
        $this->queue->ack($message->getDeliveryTag());
        $this->checkCount();

        // to exit from consumption loop
        return false;
    }

    /**
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }
}
