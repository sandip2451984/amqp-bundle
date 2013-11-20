<?php

namespace Gie\AmqpBundle;

use AMQPQueue;

/*
 *  Copyright 2013 Grzegorz Koziński
 */

/**
 * ConsumerTrait
 *
 * @author Grzeogorz Koziński <gkozinski@gmail.com>
 */
trait ConsumerTrait
{
    /**
     *
     * @var \AMQPQueue
     */
    protected $queue;

    /**
     *
     * @var integer
     */
    protected $count;

    /**
     *
     * @var integer
     */
    private $maxMessages;

    /**
     *
     * @param AMQPQueue $queue
     * @param integer $maxMessages
     */
    public function __construct(AMQPQueue $queue, $maxMessages)
    {
        $this->queue = $queue;
        $this->maxMessages = $maxMessages;
        $this->count = 0;
    }

    public function consume()
    {
        $this->queue->consume([$this, 'run']);
    }

    private function checkCount()
    {
        $this->count++;

        if ($this->count >= $this->maxMessages) {
            $this->closeProcess();
        }
    }

    private function closeProcess()
    {
        exit();
    }
}
