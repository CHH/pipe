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

    /**
     * @expectedException \InvalidArgumentException
     */
    function testSetJsCompressorThrowsExceptionWhenCompressorNotExists()
    {
        $this->environment->setJsCompressor('foobarbaz');
    }

    function testSetJsCompressorRegistersBundleProcessor()
    {
        $this->environment->setJsCompressor('uglify_js');
        $bundleProcessors = $this->environment->bundleProcessors;

        $this->assertTrue($bundleProcessors->isRegistered(
            $this->environment->contentType('.js'), '\Pipe\Compressor\UglifyJs'
        ));
    }

    function testSetCssCompressorRegistersBundleProcessor()
    {
        $this->environment->setCssCompressor('yuglify_css');
        $bundleProcessors = $this->environment->bundleProcessors;

        $this->assertTrue($bundleProcessors->isRegistered(
            $this->environment->contentType('.css'), '\Pipe\Compressor\YuglifyCss'
        ));
    }

    function testSetJsCompressorUnregistersPreviousBundleProcessor()
    {
        $bundleProcessors = $this->environment->bundleProcessors;

        $this->environment->setJsCompressor('uglify_js');
        $this->environment->setJsCompressor('yuglify_js');

        $this->assertFalse($bundleProcessors->isRegistered(
            $this->environment->contentType('.js'), '\Pipe\Compressor\UglifyJs'
        ));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    function testSetCssCompressorThrowsExceptionWhenCompressorNotExists()
    {
        $this->environment->setCssCompressor('foobarbaz');
    }

    function testConvertAbsolutePathToLogicalPath()
    {
        $this->assertEquals(
            'asset1.js', $this->environment->logicalPath(__DIR__ . '/fixtures/asset1.js')
        );
    }
}
