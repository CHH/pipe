<?php

namespace Pipe\DirectiveProcessor;

use Pipe\Context,
    Pipe\DirectiveProcessor;

class RequireDirective implements Directive
{
    protected $processor;

    function setProcessor(DirectiveProcessor $processor)
    {
        $this->processor = $processor;
    }

    function getName()
    {
        return "require";
    }

    function execute(Context $context, array $argv)
    {
        $path = $argv[0];

        if (preg_match('/^\.(\/|\\\\)/', $path)) {
            $path = $this->processor->getDirname() . DIRECTORY_SEPARATOR . $path;
        }

        if ($context->has($path)) {
            return false;
        }

        $context->push($context->getEnvironment()->find($path));
    }
}
