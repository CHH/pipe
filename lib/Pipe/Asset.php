<?php
/**
 * Asset Base Class
 *
 * @copyright Copyright (c) 2011 Christoph Hochstrasser
 * @license MIT License
 * @author Christoph Hochstrasser <christoph.hochstrasser@gmail.com>
 */

namespace Pipe;

use Pipe\Environment,
    Pipe\Context,
    Pipe\Util\Pathname;

class Asset
{
    var $path;
    var $logicalPath;

    /**
     * The Asset's declared Dependencies
     * @var array
     */
    var $dependencies = array();

    /**
     * @var Environment
     */
    protected $environment;
    protected $body;

    /**
     * List of the file's extensions
     * @var array
     */
    protected $extensions;

    function __construct(Environment $environment, $path, $logicalPath = null)
    {
        $this->environment = $environment;
        $this->path = $path;
        $this->logicalPath = $logicalPath;
    }

    function getBody()
    {
        if (null === $this->body) {
            $ctx    = new Context($this->environment);
            $result = "";

            $body = $ctx->evaluate($this->path);

            $this->dependencies = array_merge($this->dependencies, $ctx->getDependencyPaths());

            $result .= join("\n", $ctx->getDependencyAssets());
            $result .= $body;

            $this->body = $result;
        }
        return $this->body;
    }

    function __toString()
    {
        return $this->getBody();
    }

    function getLastModified()
    {
        // Load the asset, if it's not loaded
        if (!$this->body) {
            $this->getBody();
        }

        $dependenciesLastModified = array_map(
            function($dep) {
                return filemtime($dep);
            },
            $this->dependencies
        );

        return max(filemtime($this->path), max($dependenciesLastModified));
    }

    function getContentType()
    {
        $formatExtension = $this->getFormatExtension();

        return isset($this->environment->contentTypes[$formatExtension])
            ? $this->environment->contentTypes[$formatExtension]
            : false;
    }

    function getFormatExtension()
    {
        $environment = $this->environment;

        return current(array_filter(
            $this->getExtensions(), 
            function($ext) use ($environment) {
                return
                    isset($environment->contentTypes[$ext])
                    and !$environment->engines->get($ext);
            }
        ));
    }

    function getEngineExtensions()
    {
        $environment = $this->environment;

        return array_filter(
            $this->getExtensions(),
            function($ext) use ($environment) {
                return $environment->engines->get($ext);
            }
        );
    }

    /**
     * Returns a list of the File's Extensions, in reverse order
     *
     * @return array
     */
    function getExtensions()
    {
        if (null === $this->extensions) {
            $basename = $this->getBasename();

            // Avoid treating name of a dotfile as extension by
            // ignoring dots at the first offset in the string
            if (!$basename or false === ($pos = strpos($basename, '.', 1))) {
                return array();
            }

            $this->extensions = explode('.', substr($basename, $pos + 1));

            $this->extensions = array_map(function($ext) {
                return Pathname::normalizeExtension($ext);
            }, $this->extensions);
        }
        return $this->extensions;
    }

    function getProcessors()
    {
        $formatExtension = $this->getFormatExtension();
        $contentType = $this->environment->contentTypes[$formatExtension];

        return array_merge(
            $this->environment->getPreProcessors($contentType),
            array_reverse($this->getEngines()),
            $this->environment->getPostProcessors($contentType)
        );
    }

    function getEngines()
    {
        $env = $this->environment;

        return array_map(
            function($ext) use ($env) {
                return $env->getEngine($ext);
            },
            $this->getEngineExtensions()
        );
    }

    function getBasename()
    {
        return basename($this->path);
    }

    function getDirname()
    {
        return dirname($this->path);
    }

    protected function getEngineContentType()
    {
        foreach ($this->getEngineExtensions() as $ext) {
            $engine = $this->environment->getEngine($ext);

            if (is_callable(array($engine, "getDefaultContentType"))) {
                return $engine::getDefaultContentType();
            }
        }
    }
}
