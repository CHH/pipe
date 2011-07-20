<?php

namespace Pipe;

use Pipe\DirectiveProcessor\Directive,
    Pipe\DirectiveProcessor\RequireDirective;

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
class DirectiveProcessor extends Template
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
     * List of processed files, to avoid following circular references
     * @var array
     */
    protected $processed = array();

    function __construct(Parser $parser = null)
    {
        $this->parser = $parser ?: new Parser;

        // Require Standard Directives
        $this->register(new RequireDirective);
    }

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

    function prepare()
    {
        $this->processed = array();
        $this->tokens = $this->parser->parse($this->getData());
    }

    function evaluate(Context $context, $vars)
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
                        $asset->getSourceRoot() . DIRECTORY_SEPARATOR . $asset->getSourcePath(),
                        $line
                    ));
                }
                $this->directives[$directive]->execute($context, $argv);
            }
        }

        return $newSource;
    }

    /**
     * Checks if the source file has been processed
     */
    function hasProcessed($sourceFile)
    {
        return in_array($sourceFile, $this->processed);
    }
}
