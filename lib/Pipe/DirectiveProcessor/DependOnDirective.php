<?php

namespace Pipe\DirectiveProcessor;

use Pipe\Context,
	Pipe\DirectiveProcessor,
	Pipe\Util\Pathname;

class DependOnDirective implements Directive
{
	protected $processor;

	function setProcessor(DirectiveProcessor $processor)
	{
		$this->processor = $processor;
	}

	function getName()
	{
		return "depend_on";
	}

	function execute(Context $context, array $argv)
	{
		$path = array_shift($argv);
		$pathinfo = new Pathname($path);

		if (!$pathinfo->isAbsolute()) {
			$path = $this->processor->getDirname() . '/' . $path;
		}

		$context->dependOn($path);
	}
}
