<?php

namespace Pipe;

use Pipe\Environment,
    Pipe\Context;

class Asset
{
    /**
     * @var Environment
     */
    protected $environment;

    /**
     * @var SplFileInfo
     */
    protected $path;

    protected $body;

    /**
     * List of the file's extensions
     * @var array
     */
    protected $extensions;

    protected $dependencies = array();

    function __construct(Environment $environment, $path)
    {
        $this->environment = $environment;
        $this->path = $path;
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
        return $this->environment->getContentTypes()->get(
            $this->getFormatExtension()
        );
    }

    function getFormatExtension()
    {
        $environment = $this->environment;

        return current(array_filter(
            $this->getExtensions(), 
            function($ext) use ($environment) {
                return 
                    $environment->getContentTypes()->get($ext) 
                    and !$environment->getEngine($ext);
            }
        ));
    }

    function getEngineExtensions()
    {
        $environment = $this->environment;

        return array_filter(
            $this->getExtensions(),
            function($ext) use ($environment) {
                return $environment->getEngine($ext);
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
            if (false === ($pos = strpos($basename, '.', 1))) {
                return array();
            }

            $this->extensions = explode('.', substr($basename, $pos + 1));
        }
        return $this->extensions;
    }

    function getProcessors()
    {
        $formatExtension = $this->getFormatExtension();
        $contentType = $this->environment->getContentTypes()->get($formatExtension);

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
        return pathinfo($this->path, PATHINFO_BASENAME);
    }

    function getDirname()
    {
        return pathinfo($this->path, PATHINFO_DIRNAME);
    }

    function getPath()
    {
        return $this->path;
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
