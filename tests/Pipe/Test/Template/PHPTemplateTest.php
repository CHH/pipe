<?php

namespace Pipe\Test\Template;

use Pipe\Template\PHPTemplate;

class TestContext
{
    function greet()
    {
        return "Hello World!";
    }
}

class PHPTemplateTest extends \PHPUnit_Framework_TestCase
{
    function testRendersEmptyContextWithSingleVariable()
    {
        $templ = new PHPTemplate(__DIR__ . "/fixtures/php/test.phtml");
        $output = $templ->render(new \StdClass, array('name' => 'Jim'));

        $this->assertEquals("Hello World Jim!\n", $output);
    }

    function testCallContextMethod()
    {
        $template = new PHPTemplate(null, array('reader' => function() {
            return '<?= $this->greet(); ?>';
        }));

        $this->assertEquals("Hello World!", $template->render(new TestContext));
    }

    function testReadContextProperty()
    {
        $template = new PHPTemplate(null, array('reader' => function() {
            return '<?= $this->foo; ?>';
        }));

        $context = (object) array('foo' => 'bar');

        $this->assertEquals('bar', $template->render($context));
    }
}
