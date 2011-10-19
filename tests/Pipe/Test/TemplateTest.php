<?php

namespace Pipe\Test;

use Pipe\Template;

class TemplateTest extends \PHPUnit_Framework_TestCase
{
    function testRegistersExtensionWithTemplate()
    {
        Template::register('\\Pipe\\Template\\PHPTemplate', '.phtml');

        $templ = Template::create(__DIR__ . "/fixtures/template.phtml");

		$this->assertInternalType('object', $templ);
        $this->assertInstanceOf('\\Pipe\\Template\\PHPTemplate', $templ);
    }

	function testReturnsNullIfExtensionHasNoEngine()
	{
		$this->assertNull(Template::get('foo.erb'));
	}
}
