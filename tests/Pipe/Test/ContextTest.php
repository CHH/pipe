<?php

namespace Pipe\Test;

use Pipe\Environment;
use Pipe\Context;

class ContextTest extends \PHPUnit_Framework_TestCase
{
    protected $ctx;

    function setup()
    {
        $env = new Environment;
        $env->appendPath(__DIR__ . '/fixtures');

        $this->ctx = new Context($env);
        $this->ctx->path = __DIR__ . '/fixtures';
    }

    function testDependOn()
    {
        $this->ctx->dependOn('asset1.js');

        $this->assertContains(__DIR__ . '/fixtures/asset1.js', $this->ctx->dependencyPaths);
    }

    function testEvaluateWithNoProcessors()
    {
        $data = $this->ctx->evaluate($this->ctx->resolve('asset1.js'), array(
            'processors' => null
        ));

        $this->assertEmpty($this->ctx->dependencyPaths);
        $this->assertEmpty($this->ctx->dependencyAssets);

        $this->assertEquals(file_get_contents(__DIR__ . '/fixtures/asset1.js'), $data);
    }

    /**
     * @expectedException \UnexpectedValueException
     */
    function testEvaluateThrowsExceptionWhenFileNotExists()
    {
        $this->ctx->evaluate('foo');
    }

    function testIgnoresFileCheckIfDataGiven()
    {
        $data = $this->ctx->evaluate('foo', array('data' => 'foo'));
        $this->assertEquals("foo", $data);
    }

    /**
     * @expectedException \Exception
     */
    function testContentTypeThrowsExceptionWhenPathNotFound()
    {
        $this->ctx->contentType('foo');
    }

    function testContentType()
    {
        $this->assertEquals(
            'application/javascript',
            $this->ctx->contentType('asset1.js')
        );
    }
}

