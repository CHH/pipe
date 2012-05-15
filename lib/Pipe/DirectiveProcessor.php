<?php

namespace Pipe;

use Pipe\DirectiveProcessor\Parser,
    CHH\Shellwords;

# A Filter which processes special comments.
#
# Directive Comments start with a comment prefix and are then
# followed by an equal sign. Directives *must* be in the header
# of the source file. The parser stops after code is encountered.
#
# Examples
#
#   // Javascript:
#   //= require "foo"
#   
#   # Coffeescript
#   #= require "foo"
#   
#   /* CSS
#    *= require "foo"
#    */
#
class DirectiveProcessor extends \MetaTemplate\Template\Base
{
    # Parser for directives.
    protected $parser;

    # Map of directive name and the closure which is
    # invoked when the directive was used.
    protected $directives;

    # Is the directive registered?
    #
    # name - Directive Name.
    #
    # Returns True or False.
    function isRegistered($name)
    {
        return isset($this->directives[$name]);
    }

    # Registers a directive.
    #
    # name      - Directive Name.
    # directive - Closure which is called when the directive is used.
    #
    # Returns This.
    function register($name, $directive)
    {
        if (!is_callable($directive)) {
            throw new \InvalidArgumentException('Directive should be something callable');
        }
        $this->directives[$name] = $directive;
        return $this;
    }

    # Sets up the processor.
    #
    # Returns nothing.
    protected function prepare()
    {
        $this->parser = new Parser;

        $this->register('require', function($context, $argv = array()) {
            $path = array_shift($argv);
            $context->requireAsset($path);
        });

        $this->register('depend_on', function($context, $argv = array()) {
            $path = array_shift($argv);
            $context->dependOn($path);
        });

        $this->processed = array();
        $this->tokens = $this->parser->parse($this->getData());
    }

    # Loops through all tokens returned by the parser and invokes
    # the directives.
    #
    # context - Pipe\Context
    # vars    - An array of var => value pairs.
    #
    # Returns the processed asset, with all directives stripped.
    function render($context = null, $vars = array())
    {
        $newSource = '';

        foreach ($this->tokens as $token) {
            list($type, $content, $line) = $token;

            if ($type !== Parser::T_DIRECTIVE) {
                $newSource .= $content . "\n";

            } else {
                // TODO: Split by Shell Argument Rules
                $argv = Shellwords::split($content);
                $directive = array_shift($argv);

                $this->executeDirective($directive, $context, $argv);
            }
        }

        return $newSource;
    }

    # Executes a directive.
    #
    # directive - Name of the Directive.
    # context   - Pipe\Context.
    # argv      - Array of the directive arguments.
    #
    # Returns the return value of the directive's callback.
    protected function executeDirective($directive, $context, $argv)
    {
        if (!$this->isRegistered($directive)) {
            throw new \RuntimeException(sprintf(
                "Undefined Directive \"%s\" in %s on line %d", $directive, $this->source, $line
            ));
        }

        $callback = $this->directives[$directive];
        return $callback($context, $argv);
    }
}
