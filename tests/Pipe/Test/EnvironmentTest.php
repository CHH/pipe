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

    function testFindSingleAsset()
    {
        $asset = $this->environment['asset1.js'];
        $this->assertInstanceOf('\\Pipe\\Asset', $asset);
    }

    function testFindBundledAsset()
    {
        $asset = $this->environment->find('asset1.js', array('bundled' => true));
        $this->assertInstanceOf('\\Pipe\\BundledAsset', $asset);
    }

    function testFindWithoutExtension()
    {
        $this->assertNotNull($this->environment->find('asset1'));
    }

    function testReturnsNullWhenAssetIsNotFound()
    {
        $this->assertNull($this->environment['foo/bar/baz']);
    }
}
