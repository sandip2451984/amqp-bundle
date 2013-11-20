<?php

namespace Gie\AmqpBundle\Tests;

/*
 *  Copyright 2013 Grzegorz Koziński
 */

/**
 * SingleConnectionTest
 *
 * @author Grzegorz Koziński <gkozinski@gmail.com>
 */
class SingleConnectionTest extends GeneralTestCase
{
    public function testAmISingle()
    {
        $conn1 = $this->getService('gie_amqp.connection.default');
        $conn2 = $this->getService('gie_amqp.connection.default');
        $conn3 = $this->getService('gie_amqp.connection.default');
        
        $this->assertTrue($conn1 === $conn2);
        $this->assertTrue($conn1 === $conn3);
    }
}
