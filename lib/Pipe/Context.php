<?php

namespace Pipe;

use Pipe\Util\Pathname,
    UnexpectedValueException;

class Context
{
    public
        $path,

        # All paths which were already required.
        $requiredPaths    = array(),

        # Array of all dependency paths.
        $dependencyPaths  = array(),

        # Array of the dependencies' contents.
        $dependencyAssets = array();

    protected
        $environment;

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
        $asset = $this->environment->find($path);

        if (!$asset) {
            throw new UnexpectedValueException("Asset $path not found");
        }

        if (isset($options['data'])) {
            $data = $options['data'];
        } else {
            $data = @file_get_contents($path);
        }

        $subContext = $this->createSubContext();

        if (array_key_exists("processors", $options)) {
            $processors = $options["processors"];
        } else {
            $processors = $asset->getProcessors();
        }

        foreach ($processors as $class) {
            $processor = new $class(function() use ($data) {
                return $data;
            });

            $subContext->path = $processor->source = $asset->path;
            $data = $processor->render($subContext);
        }

        $this->requiredPaths = array_merge($this->requiredPaths, $subContext->requiredPaths);
        $this->dependencyPaths = array_merge($this->dependencyPaths, $subContext->dependencyPaths);
        $this->dependencyAssets = array_merge($this->dependencyAssets, $subContext->dependencyAssets);

        return $data;
    }

    function dataUri($path)
    {
        $data = $this->evalute($this->resolve($path));

        return sprintf("data:%s;base64,%s",
            $this->contentType($path), urlencode(base64_encode($data));
        );
    }

    function contentType($path)
    {
        $asset = $this->environment->find($this->resolve($path));
        return $asset->getContentType();
    }

    function requireAsset($path)
    {
        $resolvedPath = $this->resolve($path);

        if (null === $resolvedPath) {
            throw new \UnexpectedValueException("Asset $path not found");
        }

        if (!in_array($resolvedPath, $this->requiredPaths)) {
            $this->dependOn($resolvedPath);
            $this->dependencyAssets[] = $this->evaluate($resolvedPath);
            $this->requiredPaths[] = $resolvedPath;
        }

        return $this;
    }

    protected function resolve($path)
    {
        # If the path has no extension, then use the extension of the
        # current source file.
        if (!pathinfo($path, PATHINFO_EXTENSION)) {
            $path .= Pathname::normalizeExtension(pathinfo($this->path, PATHINFO_EXTENSION));
        }

        # Skip the load path if the path starts with `./`
        if (preg_match('{^\.(/|\\\\)}', $path)) {
            $path = dirname($this->path) . DIRECTORY_SEPARATOR . $path;
            return realpath($path);
        }

        $pathinfo = new Pathname($path);

        if ($pathinfo->isAbsolute()) {
            return realpath($path);
        }

        $loadPaths = $this->environment->loadPaths;
        return $loadPaths->find($path);
    }

    protected function createSubContext()
    {
        $context = new static($this->environment);
        $context->path = $this->path;
        $context->requiredPaths = $this->requiredPaths;

        return $context;
    }
}
