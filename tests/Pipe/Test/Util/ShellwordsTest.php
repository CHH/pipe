<?php

namespace Pipe\Test\Util;

use Pipe\Util\Shellwords;

class ShellwordsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider splitDataProvider
     */
    function testSplit($input, $expected)
    {
        $actual = Shellwords::split($input); 
        $this->assertEquals($expected, $actual);
    }

    function splitDataProvider()
    {
        return array(
            array('foo "bar baz"', array('foo', 'bar baz')),
            array('foo \n bar', array("foo", '\n', "bar")),
            array('\'foo bar\' baz', array("foo bar", "baz")),
            array('"foo', array())
        );
    }
}
