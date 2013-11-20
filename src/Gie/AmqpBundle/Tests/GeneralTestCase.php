<?php

namespace Gie\AmqpBundle\Tests;

use PHPUnit_Framework_TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Gie\AmqpBundle\DependencyInjection\GieAmqpExtension;

/*
 *  Copyright 2013 Grzegorz Koziński
 */

/**
 * GeneralTestCase
 *
 * @author Grzegorz Koziński <gkozinski@gmail.com>
 */
class GeneralTestCase extends PHPUnit_Framework_TestCase
{
    /**
     *
     * @var ContainerBuilder
     */
    protected $containerBuilder;

    /**
     *
     * @var GieAmqpExtension
     */
    protected $extension;


    public function setUp()
    {
        parent::setUp();
        $this->containerBuilder = new ContainerBuilder();
        $this->extension = new GieAmqpExtension();
        $this->containerBuilder->addObjectResource($this->extension);
        $config = $this->getConfigForTest();
        $this->extension->load([$config], $this->containerBuilder);
    }
    
    /**
     * 
     * @param string $serviceId
     * @return mixed
     */
    protected function getService($serviceId)
    {
        return $this->containerBuilder->get($serviceId);
    }
    
    /**
     * 
     * @return array
     */
    protected function getConfigForTest()
    {
        $config = [
            'connection' => [
                'default' => [
                    'host' => '127.0.0.1',
                    'login' => 'guest',
                    'password' => 'guest',
                    'vhost' => '/',
                    'channel' => [
                        'default' => [
                            'exchange' => [
                                'default' => [
                                    'durable' => true,
                                    'passive' => true,
                                    'type' => 'direct'
                                ]
                            ],
                            'queue' => [
                                'default' => [
                                    'routing_key' => 'test.default',
                                    'exchange' => ['default'],
                                    'durable' => true,
                                    'passive' => false,
                                    'exclusive' => false,
                                    'autodelete' => false,
                                    'publisher' => 'test.publisher.default',
                                    'consumer' => [
                                        'default' => [
                                            'count' => 50,
                                            'service' => 'test.consumer.default',
                                            'class' => 'Gie\AmqpBundle\Consumer\ConsumerExample',
                                        ],
                                    ]
                                ],
                            ]
                        ],
                    ]
                ],
            ]
        ];

        return $config;
    }
}
