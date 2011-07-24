<?php

namespace Pipe\DirectiveProcessor;

use Pipe\Context,
    Pipe\DirectiveProcessor,
    Pipe\Asset;

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

        $asset = $context->getEnvironment()->find($path);

        if (!$asset instanceof Asset) {
            throw new \RuntimeException("Asset $path not found");
        }

        $context->push($asset->process($context));
    }
}
