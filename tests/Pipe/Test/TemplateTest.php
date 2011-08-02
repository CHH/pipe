<?php

namespace Pipe\Test;

use Pipe\Template;

class TemplateTest extends \PHPUnit_Framework_TestCase
{
    function testRegistersExtensionWithTemplate()
    {
        Template::register('\\Pipe\\Template\\PhpTemplate', '.phtml');

        $templ = Template::create(__DIR__ . "/fixtures/template.phtml");

		$this->assertInternalType('object', $templ);
        $this->assertInstanceOf('\\Pipe\\Template\\PhpTemplate', $templ);
    }

	function testReturnsNullIfExtensionHasNoEngine()
	{
		$this->assertNull(Template::get('foo.erb'));
	}
}
