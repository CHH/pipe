<?php

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

            $this->dependencies = array_merge($this->dependencies, $ctx->dependencyPaths);

            $result .= join("\n", $ctx->dependencyAssets);
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

        $formatExtension = current(array_filter(
            $this->getExtensions(),
            function($ext) use ($environment) {
                return
                    isset($environment->contentTypes[$ext])
                    and !$environment->engines->get($ext);
            }
        ));

        return $formatExtension ?: array_search($this->getEngineContentType(), $environment->contentTypes);
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

    # Collects the file's extensions in reverse order.
    #
    # Returns an Array of normalized extensions.
    function getExtensions()
    {
        if (null === $this->extensions) {
            $basename = $this->getBasename();

            # Avoid treating name of a dotfile as extension by
            # ignoring dots at the first offset in the string
            if (!$basename or false === ($pos = strpos($basename, '.', 1))) {
                return array();
            }

            $extensions = explode('.', substr($basename, $pos + 1));

            $this->extensions = array_map(function($ext) {
                return Pathname::normalizeExtension($ext);
            }, $extensions);
        }
        return $this->extensions;
    }

    function getProcessors()
    {
        $formatExtension = $this->getFormatExtension();

        # TODO: Throw error if content type/format ext was not found?
        if ($formatExtension) {
            $contentType = @$this->environment->contentTypes[$formatExtension];
        }

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
                return $env->engines->get($ext);
            },
            $this->getEngineExtensions()
        );
    }

    function write($directory = '', $digestFile = true)
    {
        $filename = $this->getTargetName();

        if ($directory) {
            $filename = $directory . '/' . $filename;
        }

        if (!is_dir(dirname($filename))) {
            mkdir(dirname($filename), 0777, true);
        }

        @file_put_contents($filename, $this->getBody());

        if ($digestFile) {
            # Write a file which includes the asset's digest, so
            # the filename can be reconstructed using the asset's name
            # and this file.
            @file_put_contents($this->getTargetName(false) . ".digest", $this->getSha1());
        }
    }

    function getSha1()
    {
        return sha1($this->getBody());
    }

    function getTargetName($includeHash = true)
    {
        $target = $this->getBasename(false);

        if ($includeHash) {
            $target .= '-' . $this->getSha1();
        }

        $target .= $this->getFormatExtension();
        return $target;
    }

    function getBasename($includeExtensions = true)
    {
        $basename = basename($this->path);

        if (!$includeExtensions) {
            $basename = substr($basename, 0, strpos($basename, '.'));
        }

        return $basename;
    }

    function getDirname()
    {
        return dirname($this->path);
    }

    protected function getEngineContentType()
    {
        foreach ($this->getEngineExtensions() as $ext) {
            $engine = $this->environment->engines->get($ext);

            if (is_callable(array($engine, "getDefaultContentType"))) {
                return $engine::getDefaultContentType();
            }
        }
    }
}
