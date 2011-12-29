<?php

namespace Pipe;

use Pipe\Util\Pathname;

class Context
{
    var $path;

    protected $environment;
    protected $requiredPaths    = array();
    protected $dependencyPaths  = array();
    protected $dependencyAssets = array();

    function __construct(Environment $environment)
    {
        $this->environment = $environment;
    }

    function dependOn($path)
    {
        $this->dependencyPaths[] = $this->resolve($path);
        return $this;
    }

    function evaluate($path, $options = array())
    {
        if (isset($options['data'])) {
            $data = $options['data'];
        } else {
            $data = @file_get_contents($path);
        }

        $asset = $this->environment->find($path);

        foreach ($asset->getProcessors() as $processorClass) {
            $processor = new $processorClass(function() use ($data) {
                return $data;
            });
            $this->path = $processor->source = $asset->path;
            $data = $processor->render($this);
        }

        return $data;
    }

    function getDependencyAssets()
    {
        return $this->dependencyAssets;
    }

    function getDependencyPaths()
    {
        return $this->dependencyPaths;
    }

    function getRequiredPaths()
    {
        return $this->requiredPaths;
    }

    function getEnvironment()
    {
        return $this->environment;
    }

    function requireAsset($path)
    {
        $resolvedPath = $this->resolve($path);

        if (in_array($resolvedPath, $this->requiredPaths)) {
            return $this;
        }

        $this->dependOn($resolvedPath);
        $this->dependencyAssets[] = $this->evaluate($resolvedPath);
        $this->requiredPaths[] = $resolvedPath;

        return $this;
    }

    protected function resolve($path)
    {
        // If the path has no extension, then use the extension of the
        // current source file.
        if (!pathinfo($path, PATHINFO_EXTENSION)) {
            $path .= Pathname::normalizeExtension(pathinfo($this->path, PATHINFO_EXTENSION));
        }

        // Skip the load path if the path starts with `./`
        if (preg_match('{^\.(/|\\\\)}', $path)) {
            $path = dirname($this->path) . DIRECTORY_SEPARATOR . $path;
            return realpath($path);
        }

        $pathinfo = new Pathname($path);

        if ($pathinfo->isAbsolute()) {
            return realpath($path);
        }

        $loadPaths = $this->environment->loadPaths;

        return realpath($loadPaths->find($path));
    }
}
