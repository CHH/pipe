<?php

namespace Pipe;

use Pipe\Util\Pathstack,
    Pipe\Util\Pathname,
    Pipe\Util\ProcessorRegistry,
    MetaTemplate\Template,
    MetaTemplate\Util\EngineRegistry;

class Environment implements \ArrayAccess
{
    # Stack of Load Paths for Assets.
    var $loadPaths;

    # Map of file extension to content type.
    var $contentTypes = array(
        '.css' => 'text/css',
        '.js'  => 'application/javascript'
    );

    # Engine Registry, stores engines per file extension.
    var $engines;

    # Processors are like engines, but are associated with
    # a specific content type and one processor can be 
    # associated with one or more content types
    protected $preProcessors;
    protected $postProcessors;
    protected $bundleProcessors;

    function __construct()
    {
        $this->loadPaths = new Pathstack;

        $this->engines          = new EngineRegistry;
        $this->preProcessors    = new ProcessorRegistry;
        $this->postProcessors   = new ProcessorRegistry;
        $this->bundleProcessors = new ProcessorRegistry;

        # Register default processors
        $this->registerPreProcessor('text/css', '\\Pipe\\DirectiveProcessor');
        $this->registerPreProcessor('application/javascript', '\\Pipe\\DirectiveProcessor');

        # Register default Template Engines
        foreach (Template::getEngines()->getEngines() as $ext => $engine) {
            $this->registerEngine($engine, $ext);
        }
    }

    function getPreProcessors($contentType = null)
    {
        if (null === $contentType) {
            return $this->preProcessors;
        }
        return $this->preProcessors->get($contentType);
    }

    function getPostProcessors($contentType = null)
    {
        if (null === $contentType) {
            return $this->postProcessors;
        }
        return $this->postProcessors->get($contentType);
    }

    function getBundleProcessors($contentType = null) 
    {
        if (null === $contentType) {
            return $this->bundleProcessors;
        }
        return $this->bundleProcessors->get($contentType);
    }

    function registerEngine($engine, $extension)
    {
        $this->engines->register($engine, $extension);
        return $this;
    }

    function registerPreProcessor($contentType, $processor)
    {
        $this->preProcessors->register($contentType, $processor);
        return $this;
    }

    function registerPostProcessor($contentType, $processor)
    {
        $this->postProcessors->register($contentType, $processor);
        return $this;
    }

    function registerBundleProcessor($contentType, $processor)
    {
        $this->bundleProcessors->register($contentType, $processor);
        return $this;
    }

    function prependPath($path)
    {
        $this->loadPaths->unshift($path);
        return $this;
    }

    function appendPath($path)
    {
        $this->loadPaths->push($path);
        return $this;
    }

    function find($logicalPath, $bundled = false)
    {
        $path = new Pathname($logicalPath);

        if ($path->isAbsolute()) {
            return new Asset($this, $path->toString(), $path->toString());
        }

        $realPath = $this->loadPaths->find($logicalPath);

        if (null === $realPath) {
            return;
        }

        if ($bundled) {
            $asset = new BundledAsset($this, $realPath, $logicalPath);
        } else {
            $asset = new Asset($this, $realPath, $logicalPath);
        }

        return $asset;
    }

    # Sugar for find().
    #
    # logicalPath - The path relative to the virtual file system.
    #
    # Returns an Asset.
    function offsetGet($logicalPath)
    {
        return $this->find($logicalPath);
    }

    function offsetSet($offset, $value) {}
    function offsetExists($offset) {}
    function offsetUnset($offset) {}
}
