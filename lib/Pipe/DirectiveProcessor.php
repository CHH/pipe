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
    # Map of directive name and the closure which is
    # invoked when the directive was used.
    protected $directives = array();
    protected $parsedDirectives;

    protected $header = '';
    protected $body = '';

    const HEADER_PATTERN = "/
      \A (
        (?m:\s*) (
          (\\/\\* (?m:.*?) \\*\\/) |
          (\\#\\#\\# (?m:.*?) \\#\\#\\#) |
          (\\/\\/ .* \\\n?)+ |
          (\\# .* \\\n?)+
        )
      )+
    /x";

    const DIRECTIVE_PATTERN = '/
      ^ [\W]* = \s* (\w+.*?) (\\*\\/)? $
    /x';

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
        $this->register('require', function($context, $path) {
            $context->requireAsset($path);
        });

        $this->register('depend_on', function($context, $path) {
            $context->dependOn($path);
        });

        $this->register('require_tree', function($context, $path) {
            $context->requireTree($path);
        });

        $this->body = $this->getData();

        if (preg_match(self::HEADER_PATTERN, $this->getData(), $matches)) {
            $this->header = $matches[0];
            $this->body = substr($this->getData(), strlen($matches[0])) ?: '';
        }

        $this->processed = array();
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

        $directives = $this->getDirectives();

        foreach ($directives as $directive) {
            list($i, $name, $argv) = $directive;
            $this->executeDirective($name, $context, $argv);
        }

        return $this->getProcessedSource();
    }

    protected function getProcessedHeader()
    {
        $header = $this->header;

        foreach (explode("\n", $header) as $i => $line) {
            if (isset($this->parsedDirectives[$i])) {
                $header = str_replace($line, "\n", $header);
            }
        }

        return trim($header);
    }

    protected function getDirectives()
    {
        if (null === $this->parsedDirectives) {
            $this->parsedDirectives = array();

            foreach (explode("\n", $this->header) as $i => $line) {
                if (preg_match(self::DIRECTIVE_PATTERN, $line, $matches)) {
                    $argv = Shellwords::split($matches[1]);
                    $name = array_shift($argv);
                    $this->parsedDirectives[$i] = array($i, $name, $argv);
                }
            }
        }

        return $this->parsedDirectives;
    }

    protected function getProcessedSource()
    {
        return $this->getProcessedHeader() . "\n" . $this->body;
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
                "Undefined Directive \"%s\" in %s", $directive, $this->source
            ));
        }

        $callback = $this->directives[$directive];

        array_unshift($argv, $context);

        return call_user_func_array($callback, $argv);
    }
}
