<?php

namespace Gie\AmqpBundle\Tests;

use PHPUnit_Framework_TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpKernel\Kernel;

require_once(__DIR__ . "/../../../../../../../app/AppKernel.php");

/*
 *  Copyright 2013 Grzegorz Koziński
 */

/**
 * ContainerTestCase
 *
 * @author Grzegorz Koziński <gkozinski@gmail.com>
 */
class ContainerTestCase extends PHPUnit_Framework_TestCase
{
    /**
     *
     * @var Container
     */
    protected $container;

    /**
     *
     * @var Kernel
     */
    protected $kernel;


    public function setUp()
    {
        parent::setUp();
        $this->kernel = new \AppKernel("test", true);
        $this->kernel->boot();
        $this->container = $this->kernel->getContainer();
    }

    public function tearDown()
    {
        unset($this->container);
        parent::tearDown();
    }
}
