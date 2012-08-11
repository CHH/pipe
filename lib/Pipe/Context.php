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
        $dependencyAssets = array(),
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
        if (!is_file($path)) {
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
            $processors = array();
        }

        foreach ($processors as $p) {
            $block = function() use (&$data) {
                return $data;
            };

            if (is_callable($p)) {
                $processor = $p($block);
            } else {
                $processor = new $p($block);
            }

            $subContext->path = $processor->source = $path;
            $data = $processor->render($subContext);
        }

        $this->requiredPaths = array_merge($this->requiredPaths, $subContext->requiredPaths);
        $this->dependencyPaths = array_merge($this->dependencyPaths, $subContext->dependencyPaths);
        $this->dependencyAssets = array_merge($this->dependencyAssets, $subContext->dependencyAssets);

        return $data;
    }

    function dataUri($path)
    {
        $data = $this->evaluate($this->resolve($path));

        return sprintf("data:%s;base64,%s",
            $this->contentType($path), urlencode(base64_encode($data))
        );
    }

    function contentType($path)
    {
        $asset = $this->environment->find($this->resolve($path));

        if (!$asset) {
            throw new \Exception("Asset '$path' not found.");
        }

        return $asset->getContentType();
    }

    function requireAsset($path)
    {
        $resolvedPath = $this->resolve($path);

        if (null === $resolvedPath) {
            throw new \UnexpectedValueException("Asset $path not found");
        }

        $asset = $this->environment->find($resolvedPath);

        if (!in_array($resolvedPath, $this->requiredPaths)) {
            $this->dependOn($resolvedPath);

            $processors = is_callable(array($asset, "getProcessors")) ? $asset->getProcessors() : array();

            $this->dependencyAssets[] = $this->evaluate($resolvedPath, array(
                "processors" => $processors
            ));

            $this->requiredPaths[] = $resolvedPath;
        }

        return $this;
    }

    function resolve($path)
    {
        # Skip the load path if the path starts with `./`
        if (preg_match('{^\.(/|\\\\)}', $path)) {
            $path = dirname($this->path) . DIRECTORY_SEPARATOR . $path;
        }

        if (is_dir($path)) {
            $index = Pathname::join(array($path, "index{$this->getExtension()}"));

            if (file_exists($index)) {
                $path = $index;
            }
        }

        # If the path has no extension, then use the extension of the
        # current source file.
        if (!pathinfo($path, PATHINFO_EXTENSION)) {
            $path .= $this->getExtension();
        }

        $pathinfo = new Pathname($path);

        if ($pathinfo->isAbsolute()) {
            return $path;
        }

        return $this->environment->loadPaths->find($path);
    }

    protected function getExtension()
    {
        return Pathname::normalizeExtension(pathinfo($this->path, PATHINFO_EXTENSION));
    }

    protected function createSubContext()
    {
        $context = new static($this->environment);
        $context->path = $this->path;
        $context->requiredPaths = $this->requiredPaths;

        return $context;
    }
}
