<?php

namespace Pipe;

use Pipe\Util\ProcessorRegistry,
    MetaTemplate\Template,
    MetaTemplate\Util\EngineRegistry,
    CHH\FileUtils\Path,
    CHH\FileUtils\PathInfo,
    CHH\FileUtils\PathStack;

class Environment implements \ArrayAccess
{
    # Stack of Load Paths for Assets.
    public $loadPaths;

    # Map of file extensions to content types.
    public $contentTypes = array(
        '.css' => 'text/css',
        '.js'  => 'application/javascript',
        '.jpeg' => 'image/jpeg',
        '.jpg' => 'image/jpeg',
        '.png' => 'image/png',
        '.gif' => 'image/gif'
    );

    # Engine Registry, stores engines per file extension.
    public $engines;

    # Processors are like engines, but are associated with
    # a mime type.
    public $preProcessors;
    public $postProcessors;
    public $bundleProcessors;

    function __construct($root = null)
    {
        $this->root = $root;
        $this->loadPaths = new Pathstack($this->root);

        $this->engines = Template::getEngines();

        array_map(array($this->loadPaths, "appendExtensions"), array_keys($this->engines->getEngines()));

        $this->preProcessors    = new ProcessorRegistry;
        $this->postProcessors   = new ProcessorRegistry;
        $this->bundleProcessors = new ProcessorRegistry;

        $this->registerEngine('\\Pipe\\JstProcessor', '.jst');

        # Register default processors
        $this->registerPreProcessor('text/css', '\\Pipe\\ImportProcessor');
        $this->registerPreProcessor('text/css', '\\Pipe\\DirectiveProcessor');
        $this->registerPreProcessor('application/javascript', '\\Pipe\\DirectiveProcessor');
        $this->registerPostProcessor('application/javascript', '\\Pipe\\SafetyColons');
    }

    function registerEngine($engine, $extension)
    {
        $this->loadPaths->appendExtensions((array) $extension);
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
        $this->loadPaths->prepend($path);
        return $this;
    }

    function appendPath($path)
    {
        $this->loadPaths->push($path);
        return $this;
    }

    function find($logicalPath, $options = array())
    {
        $path = new PathInfo($logicalPath);

        if ($path->isAbsolute()) {
            $realPath = $logicalPath;
        } else {
            $realPath = $this->loadPaths->find($logicalPath);
        }

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
