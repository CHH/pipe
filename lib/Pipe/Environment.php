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

    # Map of file extensions to content types.
    var $contentTypes = array(
        '.css' => 'text/css',
        '.js'  => 'application/javascript',
        '.jpeg' => 'image/jpeg',
        '.jpg' => 'image/jpeg',
        '.png' => 'image/png',
        '.gif' => 'image/gif'
    );

    # Engine Registry, stores engines per file extension.
    var $engines;

    # Processors are like engines, but are associated with
    # a specific content type and one processor can be 
    # associated with one or more content types
    var $preProcessors;
    var $postProcessors;
    var $bundleProcessors;

    function __construct()
    {
        $this->loadPaths = new Pathstack;

        $this->engines          = Template::getEngines();
        $this->preProcessors    = new ProcessorRegistry;
        $this->postProcessors   = new ProcessorRegistry;
        $this->bundleProcessors = new ProcessorRegistry;

        # Register default processors
        $this->registerPreProcessor('text/css', '\\Pipe\\DirectiveProcessor');
        $this->registerPreProcessor('application/javascript', '\\Pipe\\DirectiveProcessor');
        $this->registerPostProcessor('application/javascript', '\\Pipe\\SafetyColons');
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

    function find($logicalPath, $options = array())
    {
        $path = new Pathname($logicalPath);

        if ($path->isAbsolute()) {
            return new ProcessedAsset($this, $path->toString(), $path->toString());
        }

        $realPath = $this->loadPaths->find($logicalPath);

        if (!is_file($realPath)) {
            return;
        }

        if (null === $realPath) {
            return;
        }

        if (@$options["bundled"]) {
            $asset = new BundledAsset($this, $realPath, $logicalPath);
        } else {
            $asset = new ProcessedAsset($this, $realPath, $logicalPath);
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
