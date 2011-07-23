<?php

namespace Pipe\Test;

use Pipe\Asset,
    Pipe\Environment;

class AssetTest extends \PHPUnit_Framework_TestCase
{
    function getExtensionsAsArrayProvider()
    {
        return array(
            array('/foo/bar.css.less.phtml', array('phtml', 'less', 'css')),
            array('/foo/bar/.application.js', array('js')),
            array('/foo/bar.less', array('less')),
            array('/foo/bar', array())
        );
    }

    /**
     * @dataProvider getExtensionsAsArrayProvider
     */
    function testGetExtensionsAsArrayInReverseOrder($path, $expected)
    {
        $asset = new Asset(new Environment, $path);;

        $this->assertEquals(
            $expected,
            $asset->getExtensions()
        );
    }
}
