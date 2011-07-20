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
        if ($this->processor->hasProcessed($argv[0])) {
            return;
        }

        $env = $context->getEnvironment();
        $context->push($env[$argv[0]]);
    }
}
