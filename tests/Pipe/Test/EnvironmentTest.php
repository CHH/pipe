<?php

namespace Pipe\Test;

use Pipe\Environment;

class EnvironmentTest extends \PHPUnit_Framework_TestCase
{
    protected $environment;

    function setUp()
    {
        $env = new Environment;
        $env->appendPath(__DIR__ . "/fixtures");

        $this->environment = $env;
    }

    function testReadSingleAsset()
    {
        $asset = $this->environment['asset1.js'];
        $this->assertInstanceOf('\\Pipe\\Asset', $asset);
    }

    function testReturnsNullWhenAssetIsNotFound()
    {
        $this->assertNull($this->environment['foo/bar/baz']);
    }
}
