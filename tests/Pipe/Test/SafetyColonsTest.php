<?php

namespace Pipe\Test;

use Pipe\SafetyColons;

class SafetyColonsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dataProvider
     */
    function test($message, $expected, $data)
    {
        $templ = new SafetyColons(function() use ($data) {
            return $data;
        });

        $this->assertEquals($expected, $templ->render(), $message);
    }

    function dataProvider()
    {
        return array(
            array("Adds semicolon when none found", "foo;", "foo"),
            array("Adds no semicolon when found", "foo;", "foo;"),
            array("Leave empty strings alone", "", ""),
            array("Matches semicolon also when whitespace at the end of file", "foo;".str_repeat(' ', 4), "foo;".str_repeat(' ', 4))
        );
    }
}

