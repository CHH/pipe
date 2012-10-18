<?php

namespace Pipe\Test\Compressor;

use Pipe\Compressor\YuglifyJs;
use Pipe\Compressor\YuglifyCss;

class YuglifyTest extends \PHPUnit_Framework_TestCase
{
    function setup()
    {
        if (!isset($_ENV['YUGLIFY_BIN'])) {
            $this->markTestSkipped('Set $_ENV[YUGLIFY_BIN] to enable this test');
        }
    }

    function testJs()
    {
        $c = new YuglifyJs(__DIR__ . '/fixtures/test.js');

        $this->assertNotEmpty($c->render());
    }

    function testCss()
    {
        $c = new YuglifyCss(__DIR__ . '/fixtures/test.css');

        $this->assertNotEmpty($c->render());
    }
}

