<?php

namespace Pipe\Test;

use Pipe\DirectiveProcessor,
    Pipe\Context,
    Pipe\Environment;

class DirectiveProcessorTest extends \PHPUnit_Framework_TestCase
{
    function test()
    {
        $fixturePath = __DIR__ . "/fixtures/directive_processor/application.js";
        $processor = new DirectiveProcessor($fixturePath);

        $processor->prepare();

        $env = new Environment;
        $env->addLoadPath(__DIR__ . "/fixtures/directive_processor");

        $context = new Context($env);

        $processor->render($context);
    }
}
