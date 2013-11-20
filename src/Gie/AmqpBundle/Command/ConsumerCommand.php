<?php

namespace Gie\AmqpBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand as BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Gie\AmqpBundle\ConsumerInterface;

/*
 *  Copyright 2013 Grzegorz Koziński
 */

/**
 * ConsumerCommand
 *
 * @author Grzeogorz Koziński <gkozinski@gmail.com>
 */
class ConsumerCommand extends BaseCommand
{
    const COMMAND = "amqp:consumer:run";
    const ARG_SERVICE = "service";

    const MSG_MAIN_DESC = "Run AMQP consumer";
    const MSG_WRONG_CONSUMER = "Service <info>%s</info> must be instance of <info>%s</info>";
    const MSG_ON_START = "<info>Start consume...</info>";
    const MSG_SERVICE_ARG_DESC = "What is consumer service name?";


    protected function configure()
    {
        $this
            ->setName(self::COMMAND)
            ->setDescription(self::MSG_MAIN_DESC)
            ->addArgument(
                self::ARG_SERVICE,
                InputArgument::REQUIRED,
                self::MSG_SERVICE_ARG_DESC
            )
        ;
    }

    /**
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $service = $input->getArgument(self::ARG_SERVICE);
        $consumer = $this->getContainer()->get($service);
        $this->checkProperConsumer($consumer, $service);
        $output->writeln(self::MSG_ON_START);
        $consumer->consume();
    }

    /**
     *
     * @param ConsumerInterface $consumer
     * @param string $service
     * @throws \InvalidArgumentException
     */
    private function checkProperConsumer($consumer, $service)
    {
        if (!$consumer instanceof ConsumerInterface) {
            $msg = sprintf(
                self::MSG_WRONG_CONSUMER,
                $service,
                'Gie\AmqpBundle\Consumer\ConsumerInterface'
            );
            throw new \InvalidArgumentException($msg);
        }
    }
}
