<?php

namespace Pipe\Test\Util;

use Pipe\Util\ProcessorRegistry;

class ProcessorRegistryTest extends \PHPUnit_Framework_TestCase
{
    protected $registry;

    function setup()
    {
        $this->registry = new ProcessorRegistry;
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    function prependThrowsExceptionIfClassDoesNotExist()
    {
        $this->registry->prepend('text/css', '\foo');
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    function registerThrowsExceptionIfClassDoesNotExist()
    {
        $this->registry->register('text/css', '\foo');
    }

    /**
     * @test
     */
    function testRegister()
    {
        $this->registry->register('text/css', '\MetaTemplate\Template\VoidTemplate');

        $this->assertTrue($this->registry->isRegistered('text/css', '\MetaTemplate\Template\VoidTemplate'));

        $this->registry->unregister('text/css', '\MetaTemplate\Template\VoidTemplate');

        $this->assertFalse($this->registry->isRegistered('text/css', '\MetaTemplate\Template\VoidTemplate'));
    }

    /**
     * @test
     */
    function registerAppendsToMimeType()
    {
        $this->registry->register('text/css', '\MetaTemplate\Template\VoidTemplate');
        $this->registry->register('text/css', '\MetaTemplate\Template\SassTemplate');

        $processors = $this->registry->all('text/css');

        $this->assertEquals('\MetaTemplate\Template\VoidTemplate', $processors[0]);
        $this->assertEquals('\MetaTemplate\Template\SassTemplate', $processors[1]);
    }

    /**
     * @test
     */
    function prependPrependsToMimeType()
    {
        $this->registry->register('text/css', '\MetaTemplate\Template\VoidTemplate');
        $this->registry->prepend('text/css', '\MetaTemplate\Template\SassTemplate');

        $processors = $this->registry->all('text/css');

        $this->assertEquals('\MetaTemplate\Template\SassTemplate', $processors[0]);
        $this->assertEquals('\MetaTemplate\Template\VoidTemplate', $processors[1]);
    }
}
