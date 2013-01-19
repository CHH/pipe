<?php

namespace Pipe\Test;

use Pipe\JstProcessor;
use Pipe\Context;
use Pipe\Environment;

class JstProcessorTest extends \PHPUnit_Framework_TestCase
{
    protected $env;
    protected $ctx;

    function setup()
    {
        $this->env = new Environment;
        $this->ctx = new Context($this->env);
    }

    function test()
    {
        $this->ctx->logicalPath = 'foo';

        $jst = new JstProcessor(function() {
            return <<<JS
function() { return "foo"; }
JS;
        });

        $this->assertEquals(<<<EXPECTED
(function() {
    this.JST || (this.JST = {});

    this.JST["foo"] = function() { return "foo"; };
}).call(this);
EXPECTED
        , $jst->render($this->ctx));
    }

    function testCustomNamespace()
    {
        $this->ctx->logicalPath = 'foo';

        $defaultNamespace = JstProcessor::$defaultNamespace;
        JstProcessor::$defaultNamespace = "this.Pipe";

        $jst = new JstProcessor(function() {
            return <<<JS
function() { return "foo"; }
JS;
        });

        $this->assertEquals(<<<EXPECTED
(function() {
    this.Pipe || (this.Pipe = {});

    this.Pipe["foo"] = function() { return "foo"; };
}).call(this);
EXPECTED
        , $jst->render($this->ctx));

        JstProcessor::$defaultNamespace = $defaultNamespace;
    }

    function testQuoting()
    {
        $this->ctx->logicalPath = 'foo';

        $jst = new JstProcessor(function() {
            return <<<JS
{{#foo}}<span>foo</span>{{/foo}} "foo"
JS;
        }, array('quote' => true));

        $this->assertEquals(<<<EXPECTED
(function() {
    this.JST || (this.JST = {});

    this.JST["foo"] = "{{#foo}}<span>foo</span>{{/foo}} \\"foo\\"";
}).call(this);
EXPECTED
        , $jst->render($this->ctx));
    }
}

