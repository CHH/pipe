<?php

namespace Pipe;

use Pipe\DirectiveProcessor\Parser,
    Pipe\DirectiveProcessor\Directive,
    Pipe\DirectiveProcessor\RequireDirective,
    Pipe\DirectiveProcessor\DependOnDirective;

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

    /**
     * Register a directive
     *
     * @param Directive $directive
     * @return DirectiveProcessor
     */
    function register(Directive $directive)
    {
        $name = $directive->getName();

        if (empty($name)) {
            throw new \UnexpectedValueException(sprintf(
                "No Name found for Directive %s, please return the Name with the
                Directive's getName() Method",
                get_class($directive)
            ));
        }

        $directive->setProcessor($this);
        $this->directives[$name] = $directive;

        return $this;
    }

    protected function prepare()
    {
        $this->parser = new Parser;

        // Require Standard Directives
        $this->register(new RequireDirective);
        $this->register(new DependOnDirective);

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

                if (!$this->isRegistered($directive)) {
                    throw new \RuntimeException(sprintf(
                        "Undefined Directive \"%s\" in %s on line %d",
                        $directive,
                        $this->file,
                        $line
                    ));
                }
                $this->directives[$directive]->execute($context, $argv);
            }
        }

        return $newSource;
    }
}
