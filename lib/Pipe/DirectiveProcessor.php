<?php

namespace Pipe;

use Pipe\DirectiveProcessor\Parser;

/**
 * A Filter which processes special directive comments.
 *
 * Directive comments start with the comment prefix and are then
 * followed by an "=". 
 *
 * For example:
 *
 * // Javascript:
 * //= require "foo"
 *
 * # Coffeescript:
 * #= require "foo"
 * 
 * / * CSS
 *   *= require "foo"
 *   * /
 * ( ^ This space must be here, otherwise PHP triggers an Parse Error)
 *
 * Directives must be in the Header of the Source File to be picked up.
 *
 * @author Christoph Hochstrasser <christoph.hochstrasser@gmail.com>
 */
class DirectiveProcessor extends \MetaTemplate\Template\Base
{
    /**
     * @var Pipe\DirectiveProcessor\Parser
     */
    protected $parser;

    /**
     * Map of available directives
     * @var array
     */
    protected $directives;

    /**
     * Checks if the Directive is registered
     *
     * @param string $name
     * @return bool
     */
    function isRegistered($name)
    {
        return isset($this->directives[$name]);
    }

    function register($name, $directive)
    {
        if (!is_callable($directive)) {
            throw new \InvalidArgumentException('Directive should be something callable');
        }
        $this->directives[$name] = $directive;
        return $this;
    }

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

    function render($context = null, $vars = array())
    {
        $newSource = '';

        foreach ($this->tokens as $token) {
            list($type, $content, $line) = $token;

            if ($type !== Parser::T_DIRECTIVE) {
                $newSource .= $content . "\n";

            } else {
                // TODO: Split by Shell Argument Rules
                $argv = explode(' ', $content);
                $directive = array_shift($argv);

                $context->path = $this->source;
                $this->executeDirective($directive, $context, $argv);
            }
        }

        return $newSource;
    }

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
