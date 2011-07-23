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

        if ($this->processor->hasProcessed($path)) {
            return;
        }

        if (preg_match('/^\.(\/|\\\\)/', $path)) {
            $path = $this->processor->getDirname() . DIRECTORY_SEPARATOR . $path;
        }

        $env = $context->getEnvironment();
        $context->push($env[$path]);
    }
}
