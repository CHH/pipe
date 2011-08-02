<?php

namespace Pipe\Test\Template;

use Pipe\Template\PhpTemplate;

class PhpTemplateTest extends \PHPUnit_Framework_TestCase
{
    function testRendersEmptyContextWithSingleVariable()
    {
        $templ = new PhpTemplate(__DIR__ . "/fixtures/php/test.phtml");
        $output = $templ->render(new \StdClass, array('name' => 'Jim'));

        $this->assertEquals("Hello World Jim!\n", $output);
    }
}
