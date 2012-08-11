<?php

namespace Pipe\Test;

use Pipe\ProcessedAsset,
    Pipe\Environment;

class AssetTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider testFormatExtensionsProvider
     */
    function testFormatExtensions($input, $expected)
    {
        $env = new Environment;
        $asset = new ProcessedAsset($env, $input, $input);

        $this->assertEquals($expected, $asset->getFormatExtension());
    }

    function testFormatExtensionsProvider()
    {
        return array(
            # Engine Content Type maps to "application/javascript", so 
            # ".js" must be the result
            array("foo/bar.coffee", ".js"),

            array("foo/bar.less", ".css"),
            array("foo/bar.less.css", ".css"),
            array("foo/bar.coffee.js", ".js"),

            # Not existing extensions should be left as is.
            array("foo/bar.bar", ".bar"),
        );
    }
}
