<?php

namespace Gie\AmqpBundle\Tests\Command;

use Gie\AmqpBundle\Tests\ContainerTestCase;
use Gie\AmqpBundle\Command\ConsumerCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/*
 *  Copyright 2013 Grzegorz Koziński
 */

/**
 * ConsumerCommandTest
 *
 * @author Grzegorz Koziński <gkozinski@gmail.com>
 */
class ConsumerCommandTest extends ContainerTestCase
{
    public function testCommandExist()
    {
        $application = new Application($this->kernel);
        $application->add(new ConsumerCommand());

        $command = $application->find('amqp:consumer:run');

        $this->assertEquals(true, $command instanceof ConsumerCommand);
    }

    public function testExecute()
    {
        $publisher = $this->container->get('test.publisher.example.default');
        $publisher->publish('publish for test consume command');

        $application = new Application($this->kernel);
        $application->add(new ConsumerCommand());

        $command = $application->find('amqp:consumer:run');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'service' => 'test.consumer.example'
        ]);

        $this->assertRegExp('/Start consume\.\.\./', $commandTester->getDisplay());
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Not enough arguments.
     */
    public function testNoServiceArgumentException()
    {
        $application = new Application($this->kernel);
        $application->add(new ConsumerCommand());

        $command = $application->find('amqp:consumer:run');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
        ]);
    }

    /**
     * @expectedException Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @expectedExceptionMessage You have requested a non-existent service "not.existed.service".
     */
    public function testServiceNotExistException()
    {
        $application = new Application($this->kernel);
        $application->add(new ConsumerCommand());

        $command = $application->find('amqp:consumer:run');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'service' => 'not.existed.service'
        ]);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Service <info>doctrine</info> must be instance of <info>Gie\AmqpBundle\Consumer\ConsumerInterface</info>
     */
    public function testWrongServiceException()
    {
        $application = new Application($this->kernel);
        $application->add(new ConsumerCommand());

        $command = $application->find('amqp:consumer:run');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'service' => 'doctrine'
        ]);
    }
}
