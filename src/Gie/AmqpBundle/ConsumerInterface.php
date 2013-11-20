<?php

namespace Gie\AmqpBundle;

use AMQPEnvelope;

/*
 *  Copyright 2013 Grzegorz Koziński
 */

/**
 * Consumer
 *
 * @author Grzeogorz Koziński <gkozinski@gmail.com>
 */
interface ConsumerInterface
{
    public function run(AMQPEnvelope $message);
    public function consume();
}
