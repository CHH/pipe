<?php

namespace Pipe\Test\Util;

use Pipe\Util\Pathstack;

class PathstackTest extends \PHPUnit_Framework_TestCase
{
    protected $stack;

    function setUp()
    {
        $this->stack = new Pathstack;
    }

    function testPushPathArray()
    {
        $this->stack->push(array(__DIR__, dirname(__DIR__)));

        $this->assertEquals(
            array(__DIR__, dirname(__DIR__)),
            $this->stack->toArray()
        );
    }

    function testUnshiftPathArray()
    {
        $this->stack->unshift(array(__DIR__, dirname(__DIR__)));

        $this->assertEquals(
            array(dirname(__DIR__), __DIR__),
            $this->stack->toArray()
        );
    }
}
